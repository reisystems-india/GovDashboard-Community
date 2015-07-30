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
        throw new Error('DashboardBuilder requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardBuilder requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardBuilder = GD.Builder.extend({
        dashboard: null,
        configButtons: null,
        actionButtons: null,
        canvas: null,
        forms: {},
        tagView: null,

        init: function(object, options) {
            this._super(options);
            this.initMessaging();

            this.original = new GD.Dashboard(object, { builder: this });
            new GD.Dashboard(object, { builder: this, singleton: true });
            this.dashboard = GD.Dashboard.singleton;
            this.dashboard.validate();
            $('#dashboardName').val(this.dashboard.getName());

            this.configButtons = null;
            this.actionButtons = null;

            this.initConfigButtons();
            this.initActionButtons();
            this.initCanvas();
            this.initCustomViewEditor();
            this.initTagView();
            this.attachEventHandlers();
        },
        run: function () {
            this.admin.run();

            if ( this.dashboard ) {
                this.canvas.renderWidgets();
            }
        },

        initConfigButtons: function() {
            this.configButtons = {
                report: new GD.DashboardReportsButton(null, {builder: this}),
                filter: new GD.DashboardFilterButton(null, {builder: this}),
                link: new GD.DashboardLinkButton(null, {builder: this}),
                display: new GD.DashboardDisplayButton(null, {builder: this})
            };
        },

        initActionButtons: function () {
            this.actionButtons = {
                "cancel": new GD.DashboardCancelButton(null, {builder: this}),
                "save": new GD.DashboardSaveButton(null, {builder: this}),
                "saveAs": (!this.dashboard.isNew() ? new GD.DashboardSaveAsButton(null, {builder: this}) : null),
                "delete": (!this.dashboard.isNew() ? new GD.DashboardDeleteButton(null, {builder: this}) : null)
            };
        },

        initMessaging: function() {
            global.GD.DashboardBuilderMessagingView = new GD.MessagingView('#gd-admin-messages');
        },

        initCanvas: function() {
            this.canvas = new GD.DashboardCanvas(null, {builder: this});
        },

        initCustomViewEditor: function() {
            global.GD.DashboardBuilderCustomViewEditor = new GD.BuilderCustomViewEditor(this.getDashboard(), '#dashboardEditorContainer', {builder: this});
            global.GD.DashboardBuilderCustomViewEditor.attachEventHandlers();
        },

        initTagView: function() {
            var _this = this;
            $.when($.ajax({
                url: '/api/taxonomy_term.json'
            }), $.ajax({
                url: '/api/taxonomy_vocabulary.json'
            })).done(function(tResponse, vResponse) {
                var terms = tResponse[0];
                var vocabularies = vResponse[0];
                var vocab = {};
                var tags = [];

                $.each(vocabularies, function(i, v) {
                    vocab[v['vid']] = tags.length;
                    tags.push({ children: [], name: v['name']});
                });

                $.each(terms, function(i, term) {
                    if (typeof vocab[term['vid']] !== 'undefined') {
                        tags[vocab[term['vid']]]['children'].push({ value: term['tid'], text: term['name'] });
                    }
                });

                _this.getTagView().setOptions(tags, true);
                _this.getTagView().render();
                _this.getTagView().setValue(_this.dashboard.getTags());
            });
        },

        getTagView: function() {
            if (!this.tagView) {
                this.tagView = new GD.ViewChosen(null, '#gd-admin-footer', {title:'Tags', limit:10});
            }

            return this.tagView;
        },

        hasChanges: function() {
            return false;
        },

        saveDashboard: function() {
            this.dashboard.setCustomView(global.GD.DashboardBuilderCustomViewEditor.getCustomViewValue());
            this.dashboard.setTags(this.getTagView().getValues());

            global.GD.DashboardBuilderMessagingView.clean();
            var _this = this;

            if ( !this.dashboard.isNew() ) {
                GD.DashboardFactory.updateDashboard(
                    this.dashboard.getId(),
                    this.dashboard.getConfig(),
                    function (data) {
                        global.GD.DashboardBuilderMessagingView.addNotices('Dashboard was updated successfully.');
                        global.GD.DashboardBuilderMessagingView.displayMessages();
                        $(document).trigger({
                            type: 'post.dashboard.update',
                            dashboard: data
                        });
                    },
                    function(jqXHR, textStatus, errorThrown) {
                        _this.actionButtons.save.changeButton('reset');
                        global.GD.DashboardBuilderMessagingView.addErrors(jqXHR.responseText);
                        global.GD.DashboardBuilderMessagingView.displayMessages();
                    }, function(){
                        _this.actionButtons.save.changeButton('reset');
                    }
                );
            } else {
                GD.DashboardFactory.createDashboard(
                    this.dashboard.getConfig(),
                    function (data) {
                        global.GD.DashboardBuilderMessagingView.addNotices('Dashboard was created successfully.');
                        global.GD.DashboardBuilderMessagingView.displayMessages();
                        $(document).trigger({
                            type: 'post.dashboard.create'
                        });

                        location.href = "/cp/dashboard/"+data.id;
                    },
                    function(jqXHR, textStatus, errorThrown) {
                        _this.actionButtons.save.changeButton('reset');
                        global.GD.DashboardBuilderMessagingView.addErrors(jqXHR.responseText);
                        global.GD.DashboardBuilderMessagingView.displayMessages();
                    }, function(){
                        _this.actionButtons.save.changeButton('reset');
                    }
                );
            }
        },

        copyDashboard: function ( name ) {
            this.dashboard.setCustomView(global.GD.DashboardBuilderCustomViewEditor.getCustomViewValue());

            global.GD.DashboardBuilderMessagingView.clean();
            var newDashboard = $.extend(true, {}, this.dashboard);
            newDashboard.setId(-1);
            newDashboard.setName(name);

            var _this = this;

            GD.DashboardFactory.createDashboard(
                newDashboard.getConfig(),
                function (data) {
                    global.GD.DashboardBuilderMessagingView.addNotices('Dashboard was copied successfully. <a href="/cp/dashboard/'+data.id+'" class="alert-link">Goto Dashboard</a>');
                    global.GD.DashboardBuilderMessagingView.displayMessages();
                    $(document).trigger({
                        type: 'post.dashboard.copy'
                    });
                },
                function(jqXHR, textStatus, errorThrown) {
                    _this.actionButtons.saveAs.changeButton('reset');
                    _this.actionButtons.saveAs.getEditorWindow().modal('hide');
                    global.GD.DashboardBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.DashboardBuilderMessagingView.displayMessages();
                }, function(){
                    _this.actionButtons.saveAs.changeButton('reset');
                }
            );

        },

        deleteDashboard: function() {
            var _this = this;
            GD.DashboardFactory.deleteDashboard(
                this.dashboard.getId(),
                function (data) {
                    global.GD.DashboardBuilderMessagingView.addNotices('Dashboard was deleted successfully.');
                    global.GD.DashboardBuilderMessagingView.displayMessages();
                    $(document).trigger({
                        type: 'post.dashboard.delete'
                    });
                    location.href = '/cp/dashboard?ds=' + _this.getDatasourceName();
                },
                function(jqXHR, textStatus, errorThrown) {
                    global.GD.DashboardBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.DashboardBuilderMessagingView.displayMessages();
                }, function(){
                    _this.actionButtons.delete.changeButton('reset');
                });
        },

        getDashboard: function () {
            return this.dashboard;
        },

        validateReports: function() {
            if (this.dashboard.getReportIds().length < 1) {
                this.configButtons['filter'].disable();
                this.configButtons['link'].disable();
                this.configButtons['display'].disable();
                global.GD.DashboardBuilderMessagingView.addErrors('You must select at least one report');
                global.GD.DashboardBuilderMessagingView.displayMessages();

                var _this = this;
                $(document).on('add.dashboard.report', function() {
                    _this.configButtons['filter'].enable();
                    _this.configButtons['link'].enable();
                    _this.configButtons['display'].enable();
                    global.GD.DashboardBuilderMessagingView.clean();
                });
            }
        },

        updateNotifications: function() {
            this.configButtons['report'].updateNotification(true);
            this.configButtons['filter'].updateNotification(true);
            this.configButtons['link'].updateNotification(true);
        },

        attachEventHandlers: function() {
            var _this = this;
            $(document).on('post.dashboard.update', function(e) {
                _this.original = new GD.Dashboard(e['dashboard'], { builder: this });
                _this.updateNotifications();
            });

            if (this.dashboard.isNew()) {
                $(document).on('add.dashboard.report', function() {
                    _this.configButtons['filter'].enable();
                    _this.configButtons['link'].enable();
                    _this.configButtons['display'].enable();
                });
            }

            $(document).on('remove.dashboard.report', function() {
                _this.validateReports();
            });

            $('#dashboardName').on('blur',function(){
                _this.getDashboard().setName($(this).val());
            });

            window.onbeforeunload = function() {
                _this.dashboard.setCustomView(global.GD.DashboardBuilderCustomViewEditor.getCustomViewValue());
                if (!GD.Dashboard.compareDashboards(_this.original, _this.dashboard) && !_this.dashboard.isNew()) {
                    return 'You have unsaved changes. Are you sure you want to leave this page? This action cannot be undone.'
                }
            }
        }
    });

    //  Register custom implementations of Lookup Filter Form
    GD.FilterFormFactory.forms['string'] = GD.DashboardLookupFilterForm;
    GD.FilterFormFactory.forms['URI'] = GD.DashboardLookupFilterForm;
    //GD.FilterFormFactory.forms['integer'] = GD.DashboardLookupFilterForm;

})(typeof window === 'undefined' ? this : window, jQuery);