/*
 * Copyright 2014 REI Systems, Inc.
 * 
 * This file is part of GovDashboard.
 * 
 * GovDashboard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GovDashboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GovDashboard.  If not, see <http://www.gnu.org/licenses/>.
 */

(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('DashboardListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardListForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardListForm = GD.View.extend({
        dashboard: null,
        addButton: null,
        itemList: null,
        formContainer: null,
        items: null,
        modal: null,
        removingItem: null,
        itemCallback: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.addButton = null;
            this.itemList = null;
            this.formContainer = null;
            this.items = null;
            this.modal = null;
            this.removingItem = -1;
            this.itemCallback = null;

            if (this.object) {
                this.dashboard = this.object;
            } else {
                this.dashboard = GD.Dashboard.singleton;
            }
        },

        getItems: function() { return []; },

        setItems: function() {
            this.items = [];
            var items = this.getItems();
            for (var key in items) {
                this.setItem(items, key);
            }
        },

        setItem: function(items, key) {},

        removeItemClicked: function(id) {
            this.removingItem = id;
            this.getModal().modal();
        },

        removeItem: function() {
            if (this.removingItem != -1) {
                this.getItemList().removeByValue(this.removingItem);
                this.removeItemFromDashboard();
                this.removingItem = -1;
            }
        },

        removeItemFromDashboard: function() { },

        getModalTitle: function() {
            return 'Remove Item';
        },

        getModal: function() {
            if (!this.modal) {
                this.modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>' + this.getModalTitle() + '</h4>');
                var body = $('<div class="modal-body row"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign col-md-1"></span><div style="height:25px; line-height: 25px;" class="col-md-11">Are you sure you want to delete this ' + this.getItemName().toLowerCase() + '?</div></div>');
                var footer = $('<div class="modal-footer"></div>');
                
                var removeButton = $('<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>');

                var _this = this;
                removeButton.click(function() {
                    _this.removeItem();
                });

                footer.append(removeButton);
                footer.append('<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>');
                content.append(header, body, footer);
                var dialog = $('<div class="modal-dialog"></div>');
                dialog.append(content);
                this.modal.append(dialog);
                $('body').append(this.modal);
            }

            return this.modal;
        },

        getItemName: function() {
            return 'Item';
        },

        getIcons: function() {
            return ['trash'];
        },

        getItemList: function() {
            if (!this.itemList) {
                this.itemList = new GD.BuilderListView(null, this.getFormContainer(), { 'header': this.getItemName(), 'icons': this.getIcons() });

                if (this.items) {
                    this.itemList.renderOptions(this.items);
                }
            }

            return this.itemList;
        },

        getAddButton: function() {
            if (!this.addButton) {
                this.addButton = $('<button class="btn btn-primary" style="margin-top:10px;"><span class="glyphicon glyphicon-plus"></span> Add ' + this.getItemName() + 's</button>');
            }

            return this.addButton;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div></div>');
                this.getItemList().render();
                this.formContainer.append(this.getAddButton());
            }

            return this.formContainer;
        },

        render: function(refresh) {
            if (refresh) {
                this.setItems();
                this.itemList = null;
                this.formContainer = null;
            }

            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        },

        itemClicked: function(v) {
            if (this.itemCallback) {
                this.itemCallback(this.items[v]);
            }
        },

        attachEventHandlers: function(addHandler, clickHandler) {
            this.itemCallback = clickHandler;

            if (addHandler) {
                this.getAddButton().click(function() {
                    addHandler();
                });
            }

            var _this = this;
            this.getFormContainer().find('span.bldr-lst-itm-icn-trsh').click(function(e) {
                _this.removeItemClicked($(this).attr('value'));
                e.stopPropagation();
            });

            this.getFormContainer().find('div.lst-itm-val').click(function(e) {
                _this.itemClicked($(this).attr('value'));
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);