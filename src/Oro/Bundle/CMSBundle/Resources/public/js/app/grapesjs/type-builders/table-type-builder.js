import BaseTypeBuilder from 'orocms/js/app/grapesjs/type-builders/base-type-builder';
import TableEditView from '../controls/table-edit';
import TableTypeDecorator from '../controls/table-edit/table-type-decorator';

/**
 * Create table component type builder
 */
const TableTypeBuilder = BaseTypeBuilder.extend({
    /**
     * @inheritdoc
     */
    constructor: function TableTypeBuilder(options) {
        TableTypeBuilder.__super__.constructor.call(this, options);
    },

    commands: {
        'table-edit': {
            run(editor, sender, table) {
                this.tableEdit = new TableEditView({
                    container: this.editorModel.view.$el.find('#gjs-tools'),
                    table,
                    selected: editor.getSelected()
                });
            },

            stop() {
                this.tableEdit.dispose();
            }
        }
    },

    modelMixin: {
        defaults: {
            tagName: 'table',
            draggable: false,
            removable: false,
            copyable: false,
            classes: ['table']
        },

        ...TableTypeDecorator,

        init() {
            const components = this.get('components');

            if (!components.length) {
                components.add([{
                    type: 'thead'
                }, {
                    type: 'tbody'
                }]);
            }

            this.bindModelEvents();
        }
    },

    isComponent(el) {
        let result = null;

        if (el.tagName === 'TABLE') {
            result = {
                type: this.componentType
            };
        }

        return result;
    }
});

export default TableTypeBuilder;
