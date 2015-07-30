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

var ReportSection = isc.defineClass("ReportSection");
ReportSection = ReportSection.addClassProperties({

    weight: 1,

    getId: function () {
        return 'ReportSection';
    },

    getName: function () {
        return 'report';
    },

    getTitle: function () {
        return 'Reports';
    },

    getDefaultRoute: function () {
        return '/cp/report?ds=' + Datasource.getInstance().getName();
    },

	getContainer: function () {
		if ( typeof this.container == 'undefined' )
		{
			this.container = isc.VLayout.create({
				ID: "reportPane",
				width: 1000,
				height: "100%",
				layoutMargin: 0,
                defaultLayoutAlign: "center",
                members: []
		    });
	    }

		return this.container;
	},

    resetCustomView: function () {
        ReportSection.getCustomViewSection().getMember('reportCustomViewData').setContents('');
        ReportSection.getCustomViewSection().getMember('reportCustomViewEditor').setValue('custom-view-code-editor-text', '');
    },

    getCustomViewSection: function () {
        if ( typeof this.customViewSection == 'undefined' ) {
            this.customViewSection = isc.VLayout.create({
                ID: "customViewSection",
                width:"100%",
                visibility:"hidden",
                paddingAsLayoutMargin:10,
                members: [
                    isc.Label.create({
                        margin:10,
                        valign:"bottom",
                        height:"5px",
                        contents:"<strong>Data:</strong>"
                    }),
                    isc.HTMLPane.create({
                        ID: "reportCustomViewData",
                        height:"150px",
                        padding:10,
                        border: "1px"
                    }),
                    isc.DynamicForm.create({
                        ID: "reportCustomViewEditor",
                        width:"100%",
                        height:"200px",
                        padding:10,
                        fields: [
                            {
                                titleOrientation: "top",
                                title:"Custom Code",
                                name: "custom-view-code-editor-text",
                                height:"*",
                                width:"*",
                                colSpan:2,
                                type: "TextArea"
                            },
                            {
                                type: "Button",
                                title: "Apply",
                                click: "ReportSection.applyCustomCode();",
                                align:"right",
                                colSpan:2
                            }
                        ]
                    })
                ]
            });
        }

        return this.customViewSection;
    },

    applyCustomCode: function () {
        ReportActions.apply('customcode');
    },

	getViewList: function () {
		if ( typeof this.viewList == 'undefined' )
		{
            var layoutAboveList = isc.HLayout.create({
                width: 1000,
                height: "1%",
                layoutTopMargin: 10,
                layoutBottomMargin: 0,
				layoutLeftMargin: 0,
				layoutRightMargin: 0,
                members: [
					isc.Label.create({
                        ID:'reportListSubHeaderId',
                        name:'reportListSubHeaderName',
                        width: "*",
						layoutMargin: 0,
                        contents: 'The following reports have been created and are available for use on dashboards.'
                    })
                ]
            }); 

            this.viewList = isc.VLayout.create({
		        ID: "reportViewList",
		        width: 1000,
                height: 500,
				layoutMargin: 0,
		        members: [
		            ReportSection.getHeaderList(),
					layoutAboveList,
		            ReportSection.getReportList()
		        ]
		    });
		}

		return this.viewList;
	},

	getViewBuilder: function () {
		if ( typeof this.viewBuilder == 'undefined' )
		{
			this.viewBuilder = isc.VLayout.create({
                ID: "reportViewBuilder",
                width: 1000,
                layoutMargin: 0,
                membersMargin: 0,
                members: [
                    ReportSection.getHeaderBuilder(),
                    ReportSection.getReportBuilder()
                ]
            });
		}

		return this.viewBuilder;
	},

	getHeaderList: function () {
	    if ( typeof this.headerList == 'undefined' )
	    {
	        this.headerList = isc.HLayout.create({
	            ID: "reportHeaderList",
                width: 1000,
                height: 10,
                membersMargin: 0,
                defaultLayoutAlign: "right",
				styleName: "reportHeader",
                layoutMargin: 0,
	            members: [
	                isc.Label.create({
	                    contents: "<h2>Report Management</h2>",
                        width: 750,
                        height: 10,
                        autoDraw: false,
	                    styleName: "sectionHeader"
	                }),
					isc.Layout.create({
						ID: "newReportButton",
						styleName: "fancyCpButton",
						contents: "<span>New Report</span>",
						width: "*",
						height: 50,
						click: "GDNavigation.gotoNewReport()"
					})
	            ]
	        });
	    }

	    return this.headerList;
	},

	getHeaderBuilder: function () {
	    if ( typeof this.headerBuilder == 'undefined' )
	    {
            var reportHeaderActions = isc.VLayout.create({
                ID: "reportHeaderActions",
                width: "1%",
                membersMargin: 5,
                members: [
                    ReportSection.getActionBar()
                ]
            });

            var reportHeaderForm = isc.HLayout.create({
	            ID: "reportHeaderForm",
	            width: 1000,
	            height: "*",
                membersMargin: 0,
	            members: [
                    ReportForm.getDetailsForm(),
                    reportHeaderActions
	            ]
	        });

            var reportHeaderTop = isc.HLayout.create({
                ID: "reportHeaderTop",
                width: 1000,
                layoutTopMargin: 0,
                members: [
                    isc.Label.create({
                        ID: "reportAdminPageTitle",
                        contents: "<h2>Edit Report</h2>",
	                    valign: "top",
                        width: 800,
	                    styleName: "sectionHeader",
                        wrap: false
	                })
                ]
            });

            this.headerBuilder = isc.VLayout.create({
	            ID: "reportHeader",
	            width: 1000,
                height: "1%",
                members: [
	                reportHeaderTop,
                    reportHeaderForm
	            ]
	        });
	    }

	    return this.headerBuilder;
	},

    refreshReportHeaderActions: function() {
        isc.Canvas.getById("reportHeaderActions").setMembers(
            [
                ReportSection.getActionBar()
            ]
        );
    },

    getReportList: function () {

	    if ( typeof this.reportList == 'undefined' )
	    {
	        this.reportList = isc.ListGrid.create({
	            ID: "reportList",
                styleName: "tableGrid",
	            dataSource : ReportDS.getInstance(),
                headerAutoFitEvent: "none",
                leaveScrollbarGap: false,
	            wrapCells: true,
	            fixedRecordHeights: false,
	            autoFetchData : true,
                selectionType: "simple",
                initialCriteria: {datasource: Datasource.getInstance().getName()},
	            fields: [
                    {
                        name:"name",
                        formatCellValue: function ( value, record, rowNum, colNum, grid ) {
                            return '<a href="/cp/report/'+record.id+'" title="Edit Report">'+value+'</a>';
                        }
                    },
	                {name:"author_info", title:"Author"},
	                {name:"changed"},
                    {name:"dataset_list", title: "Data", width: 150},
                    {name:"dashboard_list", title:"Used in Dashboards"}

	            ],
                initialSort: [
                    {property:"changed", direction:"descending"}
                ]
	        });
	    }

	    return this.reportList;
	},

	getReportBuilder: function() {
		if ( typeof this.reportBuilderLayout == 'undefined' ) {
            this.reportBuilderLayout = isc.HLayout.create({
    			ID: "reportBuilderLayout",
                width: 1000,
                height: "100%",
                members: [
                    ReportSection.getReportPreviewSection()
                ]
            });
		}
		return this.reportBuilderLayout;
	},

    getReportPreviewSection: function() {
        if ( typeof this.reportPreviewSection == 'undefined' ) {
			this.reportPreviewSection = isc.Layout.create({
    			ID: "reportPreviewSection",
                width: "100%",
                members: [ReportPreview.get()]
            });
		}
		return this.reportPreviewSection;
    },

	getActionBar: function() {
	    if ( typeof this.actionBarLayout == 'undefined' ) {
	        this.actionBarLayout = isc.HLayout.create({
	            membersMargin: 5,
                width:"*",
                members: [
                    isc.IButton.create({
                        title: "Cancel",
                        click: function() {
                            ReportActions.exit();
                        }
                    }),
                    isc.IButton.create({
                        title: "Delete",
                        click: function() {
                            ReportActions.deleteReport();
                        }
                    }),
                    isc.IButton.create({
                        title: "Save As...",
                        click: "ReportForm.promptSaveAs()"
                    }),
                    isc.IButton.create({
                        title: "Save",
                        click: function() {
                            ReportForm.confirmSave();
                        }
                    })
	            ]
	        });
	    }
	    return this.actionBarLayout;
	},
    
    setReportChart: function(chartType) {
        var chartButtons = this.getTypeSelectorBar().members;
        for(var i=0, buttonCount=chartButtons.length; i<buttonCount; i+=1) {
            if(chartButtons[i].type == chartType) {
                chartButtons[i].setSelected(true);
                break;
            }
        }
    },

    getReportConfigButtons: function() {
        if ( typeof this.reportConfigButtonsLayout == 'undefined' ) {
            this.reportConfigButtonsLayout = isc.HLayout.create({
                ID: "reportConfigButtonsLayout",
                membersMargin: 2,
                layoutTopMargin: 20,
				height: 50,
                memberOverlap: 18,
                members: [
                    this.getDataButton(),
                    isc.LayoutSpacer.create({width:34}),

                    this.getSelectColumnsButton(),
                    isc.LayoutSpacer.create({width:34}),

                    isc.Button.create({
                        ID: "reportConfigButtonFilter",
                        title: "Filter",
						height: 35,
                        width: 80,
						baseStyle: "orangeCpButton",
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            ReportConfigDropDowns.configButtonClick(this, "filter", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            ReportConfigDropDowns.getFilterDropDown().moveBy(deltaX, deltaY);
                        }
                    }),
                    ReportOptionCounts.getInstance().getFilterCount(),
                    isc.LayoutSpacer.create({width:34}),

                    isc.Button.create({
                        ID: "reportConfigButtonConfigure",
                        title: "Configure",
						height: 35,
                        width: 100,
						baseStyle: "orangeCpButton",
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            ReportConfigDropDowns.configButtonClick(this, "configure", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            ReportConfigDropDowns.getConfigDropDown().moveBy(deltaX, deltaY);
                        }
                    }),
                    ReportOptionCounts.getInstance().getConfigCount(),
                    isc.LayoutSpacer.create({width:34}),

                    isc.Button.create({
                        ID: "reportConfigButtonVisualize",
                        title: "Visualize",
						height: 35,
                        width: 100,
						baseStyle: "orangeCpButton",
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            ReportConfigDropDowns.configButtonClick(this, "visualize", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            ReportConfigDropDowns.getVisualizeDropDown().moveBy(deltaX, deltaY);
                        }
                    })
                ]
            });
        }

        return this.reportConfigButtonsLayout;
    },

    getDataButton:function(){
        if ( typeof this.dataButton == "undefined" ) {
            this.dataButton = isc.Button.create({
                ID: "reportConfigButtonData",
                title: "Data",
                baseStyle: "orangeCpButton",
                height: 35,
                width: 80,
                layoutMargin: 0,
                click: function() {
                    var x = this.getPageLeft();
                    var y = this.getPageTop() + this.getVisibleHeight();
                    ReportConfigDropDowns.configButtonClick(this, "data", x, y);
                },
                parentMoved: function(parent, deltaX, deltaY) {
                    ReportConfigDropDowns.getDataDropDown().moveBy(deltaX, deltaY);
                }
            })
        }
        return this.dataButton;
    },

    getSelectColumnsButton: function() {
        if ( typeof this.selectColunsButton == "undefined" ) {
            this.selectColunsButton = isc.Button.create({
                ID: "reportConfigButtonColumns",
                title: "Columns",
                baseStyle: "orangeCpButton",
                height: 35,
                width: 100,
                layoutMargin: 0,
                click: function() {
                    var x = this.getPageLeft();
                    var y = this.getPageTop() + this.getVisibleHeight();
                    ReportConfigDropDowns.configButtonClick(this, "columns", x, y);
                    if (!GD.ReportColumnTree) {
                        var r = {
                            columns: Report.getColumns(),
                            dataset: ReportConfig.getDatasets()[0]
                        };
                        GD.ReportColumnTree = new GD.ColumnTree(r, $('#treeContainer'), {'checkbox': true});
                        GD.ReportColumnTree.render();
                    }
                },
                parentMoved: function(parent, deltaX, deltaY) {
                    ReportConfigDropDowns.getColumnsDropDown().moveBy(deltaX, deltaY);
                }
            })
        }

        return this.selectColunsButton;
    },

    showCustomView: function() {
        ReportSection.getCustomViewSection().show();
    },

    hideCustomView: function () {
        ReportSection.getCustomViewSection().hide();
    },

	getTypeSelectorBar: function() {
	    if ( typeof this.typeSelectorBarLayout == 'undefined' ) {
                this.typeSelectorBarLayout = isc.ToolStrip.create({
	        	ID: "typeSelectorBarLayout",
                layoutAlign:"center",
                membersMargin: 2,
				layoutTopMargin: 15,
                height: 40,
                width: 100,
	            members: [
                    isc.ImgButton.create({
                        ID: "chartButton_table",
                        type: "table",
                        click: "ReportPreview.selectType('table');",
                        src: "[SKIN]/ToolStrip/Reports-Advanced-table.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Advanced Table",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_line",
                        type:"line",
                        click: "ReportPreview.selectType('line');",
                    	src: "[SKIN]/ToolStrip/Reports-Line-Chart.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Line",
						hoverOpacity:100
                    }),
                    // TODO: see GOVDB-1527, GOVDB-1321 regarding scatter implementation
                    // TODO: until then, keep scatter commented out
                    /*isc.ImgButton.create({
                        ID: "chartButton_scatter",
                        type: "scatter",
                        click: "ReportPreview.selectType('scatter');",
                    	src: "[SKIN]/ToolStrip/Reports-Scatter-Chart.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Scatter",
						hoverOpacity:100
                    }),*/
                    isc.ImgButton.create({
                        ID: "chartButton_area",
                        type: "area",
                        click: "ReportPreview.selectType('area');",
                    	src: "[SKIN]/ToolStrip/Reports-Area-Chart.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Area",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_bar",
                        type: "bar",
                        click: "ReportPreview.selectType('bar');",
                    	src: "[SKIN]/ToolStrip/Reports-Bar-Chart.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Bar",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_column",
                        type: "column",
                        click: "ReportPreview.selectType('column');",
                    	src: "[SKIN]/ToolStrip/Reports-Column-Chart.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Column",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_pie",
                        type: "pie",
                        click: "ReportPreview.selectType('pie');",
                    	src: "[SKIN]/ToolStrip/Reports-Pie.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Pie",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_sparkline",
                        type: "sparkline",
                        click: "ReportPreview.selectType('sparkline');",
                    	src: "[SKIN]/ToolStrip/Reports-Sparkline.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Sparkline",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_gauge",
                        type: "gauge",
                        click: "ReportPreview.selectType('gauge');",
                    	src: "[SKIN]/ToolStrip/Reports-Gauge.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Gauge",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_dynamic_text",
                        type: "dynamic_text",
                        click: "ReportPreview.selectType('dynamic_text');",
                        src: "[SKIN]/ToolStrip/Reports-Dynamic-Text.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
						prompt: "Dynamic Text",
						hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_map",
                        type: "map",
                        click: "ReportPreview.selectType('map');",
                        src: "[SKIN]/ToolStrip/Reports-Map.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
                        prompt: "Map",
                        hoverOpacity:100
                    }),
                    isc.ImgButton.create({
                        ID: "chartButton_customview",
                        type: "customview",
                        click: "ReportPreview.selectType('customview');",
                        src: "[SKIN]/ToolStrip/Reports-Dynamic-Text.png",
                        actionType: "radio",
                        radioGroup: "chartType",
                        width: 36,
                        height: 36,
                        prompt: "Custom View",
                        hoverOpacity:100
                    })
	            ],
                click: function() {
                    ReportConfigDropDowns.closeAllDropDowns();
                }
	        });
	    }

        return this.typeSelectorBarLayout;
	},

    disableChartTypesByColumns: function() {
        // TODO: see GOVDB-1527, GOVDB-1321 regarding scatter implementation
        var chartTypes = ["table", "line", "area", "bar", "column", "pie", "sparkline", "gauge", "dynamic_text", "map"];
        var chartTypeTooltips = ["Advanced Table", "Line", "Area", "Bar", "Column", "Pie", "Sparkline", "Gauge", "Dynamic Text", "Map"];

        // Enable all chart types
        for ( var i=0, chartTypeCount=chartTypes.length; i<chartTypeCount; i++ ) {
            isc.Canvas.getById('chartButton_'+chartTypes[i]).enable();
            isc.Canvas.getById('chartButton_'+chartTypes[i]).prompt = chartTypeTooltips[i];
        }

        // Disable chart types based on column selection
        if ( ReportConfig.getNumericColumnCount() != 1 || !ReportConfig.getNonNumericColumnCount() ) {
            isc.Canvas.getById('chartButton_pie').disable();
            isc.Canvas.getById('chartButton_pie').prompt = "Pie - You must have exactly 1 numeric column and at least 1 non-numeric column for this report type to function.";
        }

        if ( ReportConfig.getNumericColumnCount() != 1 && ReportConfig.getNonNumericColumnCount() ) {
            isc.Canvas.getById('chartButton_gauge').disable();
            isc.Canvas.getById('chartButton_gauge').prompt = "Gauge - You must have exactly one row having one numeric column for this report type to function.";
        }

        if ( (!ReportConfig.getNumericColumnCount() || !ReportConfig.getNonNumericColumnCount()) && !ReportConfig.findColumnsByCategory('measure') ) {
            var theseChartTypes = ["line", "area", "bar", "column", "sparkline", "map"];
            var theseTooltips = ["Line", "Area", "Bar", "Column", "Sparkline", "Map"];

            var tooltipMessage = "";
            for ( var i=0, chartTypeCount=theseChartTypes.length; i<chartTypeCount; i++ ) {
                isc.Canvas.getById('chartButton_'+theseChartTypes[i]).disable();
                tooltipMessage = "You must have at least one numeric and at least one non-numeric value for this report type to function.";
                isc.Canvas.getById('chartButton_'+theseChartTypes[i]).prompt = theseTooltips[i] + " - " + tooltipMessage;
            }
        }
    }
});