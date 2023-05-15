import {throttle} from 'underscore';
import $ from 'jquery';
import BaseView from 'oroui/js/app/views/base/view';
import routing from 'routing';
import numberFormatter from 'orolocale/js/formatter/number';
import urlHelper from 'orodatagrid/js/url-helper';

const ProductKitFormView = BaseView.extend({
    /**
     * @inheritdoc
     */
    optionNames: BaseView.prototype.optionNames.concat([
        'kitLineItemProductSelector', 'kitLineItemQuantitySelector', 'subtotalUrl', 'productId', 'maskClass'
    ]),

    kitLineItemProductSelector: '[data-role="kit-line-item-product"]',

    kitLineItemQuantitySelector: '[data-role="kit-line-item-quantity"]',

    subtotalUrl: void 0,

    productId: void 0,

    maskClass: 'loading-blur',

    /**
     * @inheritdoc
     */
    events() {
        const events = {};

        events[`change ${this.kitLineItemProductSelector}`] = this.onProductChange;
        events[`change ${this.kitLineItemQuantitySelector}`] = this.getSubtotal;

        return events;
    },

    /**
     * @inheritdoc
     */
    constructor: function ProductKitFormView(options) {
        this.getSubtotal = throttle(this.getSubtotal.bind(this), 20);
        ProductKitFormView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        if (this.productId === void 0) {
            throw new Error('Option "productId" is required for ProductKitFormView');
        }

        if (this.subtotalUrl === void 0) {
            throw new Error('Option "subtotalUrl" is required for ProductKitFormView');
        }

        ProductKitFormView.__super__.initialize.call(this, options);

        this.$(this.kitLineItemProductSelector).each((i, el) => {
            this.lockRelatedElements($(el));
        });
    },

    /**
     * @inheritdoc
     */
    delegateEvents(events) {
        ProductKitFormView.__super__.delegateEvents.call(this, events);
        // Handler is moved to parent element to allow preventing submit by validator
        this.$el.parent().on(`submit${this.eventNamespace()}`, this.onSubmit.bind(this));
    },

    /**
     * @inheritdoc
     */
    undelegateEvents() {
        if (this.$el) {
            // this.$el might be not set yet
            this.$el.parent().off(this.eventNamespace());
        }
        ProductKitFormView.__super__.undelegateEvents.call(this);
    },

    /**
     * Handler on submit form
     * @param e
     */
    onSubmit(e) {
        e.preventDefault();
    },

    /**
     * Gets actual total price
     * @param {Event} e
     */
    getSubtotal(e) {
        if (!this.$el.validate().form()) {
            return;
        }

        const data = this.$el.serializeArray();

        if (!this._activeAjaxActions) {
            this._activeAjaxActions = 0;
        }

        $.ajax({
            type: 'POST',
            url: routing.generate(this.subtotalUrl, {
                productId: urlHelper.encodeURI(this.productId),
                getSubtotal: true
            }),
            beforeSend: () => {
                this._activeAjaxActions++;
                $(`#${this.$el.attr('id')}totals`).addClass(this.maskClass);
            },
            data: data,
            success: response => {
                if (this.disposed) {
                    return;
                }

                const {subtotal} = response;

                if (subtotal) {
                    $(`#${this.$el.attr('id')}amount`).text(
                        numberFormatter.formatCurrency(subtotal.amount, subtotal.currency)
                    );
                }
            },
            complete: () => {
                if (this.disposed) {
                    return;
                }
                this._activeAjaxActions--;
                if (this._activeAjaxActions === 0) {
                    $(`#${this.$el.attr('id')}totals`).removeClass(this.maskClass);
                }
            }
        });
    },

    /**
     * Handler on change
     * @param {Event} e
     */
    onProductChange(e) {
        this.lockRelatedElements($(e.target));
        this.getSubtotal(e);
    },

    /**
     * Makes specific elements to be in "readonly" mode
     * @param {jQuery.Element} $el
     */
    lockRelatedElements($el) {
        const $relatedElements = $($el.data('relatedElements'));

        if (Boolean($el.val())) {
            $relatedElements.each((i, el) => {
                $(el).removeAttr('readonly');

                if (!$(el).val()) {
                    $(el).val($(el).data('value') || 1);
                }
            });
        } else {
            if (!$el.is(':checked')) {
                return;
            }
            $relatedElements.each((i, el) => {
                $(el).val('').attr('readonly', true);
            });
        }
    }
});

export default ProductKitFormView;