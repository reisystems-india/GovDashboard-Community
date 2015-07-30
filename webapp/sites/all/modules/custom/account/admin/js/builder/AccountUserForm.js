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
        throw new Error('AccountUserForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('AccountUserForm requires GD');
    }

    var GD = global.GD;

    global.GD.AccountUserForm = GD.View.extend({
        object: null,
        container: null,
        options: null,
        formContainer: null,
        emailContainer: null,
        emailForm: null,
        lastNameContainer: null,
        lastNameForm: null,
        firstNameContainer: null,
        firstNameForm: null,
        groupsContainer: null,
        groupsForm: null,
        groupsInput: null,
        groupList: null,
        buttonsContainer: null,
        cancelButton: null,
        createButton: null,

        init: function(object, container, options) {
            this.object = object;
            this.container = container;
            this.options = options;
            this.initVariables();
        },

        initVariables: function() {
            this.formContainer = null;
            this.emailContainer = null;
            this.emailForm = null;
            this.lastNameContainer = null;
            this.lastNameForm = null;
            this.firstNameContainer = null;
            this.firstNameForm = null;
            this.groupsContainer = null;
            this.groupsForm = null;
            this.groupsInput = null;
            this.groupList = null;
            this.buttonsContainer = null;
            this.cancelButton = null;
            this.createButton = null;
        },

        getGroupList: function() {
            var _this = this;
            $.ajax({
                url: '/api/group.json'
            }).done(function(d) {
                _this.renderGroupList(d);
            });
        },

        renderGroupList: function(groups) {
            for (var i = 0; i < groups.length; i++) {
                var option = $('<option></option>');
                option.attr('value', groups[i]['id']);
                option.text(groups[i]['name']);
                this.getGroupsInput().append(option);
            }

            this.getGroupsForm().empty();
            this.getGroupsForm().append(this.getGroupsInput());

            this.getGroupsInput().multiselect({
                maxHeight: 200,
                buttonWidth: '100%',
                numberDisplayed: 2
            });

            this.groupList = groups;
        },

        getGroupsInput: function() {
            if (!this.groupsInput) {
                this.groupsInput = $('<select class="multiselect" multiple="multiple" style="width: 100%;"></select>');
            }

            return this.groupsInput;
        },

        getGroupsForm: function() {
            if (!this.groupsForm) {
                this.groupsForm = $('<div class="col-sm-8"></div>');
                if (!this.groupList) {
                    this.getGroupList();
                    this.groupsForm.append($('<div class="ldng" style="height: 50px;"></div>'));
                } else {
                    this.groupsForm.append(this.getGroupsInput());
                }
            }

            return this.groupsForm;
        },

        getGroupsContainer: function() {
            if (!this.groupsContainer) {
                this.groupsContainer = $('<div class="form-group"></div>');
                this.groupsContainer.append('<label class="col-sm-4 control-label">Groups</label>', this.getGroupsForm());
            }

            return this.groupsContainer;
        },

        getEmailForm: function() {
            if (!this.emailForm) {
                this.emailForm = $('<input type="text" class="form-control"/>');
            }

            return this.emailForm;
        },

        getEmailContainer: function() {
            if (!this.emailContainer) {
                this.emailContainer = $('<div class="form-group"></div>');
                var container = $('<div class="col-sm-8"></div>');
                container.append(this.getEmailForm());
                this.emailContainer.append('<label class="col-sm-4 control-label">Email</label>', container);
            }

            return this.emailContainer;
        },

        getLastNameForm: function() {
            if (!this.lastNameForm) {
                this.lastNameForm = $('<input type="text" class="form-control"/>');
            }

            return this.lastNameForm;
        },

        getLastNameContainer: function() {
            if (!this.lastNameContainer) {
                this.lastNameContainer = $('<div class="form-group"></div>');
                var container = $('<div class="col-sm-8"></div>');
                container.append(this.getLastNameForm());
                this.lastNameContainer.append('<label class="col-sm-4 control-label">Last Name</label>', container);
            }

            return this.lastNameContainer;
        },

        getFirstNameForm: function() {
            if (!this.firstNameForm) {
                this.firstNameForm = $('<input type="text" class="form-control"/>');
            }

            return this.firstNameForm;
        },

        getFirstNameContainer: function() {
            if (!this.firstNameContainer) {
                this.firstNameContainer = $('<div class="form-group"></div>');
                var container = $('<div class="col-sm-8"></div>');
                container.append(this.getFirstNameForm());
                this.firstNameContainer.append('<label class="col-sm-4 control-label">First Name</label>', container);
            }

            return this.firstNameContainer;
        },

        getCreateButton: function() {
            if (!this.createButton) {
                this.createButton = $('<button type="button" class="btn btn-default">Create</button>');

                var _this = this;
                this.createButton.click(function() {
                    _this.createUser();
                });
            }

            return this.createButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="btn btn-default" style="margin-right: 10px;">Cancel</button>');

                var _this = this;
                this.cancelButton.click(function() {
                    _this.cancelCreate();
                });
            }

            return this.cancelButton;
        },

        getButtonsContainer: function() {
            if (!this.buttonsContainer) {
                this.buttonsContainer = $('<div class="pull-right"></div>');
                this.buttonsContainer.append(this.getCancelButton(), this.getCreateButton());
            }

            return this.buttonsContainer;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="bldr-acc-usr-frm form-horizontal"></div>');
                this.formContainer.append(this.getFirstNameContainer());
                this.formContainer.append(this.getLastNameContainer());
                this.formContainer.append(this.getEmailContainer());
                this.formContainer.append(this.getGroupsContainer());
                this.formContainer.append(this.getButtonsContainer());
            }

            return this.formContainer;
        },

        getGroups: function() {
            return this.getGroupsInput().val();
        },

        getEmail: function() {
            return this.getEmailForm().val();
        },

        getLastName: function() {
            return this.getLastNameForm().val();
        },

        getFirstName: function() {
            return this.getFirstNameForm().val();
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        },

        cancelCreate: function() {
            this.clear();
            UserListActions.gotoLanding();
        },

        validateUser: function() {
            var messages = [];

            var firstName = this.getFirstName();
            if (!firstName) {
                this.getFirstNameContainer().addClass('has-error');
                messages.push('First Name : Field is required');
            }

            var lastName = this.getLastName();
            if (!lastName) {
                this.getLastNameContainer().addClass('has-error');
                messages.push('Last Name : Field is required');
            }

            var email = this.getEmail();
            if (!email) {
                this.getEmailContainer().addClass('has-error');
                messages.push('Email : Field is required');
            }

            var groups = this.getGroups();
            if (!groups) {
                this.getGroupsContainer().addClass('has-error');
                messages.push('Groups : At least one group is required');
            }

            return messages;
        },

        createUser: function() {
            this.clearErrors();

            var messages = this.validateUser();
            if (messages.length == 0) {
                this.getCreateButton().addClass('disabled');
                this.getCancelButton().addClass('disabled');
                var loading = $('<div style="height: 50px; width: 50px; position: absolute; top: 65.5px; left: 275px; z-index: 1;"><div class="ldng"></div></div>');
                this.container.append(loading);
                this.getFormContainer().css('opacity', '.5');

                var _this = this;
                $.ajax({
                    url: '/api/user.json',
                    type: 'POST',
                    data: {
                        'user': {
                            email: this.getEmail(),
                            firstname: this.getFirstName(),
                            lastname: this.getLastName(),
                            roles: this.getGroups()
                        }
                    }
                }).done(function() {
                    _this.clear();
                    UserListLayout.getInstance().getUserTileGrid().invalidateCache();
                    UserListActions.gotoLanding();
                    UserListLayout.getInstance().getUserTileGrid().data.sortByProperty('fullname', true);
                    MessageSection.showMessage('User Successfully Created');
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    MessageSection.showMessage([errorThrown], 'error');
                }).always(function() {
                    _this.getCreateButton().removeClass('disabled');
                    _this.getCancelButton().removeClass('disabled');
                    _this.getFormContainer().css('opacity', '1');
                    loading.remove();
                });
            } else {
                MessageSection.showMessage(messages, 'error');
            }
        },

        clearErrors: function() {
            MessageSection.clearMessages();
            this.getFirstNameContainer().removeClass('has-error');
            this.getLastNameContainer().removeClass('has-error');
            this.getEmailContainer().removeClass('has-error');
            this.getGroupsContainer().removeClass('has-error');
        },

        clear: function() {
            this.clearErrors();

            this.getFirstNameForm().val('');
            this.getLastNameForm().val('');
            this.getEmailForm().val('');

            var values = this.getGroups();
            for (var key in values) {
                this.getGroupsInput().multiselect('deselect', values[key]);
            }
        }
    });

})(window ? window : window, jQuery);
