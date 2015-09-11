/*global define*/
define(function(require) {
    'use strict';

    var EmailNotificationCollectionView;
    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var routing = require('routing');
    var EmailNotificationView = require('./email-notification-item-view');
    var BaseCollectionView = require('oroui/js/app/views/base/collection-view');
    var messenger = require('oroui/js/messenger');

    EmailNotificationCollectionView = BaseCollectionView.extend({
        template: require('tpl!oroemail/templates/email-notification/email-notification-collection-view.html'),
        itemView: EmailNotificationView,
        listSelector: '.items',
        countNewEmail: 0,

        listen: {
            'change:seen collection': 'updateViewMode',
            'reset collection': 'onResetCollection'
        },

        events: {
            'click': 'onClickIconEnvelope',
            'click button.mark-as-read': 'onClickMarkAsRead'
        },

        initialize: function(options) {
            EmailNotificationCollectionView.__super__.initialize.call(this, options);
            this.countNewEmail = parseInt(options.countNewEmail);
        },

        render: function() {
            EmailNotificationCollectionView.__super__.render.call(this);
            this.updateViewMode();
        },

        getTemplateData: function() {
            var data = EmailNotificationCollectionView.__super__.getTemplateData.call(this);
            data.userEmailsUrl = routing.generate('oro_email_user_emails');
            return data;
        },

        updateViewMode: function() {
            if (!this.isActiveTypeDropDown('notification')) {
                var $iconEnvelope = this.$el.find('.oro-dropdown-toggle .icon-envelope');
                if (this.collection.models.length === 0) {
                    this.setModeDropDownMenu('empty');
                    $iconEnvelope.removeClass('new');
                } else {
                    this.setModeDropDownMenu('content');
                    if (this.countNewEmail > 0) {
                        $iconEnvelope.addClass('new');
                    } else {
                        $iconEnvelope.removeClass('new');
                    }
                }
            }
        },

        onClickMarkAsRead: function() {
            var self = this;
            $.ajax({
                url: routing.generate('oro_email_mark_all_as_seen'),
                success: function(response) {
                    self.collection.markAllAsRead();
                    self.setCount(0);
                    if (response.successful) {
                        mediator.trigger('datagrid:doRefresh:user-email-grid');
                    }
                },
                error: function(model, response) {
                    messenger.showErrorMessage(__('oro.email.error.mark_as_read'), response.responseJSON || {});
                }
            });
        },

        resetModeDropDownMenu: function() {
            this.$el.find('.dropdown-menu').removeClass('content empty notification');

            return this;
        },

        setModeDropDownMenu: function(type) {
            this.resetModeDropDownMenu();
            this.$el.find('.dropdown-menu').addClass(type);
        },

        isActiveTypeDropDown: function(type) {
            return this.$el.find('.dropdown-menu').hasClass(type);
        },

        onResetCollection: function() {
            this.setCount(0);
        },

        onClickIconEnvelope: function() {
            if (this.isActiveTypeDropDown('notification')) {
                this.open();
                this.setModeDropDownMenu('content');
            }
            this.updateViewMode();
        },

        showNotification: function() {
            if (!this.isOpen()) {
                this.open();
                this.setModeDropDownMenu('notification');
            }
        },

        isOpen: function() {
            this.$el.hasClass('open');
        },

        close: function() {
            this.$el.removeClass('open');
        },

        open: function() {
            this.$el.addClass('open');
        }
    });

    return EmailNotificationCollectionView;
});
