define(function(require) {
    'use strict';

    const $ = require('jquery');
    const template = require('tpl-loader!orofilter/templates/filter/date-picker.html');
    require('jquery-ui/widget');

    $.widget('orofilter.itemizedPicker', {
        options: {
            title: 'Title',
            items: [],
            onSelect: $.noop,
            template: template
        },

        _create: function() {
            this.render();
            this._on({
                'click a': 'onSelect'
            });
        },

        onSelect: function(e) {
            this.options.onSelect(e.target.text);
        },

        render: function() {
            this.element.html(this.options.template({
                title: this.options.title,
                items: this.options.items
            }));
        }
    });
});
