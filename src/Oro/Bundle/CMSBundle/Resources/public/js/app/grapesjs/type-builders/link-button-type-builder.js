import __ from 'orotranslation/js/translator';
import BaseTypeBuilder from 'orocms/js/app/grapesjs/type-builders/base-type-builder';

const LinkButtonTypeBuilder = BaseTypeBuilder.extend({
    parentType: 'link',

    button: {
        label: __('oro.cms.wysiwyg.component.link_button.label'),
        category: 'Basic',
        attributes: {
            'class': 'fa fa-hand-pointer-o'
        }
    },

    modelMixin: {
        defaults: {
            tagName: 'a',
            classes: ['btn', 'btn--info'],
            style: {},
            components: [{
                type: 'textnode',
                content: __('oro.cms.wysiwyg.component.link_button.content')
            }]
        }
    },

    constructor: function LinkButtonTypeBuilder(options) {
        LinkButtonTypeBuilder.__super__.constructor.call(this, options);
    },

    isComponent(el) {
        let result = null;

        if (el.tagName === 'A' && el.classList.contains('btn')) {
            result = {
                type: this.componentType
            };
        }

        return result;
    }
});

export default LinkButtonTypeBuilder;
