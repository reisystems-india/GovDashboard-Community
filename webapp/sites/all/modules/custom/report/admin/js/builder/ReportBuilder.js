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
        throw new Error('ReportBuilder requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportBuilder requires GD');
    }

    var GD = global.GD;

    var ReportBuilder = GD.Builder.extend({
        report: {},
        configButtons: null,
        actionButtons: null,
        typeButtons: null,
        canvas: {},
        forms: {},
        tagView: null,
        originalReport: null,
        reportDataset: null,
        reportTypeToolbar:null,

        init: function(object, options) {
            this._super(options);

            this.setReport(object);

            this.initTypeButtons();
            this.initConfigButtons();
            this.initActionButtons();
            this.initMessaging();
            this.initCanvas();
            this.initCustomViewEditor();
            this.initTagView();
            this.attachEventHandlers();
        },

        run: function () {
            this.admin.run();

            $(document).trigger({
                type: 'loaded.report'
            });

            $('#reportName').val(this.getReport().getName());

            if ( !this.getReport().isNew() ) {
                this.getCanvas().loadPreview();
            } else {
                this.configButtons.data.enable();
                this.configButtons.data.openForm();
            }

            var uri = new GD.Util.UriHandler();
            if ( uri.getParam('dataset') ) {
                $(document).trigger({
                    type: 'changed.report.dataset'
                });
            }
        },

        initTypeButtons: function() {
            this.reportTypeToolbar = new GD.ReportTypeToolbar(null,$('#reportTypeToolbarContainer'),{builder:this}).render();
        },

        getReportTypeToolbar: function () {
            return this.reportTypeToolbar;
        },

        initConfigButtons: function() {
            this.configButtons = {
                data: new GD.ReportDataButton(null, {builder: this}),
                column: new GD.ReportColumnButton(null, {builder: this}),
                filter: new GD.ReportFilterButton(null, {builder: this}),
                configure: new GD.ReportConfigureButton(null, {builder: this}),
                visualize: new GD.ReportVisualizeButton(null, {builder: this})
            };
        },

        initActionButtons: function () {
            this.actionButtons = {
                "cancel": new GD.ReportCancelButton(null, {builder: this}),
                "save": new GD.ReportSaveButton(null, {builder: this}),
                "saveAs": (!this.getReport().isNew() ? new GD.ReportSaveAsButton(null, {builder: this}) : null),
                "delete": (!this.getReport().isNew() ? new GD.ReportDeleteButton(null, {builder: this}) : null)
            };
        },

        initMessaging: function() {
            global.GD.ReportBuilderMessagingView = new GD.MessagingView('#gd-admin-messages');
        },

        initCanvas: function() {
            this.canvas = new GD.ReportCanvas(this);
        },

        initCustomViewEditor: function() {
            this.customViewEditor = new GD.ReportCustomViewEditor(this, $('#reportEditorContainer')).attachEventHandlers();
        },

        getCustomViewEditor: function () {
            return this.customViewEditor;
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
                    if (typeof vocab[term['vid']] != 'undefined') {
                        tags[vocab[term['vid']]]['children'].push({ value: term['tid'], text: term['name'] });
                    }
                });

                _this.getTagView().setOptions(tags, true);
                _this.getTagView().render();
                _this.getTagView().setValue(_this.report.getTags());
            });
        },

        getCanvas: function () {
            return this.canvas;
        },

        getTagView: function() {
            if ( !this.tagView ) {
                this.tagView = new GD.ViewChosen(null, '#gd-admin-footer', {title:'Tags', limit:10});
            }

            return this.tagView;
        },

        hasChanges: function() {
            return false;
        },
        configCleanup: function(config){
            //remove unwanted series props
            var conf = config.config;
            if(conf.hasOwnProperty("visual")){
                if(conf.visual.hasOwnProperty("series")){
                    var series = conf.visual.series,
                        i;
                    for(i in series){
                        delete series[i].columnId;
                        delete series[i].new;
                        delete series[i].val;
                        delete series[i].text;
                        delete series[i].unsupported;
                    }

                }

                if(conf.visual.hasOwnProperty("traffic")){
                    var traffic = conf.visual.traffic,
                        j;
                    for(j in traffic){
                        delete traffic[j].trafficColumn;
                        delete traffic[j].displayName;
                        delete traffic[j].columnId;
                        delete traffic[j].columnType;
                        delete traffic[j].val;
                        delete traffic[j].text;
                        delete traffic[j].unsupported;

                    }

                }
            }
        },
        saveReport: function() {

            this.getReport().setTags(this.getTagView().getValues());

            global.GD.ReportBuilderMessagingView.clean();
            var _this = this;
            var config = this.getReport().getConfig();
            this.configCleanup(config);
            $.when($(document).trigger('preprocess.report.save', config)).done(function(){
                if ( !_this.getReport().isNew() ) {
                    GD.ReportFactory.updateReport(
                        _this.getReport().getId(),
                        config,
                        function (data) {
                            global.GD.ReportBuilderMessagingView.addNotices('Report was updated successfully.');
                            global.GD.ReportBuilderMessagingView.displayMessages();
                            _this.originalReport = $.extend(true, {}, _this.report);
                            $(document).trigger({
                                type: 'report.update.post',
                                report: data
                            });
                            _this.updateNotifications();
                        },
                        function(jqXHR, textStatus, errorThrown) {
                            _this.actionButtons.save.changeButton('reset');
                            global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                            global.GD.ReportBuilderMessagingView.displayMessages();
                        }, function(){
                            _this.actionButtons.save.changeButton('reset');
                        }
                    );
                } else {
                    GD.ReportFactory.createReport(
                        config,
                        function (data) {
                            global.GD.ReportBuilderMessagingView.addNotices('Report was created successfully.');
                            global.GD.ReportBuilderMessagingView.displayMessages();
                            $(document).trigger({
                                type: 'report.create.post'
                            });

                            location.href = "/cp/report/"+data.id;
                        },
                        function(jqXHR, textStatus, errorThrown) {
                            _this.actionButtons.save.changeButton('reset');
                            global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                            global.GD.ReportBuilderMessagingView.displayMessages();
                        }, function(){
                            _this.actionButtons.save.changeButton('reset');
                        }
                    );
                }
            });
        },

        copyReport: function ( name ) {

            global.GD.ReportBuilderMessagingView.clean();
            var newReport = $.extend(true, {}, this.report);
            newReport.setId(-1);
            newReport.setName(name);

            var _this = this;

            GD.ReportFactory.createReport(
                newReport.getConfig(),
                function (data) {
                    global.GD.ReportBuilderMessagingView.addNotices('Report was copied successfully. <a href="/cp/report/'+data.id+'" class="alert-link">Goto Report</a>');
                    global.GD.ReportBuilderMessagingView.displayMessages();
                    $(document).trigger({
                        type: 'report.copy.post'
                    });
                },
                function(jqXHR, textStatus, errorThrown) {
                    _this.actionButtons.saveAs.changeButton('reset');
                    _this.actionButtons.saveAs.getEditorWindow().modal('hide');
                    global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.ReportBuilderMessagingView.displayMessages();
                }, function(){
                    _this.actionButtons.saveAs.changeButton('reset');
                }
            );

        },

        getReportReference: function(){
            return GD.ReportFactory.getReportReference(
                this.report.getId(),
                null, // must be used as promise
                function(jqXHR, textStatus, errorThrown) {
                    global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.ReportBuilderMessagingView.displayMessages();
                }
            );
        },

        deleteReport: function() {
            var _this = this;
            GD.ReportFactory.deleteReport(
                this.report.getId(),
                function (data) {
                    global.GD.ReportBuilderMessagingView.addNotices('Report was deleted successfully.');
                    global.GD.ReportBuilderMessagingView.displayMessages();
                    $(document).trigger({
                        type: 'report.delete.post'
                    });
                    location.href = '/cp/report?ds=' + _this.getDatasourceName();
                },
                function(jqXHR, textStatus, errorThrown) {
                    global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.ReportBuilderMessagingView.displayMessages();
                }, function(){
                    _this.actionButtons.delete.changeButton('reset');
                });
        },

        setReport: function ( object ) {
            var _this = this;
            _this.report = new GD.Report(object);
            _this.report.setDatasource(_this.admin.getActiveDatasource());
            _this.originalReport = $.extend(true, {}, _this.report);
            return _this.report;
        },

        getReport: function () {
            return this.report;
        },

        updateNotifications: function() {
            this.configButtons['column'].updateNotification(true);
            this.configButtons['filter'].updateNotification(true);
            this.configButtons['configure'].updateNotification(false);
        },

        attachEventHandlers: function() {
            var _this = this;

            $(document).on('report.update.post', function(e) {
                _this.originalReport = $.extend(true, {}, _this.report);
                _this.updateNotifications();
            });

            $('#reportName').on('blur',function(){
                _this.getReport().setName($(this).val());
            });

            $('#reportName').focus(function(){
               $(this).data('placeholder',$(this).attr('placeholder'))
               $(this).attr('placeholder','');
            });
            $('#reportName').blur(function(){
               $(this).attr('placeholder',$(this).data('placeholder'));
            });

            window.onbeforeunload = function() {
                //_this.getReport().setCustomView(global.GD.ReportBuilderCustomViewEditor.getCustomViewValue());
                if ( !_this.compareReports(_this.originalReport, _this.report) && !_this.report.isNew()) {
                    return 'You have unsaved changes. Are you sure you want to leave this page? This action cannot be undone.';
                }
            }
        },

        getConfigurationFlag: function ( name ) {
            if ( this.environment ) {
                if ( typeof this.environment[name] != 'undefined' ) {
                    return this.environment[name];
                }
            }
            return null;
        },

        compareReports: function(a, b) {
            var same = true;

            //same &= (a.getCustomView() == b.getCustomView());
            same &= (a.getName() == b.getName());
            same &= (a.getDescription() == b.getDescription());

            //  Same number of items
            same &= (a.getColumns().length == b.getColumns().length);
            same &= (a.getFilters().length == b.getFilters().length);

            return same;
        }
    });

    GD.ReportBuilder = ReportBuilder;

    //  Register custom implementations of Lookup Filter Form
    GD.FilterFormFactory.forms['string'] = GD.ReportLookupFilterForm;
    GD.FilterFormFactory.forms['URI'] = GD.ReportLookupFilterForm;
    //GD.FilterFormFactory.forms['integer'] = GD.ReportLookupFilterForm;

})(typeof window === 'undefined' ? this : window, jQuery);