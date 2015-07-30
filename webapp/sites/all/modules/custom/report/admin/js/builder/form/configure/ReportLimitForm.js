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
        throw new Error('ReportLimitForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportLimitForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportLimitForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
        },

        initialize: function () {
            var reportObj = this.getController().getReport();
            this.setQueryLimitInput(reportObj.getLimit());
            this.setQueryOffsetInput(reportObj.getOffset());
            this.getApplyButton();
            this.updateHelpText();
        },

        getController: function () {
            return this.options.builder;
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<div>',
                        '<p></p>',
                        '<div class="form-group">',
                            '<label for="reportQueryLimit">Limit</label>',
                            '<input type="text" class="form-control input-sm" id="reportQueryLimit" value="">',
                        '</div>',
                        '<div class="form-group">',
                            '<label for="reportQueryOffset">Offset</label>',
                            '<input type="text" class="form-control input-sm" id="reportQueryOffset" value="">',
                        '</div>',
                        '<div class="form-group action-group">',
                            '<button class="btn btn-primary btn-sm pull-right" type="button" id="reportQueryLimitApply">Apply</button>',
                        '<div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },

        showValidationMessage: function(msg, input){
            input.after('<div class="help-block">'+msg+'</div>');
            input.parent().addClass('has-error');
        },

        removeValidationMessage: function(){
            this.getFormContainer().find('.help-block').remove();
            this.getFormContainer().find('.form-group').removeClass('has-error');
        },

        validateInteger: function(value) {
            return /^\+?(0|[1-9]\d*)$/.test(value);
        },

        getApplyButton: function() {
            if (!this.applyButton) {
                var _this = this;
                this.applyButton = this.getFormContainer().find('#reportQueryLimitApply');
                this.applyButton.on('click', function() {
                    _this.removeValidationMessage();

                    var limit = _this.getQueryLimitInput().val();
                    var offset = _this.getQueryOffsetInput().val();

                    if ( !_this.validateInteger(limit) && limit !== '' ) {
                        _this.showValidationMessage('Enter a whole number or leave blank.',_this.getQueryLimitInput());
                        return false;
                    }

                    if ( !_this.validateInteger(offset) ) {
                        _this.showValidationMessage('Enter a whole number.',_this.getQueryOffsetInput());
                        return false;
                    }

                    var report = _this.getController().getReport();

                    if ( limit === '' ) {
                        report.setLimit(null);
                    } else {
                        report.setLimit(limit);
                    }

                    report.setOffset(offset);


                    $(document).trigger({
                        type: 'changed.query.limit'
                    });
                });
            }
            return this.applyButton;
        },

        updateHelpText: function () {
            if ( !this.helpText ) {
                this.helpText = this.getFormContainer().find('p');
            }

            if ( this.getController().getReport().getChartType() != 'table' ) {
                if(this.getMaxLimit()){
                    this.helpText.text('There is a maximum limit that varies by display type. Currently, the maximum limit is '+this.getMaxLimit()+'. Any value above it will be ignored.');
                }else{
                    this.helpText.text('There is a maximum limit that varies by display type. Currently, there is no limit on returned records');
                }
            } else {
                this.helpText.text('There is a maximum number of '+this.getMaxLimit()+' records that can be displayed per table. Any more than that will invoke pagination.');
            }
        },

        getQueryLimitInput: function () {
            if ( !this.queryLimitInput ) {
                this.queryLimitInput = this.getFormContainer().find('#reportQueryLimit');
            }
            return this.queryLimitInput;
        },

        setQueryLimitInput: function ( value ) {
            if ( !this.queryLimitInput ) {
                this.queryLimitInput = this.getFormContainer().find('#reportQueryLimit');
            }
            if ( value === 'null' ) { value = null } // handles a case where null may have been saved as string
            this.queryLimitInput.val(value);
        },

        getQueryOffsetInput: function () {
            if ( !this.queryOffsetInput ) {
                this.queryOffsetInput = this.getFormContainer().find('#reportQueryOffset');
            }
            return this.queryOffsetInput;
        },

        setQueryOffsetInput: function ( value ) {
            if ( !this.queryOffsetInput ) {
                this.queryOffsetInput = this.getFormContainer().find('#reportQueryOffset');
            }
            if ( !value ) { value = 0 }
            this.queryOffsetInput.val(value);
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                this.initialize();
            }

            return this.getFormContainer();
        },

        getMaxLimit: function () {

            var limit = null;

            switch ( this.getController().getReport().getChartType() ) {
                case 'line' :
                case 'scatter' :
                case 'area' :
                case 'bar' :
                case 'column' :
                case 'sparkline' :
                case 'customview' :
                    limit = 500;
                    break;

                case 'pie' :
                    limit = 100;
                    break;

                case 'gauge' :
                case 'dynamic_text' :
                    limit = 1;
                    break;

                case 'map' :
                    limit = 1000;
                    break;

                case 'table' :
                    limit = 100;
                    break;
            }

            return limit;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);