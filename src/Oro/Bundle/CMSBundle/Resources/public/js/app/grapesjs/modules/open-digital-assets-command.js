import _ from 'underscore';
import routing from 'routing';
import DigitalAssetDialogWidget from 'orodigitalasset/js/widget/digital-asset-dialog-widget';

const openDigitalAssetsCommand = {
    options: {
        title: null,
        routeName: null,
        routeParams: null,
        loadingElement: null,
        target: null,
        onSelect: function() {},
        onClose: function() {}
    },

    dialog: null,

    constructor: function OpenDigitalAssetsCommand(...args) {
        OpenDigitalAssetsCommand.__super__.constructor.apply(this, args);
    },

    /**
     * @param {object} editor
     * @param {object} sender
     * @param {object} options
     * @returns {openDigitalAssetsCommand}
     */
    run: function(editor, sender, options) {
        this.options = {
            ...this.options,
            ...options
        };

        if (!options.routeName) {
            throw new TypeError('Missing required option: routeName');
        }

        this.dialog = this._openChooseDialog(editor);
        this.dialog.on('grid-row-select', this._onGridRowSelect.bind(this, editor));
        this.dialog.on('close', this._onCloseDialog.bind(this, editor));
        this.dialog.render();

        return this;
    },

    stop: function(editor) {
        this.dialog.dispose();

        return this;
    },

    _onCloseDialog(editor) {
        this.options.onClose(this);
        editor.stopCommand(this.id);
    },

    /**
     * @param {object} data
     * @private
     */
    _onGridRowSelect: function(editor, data) {
        this.options.onSelect(data.model, this);
        editor.stopCommand(this.id);
    },

    _openChooseDialog: function(editor) {
        return new DigitalAssetDialogWidget({
            title: this.options.title,
            url: routing.generate(
                this.options.routeName,
                _.extend(editor.Config.requestParams, this.options.routeParams || {})
            ),
            dialogOptions: {
                modal: true
            }
        });
    }
};

export default openDigitalAssetsCommand;
