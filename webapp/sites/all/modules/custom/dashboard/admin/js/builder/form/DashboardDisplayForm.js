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
        throw new Error('DashboardDisplayForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardDisplayForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardDisplayForm = GD.View.extend({
        header: null,
        formContainer: null,
        publicForm: null,
        publicInput: null,
        urlInput: null,
        exportForm: null,
        exportInput: null,
        customForm: null,
        customInput: null,
        widgetHeader: null,
        widgetInfo: null,
        dashboard: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.header = null;
            this.formContainer = null;
            this.publicForm = null;
            this.publicInput = null;
            this.urlInput = null;
            this.customForm = null;
            this.customInput = null;
            this.exportForm = null;
            this.exportInput = null;
            this.widgetHeader = null;
            this.widgetInfo = null;

            if (this.object) {
                this.dashboard = this.object;
            } else {
                this.dashboard = GD.Dashboard.singleton;
            }
        },

        getHeader: function() {
            if (!this.header) {
                this.header = $('<h5>Options</h5>');
            }

            return this.header;
        },

        getWidgetHeader: function() {
            if (!this.widgetHeader) {
                this.widgetHeader = $('<h5>Widgets</h5>');
            }

            return this.widgetHeader;
        },

        getWidgetInfo: function() {
            if (!this.widgetInfo) {
                this.widgetInfo = $('<p>Drag and drop any of the following to the work area to add.</p>');
            }

            return this.widgetInfo;
        },

        getTextWidget: function() {
            if (!this.textWidget) {
                // only works if wrapped in span tag
                this.textWidget = $('<span id="dashboardWidgetTextButton" data-widget="text" class="btn btn-default dropItem"><span class="glyphicon glyphicon-font"></span> Text</span>');
                this.textWidget.draggable({
                    distance: 20,
                    helper: "clone"
                });
            }

            return this.textWidget;
        },

        getImageWidget: function() {
            if (!this.imageWidget) {
                // only works if wrapped in span tag
                this.imageWidget = $('<span id="dashboardWidgetImageButton" data-widget="image" class="btn btn-default dropItem"><span class="glyphicon glyphicon-picture"></span> Image</span>');
                this.imageWidget.draggable({
                    distance: 20,
                    helper: "clone"
                });
            }

            return this.imageWidget;
        },

        getUrlInput: function() {
            if (!this.urlInput) {
                this.urlInput = $('<input type="text" class="form-control" id="dashboardOptionIsPublicUrl" />');
                this.urlInput.hide();

                if (this.dashboard) {
                    if (this.dashboard.getPublic()) {
                        if (this.dashboard.getId()) {
                            this.urlInput.val(window.location.protocol+'//'+window.location.host+'/public/dashboards?id='+this.dashboard.id).show();
                        }
                    }
                }
            }

            return this.urlInput;
        },

        getPublicForm: function() {
            if (!this.publicForm) {
                this.publicForm = $('<div class="form-group" id="dashboardOptionIsPublicGroup"></div>');
                var container = $('<div class="checkbox"></div>');
                var label = $('<label></label>');
                label.append(this.getPublicInput());
                label.append(' Allow public access');
                container.append(label);

                this.publicForm.append(container);
                this.publicForm.append(this.getUrlInput());
            }

            return this.publicForm;
        },

        getPublicInput: function() {
            if (!this.publicInput) {
                this.publicInput = $('<input type="checkbox" class="form" id="dashboardOptionIsPublicValue" />');

                if (this.dashboard) {
                    this.publicInput.prop('checked', this.dashboard.getPublic());
                }

                var _this = this;
                this.publicInput.on('change', function(){
                    if ( this.checked ) {
                        _this.dashboard.setPublic(true);
                        _this.getUrlInput().val(window.location.protocol+'//'+window.location.host+'/public/dashboards?id='+_this.dashboard.id).show();
                    } else {
                        _this.dashboard.setPublic(false);
                        _this.getUrlInput().hide();
                    }
                });
            }

            return this.publicInput;
        },

        getPublic: function() {
            return this.getPublicInput().prop('checked');
        },

        getExportForm: function() {
            if (!this.exportForm) {
                this.exportForm = $('<div class="form-group" id="dashboardOptionIsExportableGroup"></div>');
                var container = $('<div class="checkbox"></div>');
                var label = $('<label></label>');
                label.append(this.getExportInput());
                label.append(' Allow dashboard export');
                container.append(label);

                this.exportForm.append(container);
            }

            return this.exportForm;
        },

        getExportInput: function() {
            if (!this.exportInput) {
                this.exportInput = $('<input type="checkbox" class="form" id="dashboardOptionIsExportableValue" />');

                if (this.dashboard) {
                    this.exportInput.prop('checked', this.dashboard.getExportable());
                }

                var _this = this;
                this.exportInput.on('change', function(){
                    if ( this.checked ) {
                        _this.dashboard.setExportable(true);
                    } else {
                        _this.dashboard.setExportable(false);
                    }
                });
            }

            return this.exportInput;
        },
        getPrintForm: function() {
            if (!this.printForm) {
                this.printForm = $('<div class="form-group" id="dashboardOptionIsPrintableGroup"></div>');
                var container = $('<div class="checkbox"></div>');
                var label = $('<label></label>');
                label.append(this.getPrintInput());
                label.append(' Allow dashboard print');
                container.append(label);

                this.printForm.append(container);
            }

            return this.printForm;
        },

        getPrintInput: function() {
            if (!this.printInput) {
                this.printInput = $('<input type="checkbox" class="form" id="dashboardOptionIsPrintableValue" />');

                if (this.dashboard) {
                    this.printInput.prop('checked', this.dashboard.getPrintable());
                }

                var _this = this;
                this.printInput.on('change', function(){
                    if ( this.checked ) {
                        _this.dashboard.setPrintable(true);
                    } else {
                        _this.dashboard.setPrintable(false);
                    }
                });
            }

            return this.printInput;
        },
        getCustomInput: function() {
            if (!this.customInput) {
                this.customInput = $('<input type="checkbox" class="form" id="dashboardOptionIsCustomValue" />');

                if (this.dashboard) {
                    if (this.dashboard.getCustomView()) {
                        this.customInput.prop('checked',true);
                    }
                }

                this.customInput.on('change',function(){
                    $(document).trigger({
                        type: 'toggle.builder.custom',
                        toggle: this.checked
                    });
                });
            }

            return this.customInput;
        },

        getCustomForm: function() {
            if (!this.customForm) {
                this.customForm = $('<div class="form-group" id="dashboardOptionIsCustomGroup"></div>');
                var container = $('<div class="checkbox"></div>');
                var label = $('<label></label>');
                container.append(label);
                label.append(this.getCustomInput()).append(' Use custom view');
                this.customForm.append(container);
            }
            return this.customForm;
        },

        allowedPublic: function() {
            var allowed = true;

            if (GD.DashboardBuilder.config) {
                allowed = GD.DashboardBuilder.config['public'];
            }

            return allowed;
        },

        allowedExport: function() {
            var allowed = false;

            if (GD.DashboardBuilder.config) {
                allowed = GD.DashboardBuilder.config['export'];
            }

            return allowed;
        },

        allowedPrint: function() {
            var allowed = false;

            if (GD.DashboardBuilder.config) {
                allowed = GD.DashboardBuilder.config['print'];
            }

            return allowed;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="dsb-dsply-frm"></div>');
                this.formContainer.append(this.getHeader());

                if (this.allowedPublic()) {
                    this.formContainer.append(this.getPublicForm());
                }

                this.formContainer.append(this.getCustomForm());

                if (this.allowedExport()) {
                    this.formContainer.append(this.getExportForm());
                }
                if (this.allowedPrint()) {
                    this.formContainer.append(this.getPrintForm());
                }

                // widgets
                this.formContainer.append(this.getWidgetHeader());
                this.formContainer.append(this.getWidgetInfo());
                this.formContainer.append($('<div class="widget-buttons"></div>').append(this.getTextWidget(),' ',this.getImageWidget()));
            }

            return this.formContainer;
        },

        render: function(force) {
            if (force) {
                this.initVariables();
            }

            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);