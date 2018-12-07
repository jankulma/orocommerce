define(function(require) {
    'use strict';

    var ListItemProductPricesView;
    var BaseView = require('oroui/js/app/views/base/view');
    var ElementsHelper = require('orofrontend/js/app/elements-helper');
    var layout = require('oroui/js/layout');
    var BaseModel = require('oroui/js/app/models/base/model');
    var PricesHelper = require('oropricing/js/app/prices-helper');
    var Popover = require('bootstrap-popover');
    var _ = require('underscore');
    var NumberFormatter = require('orolocale/js/formatter/number');
    var ListItemProductPricesSubView = require('oropricing/js/app/views/list-item-product-prices-subview');

    ListItemProductPricesView = BaseView.extend(_.extend({}, ElementsHelper, {
        pricesHintContent: require('tpl!oropricing/templates/product/list-item-prices-tier-table.html'),

        elements: {
            prices: '[data-name="prices"]',
            pricesHint: '[data-name="prices-hint"]'
        },

        defaultOptions: {
            showValuePrice: true,
            showListedPrice: true,
            showHint: true,
            doUpdateQtyForUnit: true,
            changeUnitLabel: false
        },

        modelAttr: {
            prices: {},
            qtyCheckedForUnit: {}
        },

        /**
         * @inheritDoc
         */
        constructor: function ListItemProductPricesView() {
            ListItemProductPricesView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            ListItemProductPricesView.__super__.initialize.apply(this, arguments);
            this.deferredInitializeCheck(options, ['productModel']);
        },

        /**
         * @inheritDoc
         */
        deferredInitialize: function(options) {
            options = _.defaults(options, this.defaultOptions);
            this.initModel(options);
            if (!this.model) {
                return;
            }

            this.showHint = options.showHint;
            this.showListedPrice = options.showListedPrice;
            this.showValuePrice = options.showValuePrice;
            this.doUpdateQtyForUnit = options.doUpdateQtyForUnit;
            this.changeUnitLabel = options.changeUnitLabel;

            this.initializeElements(options);
            this.render();
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            this.disposeElements();

            if (this.model) {
                this.model.off(null, null, this);
            }

            ListItemProductPricesView.__super__.dispose.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            if (this.doUpdateQtyForUnit) {
                this.updateQtyForUnit();
                this.model.off('change:unit', this.updateQtyForUnit, this);
                this.model.on('change:unit', this.updateQtyForUnit, this);
            }

            if (this.showListedPrice || this.showValuePrice) {
                this.renderPriceBlock();
            }

            if (this.showHint) {
                this.renderHint();
            }

            return this;
        },

        /**
         * Update qty in case when it less than allowed by price list
         */
        updateQtyForUnit: function() {
            var unit = this.model.get('unit');
            var qtyCheckedForUnit = this.model.get('qtyCheckedForUnit');
            if (!_.isEmpty(qtyCheckedForUnit[unit])) {
                return false;
            }

            var prices = this.model.get('pricesByUnit');
            if (_.isEmpty(prices) || _.isEmpty(prices[unit])) {
                return false;
            }

            qtyCheckedForUnit[unit] = true;
            var lastPrice = _.last(prices[unit]);
            if (this.model.get('quantity') < lastPrice.quantity) {
                this.model.set('quantity', lastPrice.quantity);
            }

            return true;
        },

        renderHint: function() {
            var $pricesHint = this.getElement('pricesHint');
            var prices = this.getPreparedPriceForHint();
            if (0 === $pricesHint.length || 0 === _.keys(prices).length) {
                return;
            }

            if (!$pricesHint.data(Popover.DATA_KEY)) {
                layout.initPopoverForElements($pricesHint, {
                    'container': 'body',
                    'placement': 'bottom',
                    'trigger': 'hover',
                    'close': false,
                    'class': 'prices-hint-content'
                }, true);
            }

            $pricesHint.data(Popover.DATA_KEY).updateContent(this.pricesHintContent({
                prices: prices,
                formatter: NumberFormatter
            }));
        },

        getPreparedPriceForHint: function() {
            return this.model.get('pricesByUnit');
        },

        renderPriceBlock: function(showErrors) {
            if (0 === this.getElement('prices').length) {
                if (showErrors) {
                    console.error('Price block expected but doesn\'t exist.');
                }
                return;
            }

            if (false === _.isEmpty(this.model.get('prices'))) {
                this.subview('prices', new ListItemProductPricesSubView({
                    autoRender: true,
                    el: this.getElement('prices'),
                    model: this.model,
                    showListedPrice: this.showListedPrice,
                    showValuePrice: this.showValuePrice,
                    changeUnitLabel: this.changeUnitLabel
                }));
            } else {
                this.getElement('prices').html(
                    _.__('oro.pricing.product_prices.empty_prices')
                );
            }
        },

        initModel: function(options) {
            this.modelAttr = _.extend({}, this.modelAttr, options.modelAttr || {});
            if (options.productModel) {
                this.model = options.productModel;
                this._onPricesChanged();
            }

            if (!this.model) {
                this.model = new BaseModel();
            }

            this.model.off('change:prices', this._onPricesChanged, this);
            this.model.on('change:prices', this._onPricesChanged, this);

            _.each(this.modelAttr, function(value, attribute) {
                if (!this.model.has(attribute) || !_.isEmpty(value)) {
                    this.model.set(attribute, value);
                }
            }, this);
        },

        _onPricesChanged: function() {
            this.model.set(
                'pricesByUnit',
                PricesHelper.preparePrices(this.model.get('prices'))
            );
        }
    }));

    return ListItemProductPricesView;
});
