define(function(require) {
    'use strict';

    var OrderTaxesComponent;
    var _ = require('underscore');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var TaxFormatter = require('orotax/js/formatter/tax');

    /**
     * @export orotax/js/app/components/order-taxes-component
     * @extends oroui.app.components.base.Component
     * @class orotax.app.components.OrderTaxesComponent
     */
    OrderTaxesComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                totalsTemplate: '#order-taxes-totals-template',
                collapseSelector: '[data-role="order-taxes-totals"]'
            }
        },

        /**
         * @property {Object}
         */
        totalsTemplate: null,

        /**
         * @inheritDoc
         */
        constructor: function OrderTaxesComponent() {
            OrderTaxesComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.on('entry-point:order:trigger:totals', this.appendTaxResult, this);

            this.totalsTemplate = $(this.options.selectors.totalsTemplate).html();
        },

        appendTaxResult: function(totals) {
            var subtotals = _.extend({subtotals: {}}, totals).subtotals;
            _.map(_.where(subtotals, {type: 'tax'}), _.bind(this.prepareItem, this));
        },

        /**
         * Formats data in a subtotals item
         *
         * @param {Object} item
         * @param {number} index
         */
        prepareItem: function(item, index) {
            item.data.total = TaxFormatter.formatItem(item.data.total);
            item.data.shipping = TaxFormatter.formatItem(item.data.shipping);
            item.data.taxes = _.map(item.data.taxes, TaxFormatter.formatTax);

            item.data.show = $(this.options.selectors.collapseSelector).eq(index).hasClass('show');
            item.template = this.totalsTemplate;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('entry-point:order:trigger:totals', this.appendTaxResult, this);

            OrderTaxesComponent.__super__.dispose.call(this);
        }
    });

    return OrderTaxesComponent;
});
