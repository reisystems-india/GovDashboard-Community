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

var DashboardSection = isc.defineClass("DashboardSection");

DashboardSection = DashboardSection.addClassProperties({

    weight: 2,

    getId: function () {
        return 'DashboardSection';
    },

    getName: function () {
        return 'dashboard';
    },

    getTitle: function () {
        return 'Dashboards';
    },

    getDefaultRoute: function () {
        return '/cp/dashboard?ds=' + Datasource.getInstance().getName();
    },

	getContainer: function () {
		if ( typeof this.container == 'undefined' ) 
		{
			this.container = isc.VLayout.create({
				ID: "dashboardPane",
				width: 900,
				height: "100%",
                layoutMargin: 0,
                autoDraw:false,
				members: []
		    });
	    }
		    
		return this.container;
	},

    resetCustomView: function () {
        DashboardSection.getDashboardCustomViewSection().getMember('dashboardCustomViewEditor').setValue('custom-view-code-editor-text', '');
        DashboardSection.getDashboardCustomViewToggle().setContents(DashboardSection.getDashboardCustomViewToggleHtml());
    },

    getDashboardCustomViewSection: function () {
        if ( typeof this.customView == 'undefined' ) {
            this.customView = isc.VLayout.create({
                ID: "dashboardCustomViewSection",
                width:900,
                paddingAsLayoutMargin:10,
                members: [
                    isc.DynamicForm.create({
                        ID: "dashboardCustomViewEditor",
                        width:900,
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
                                click: "DashboardSection.applyCustomCode(DashboardSection.getDashboardCustomViewSection().getMember('dashboardCustomViewEditor').getValue('custom-view-code-editor-text'));",
                                align:"right",
                                colSpan:2
                            }
                        ]
                    })
                ]
            });
        }

        return this.customView;
    },
	
	getViewList: function () {
		if ( typeof this.viewList == 'undefined' ) 
		{
            var layoutAboveList = isc.HLayout.create({
                width: 900,
                height: "1%",
                layoutTopMargin: 10,
                layoutBottomMargin: 0,
                layoutLeftMargin: 0,
                layoutRightMargin: 0,
                members: [
					isc.Label.create({
                        ID:'dashboardListSubHeaderId',
                        name:'dashboardListSubHeaderName',
                        width: "*",
						layoutMargin: 0,
                        contents: 'The following dashboards have been created.'
                    })
                ]
            }); 

			this.viewList = isc.VLayout.create({
		        ID: "dashboardViewList",
                width: 900,
                height: 500,
                layoutMargin: 0,
		        members: [
		            DashboardSection.getHeaderList(),
                    layoutAboveList,
		            DashboardSection.getDashboardList()
		        ]
		    });
		}
		    
		return this.viewList;
	},
	
	getViewBuilder: function () {
		if ( typeof this.viewBuilder == 'undefined' ) 
		{
			this.viewBuilder = isc.VLayout.create({
		        ID: "dashboardViewBuilder",
		        width: 900,
                height: 600,
                animateMembers: true,
                membersMargin: 0,
				layoutMargin: 0,
                members: [
                    DashboardSection.getHeaderBuilder(),
                    DashboardSection.getDashboardInfo(),
                    DashboardSection.getDashboardOptionsLayout(),
                    DashboardSection.getDashboardCustomViewSection(),
					DashboardSection.getDashboardBuilder()
		        ]
		    });
        }

        return this.viewBuilder;
	},

    getHeaderList: function () {
	    if ( typeof this.headerList == 'undefined' ) {
	        this.headerList = isc.HLayout.create({
	            ID: "dashboardHeaderList",
                width: 900,
                height: 10,
                membersMargin: 0,
                defaultLayoutAlign: "right",
				styleName: "dashboardHeader",
                layoutMargin: 0,
                autoDraw:false,
		        members: [
	                isc.Label.create({
	                    contents: "<h2>Dashboard Management</h2>",
                        width: 750,
                        height: 10,
                        autoDraw:false,
	                    styleName: "sectionHeader"
	                }),
					isc.Layout.create({
						ID: "newDashboardButton",
						styleName: "fancyCpButton",
						contents: "<span>New Dashboard</span>",
						width: "*",
						height: 50,
						click: "GDNavigation.gotoNewDashboard()"
					})
	            ]
	        });
        }
	    
	    return this.headerList;
	},
	
	getHeaderBuilder: function () {
	    if ( typeof this.headerBuilder == 'undefined' ) {
	        this.headerBuilder = isc.HLayout.create({
	            ID: "dashboardHeaderBuilder",
                width: 900,
                height: 10,
                membersMargin: 0,
                layoutMargin: 0,
                defaultLayoutAlign: "left",
                layoutBottomMargin: 10,
		        members: [
	                isc.Label.create({
	                    contents: "<h2>Dashboard Management</h2>",
                        width: 900,
                        height: 10,
                        autoDraw:false,
	                    styleName: "sectionHeader"
	                })
	            ]
	        });
	    }
	    
	    return this.headerBuilder;
	},
	
	getDashboardList: function () {
	    if ( typeof this.dashboardList == 'undefined' ) 
	    {
	        this.dashboardList = isc.ListGrid.create({
                ID: "dashboardList",
                styleName: "tableGrid",
				layoutMargin: 0,
	            dataSource : DashboardDS.getInstance(),
                headerAutoFitEvent: "none",
                leaveScrollbarGap: false,
	            wrapCells: true,
	            fixedRecordHeights: false,
	            autoFetchData : true,
                initialCriteria: {
                    datasource: Datasource.getInstance().getName(),
                    fields: 'reports'
                },
	            fields: [
                    {
                        name:"public",
                        title:"Access",
                        width:55,
                        type:'image',
                        align: "center",
                        showHover: true,
                        hoverHTML: function(record, value, rowNum, colNum, grid) {
                            if ( record['public'] == 1 ) {
                                return 'Public';
                            }
                        },
                        formatCellValue: function ( value, record, rowNum, colNum, grid ) {
                            if ( value == 1 ) {
                                return '<img src="/sites/all/modules/custom/webui/admin/images/public-icon.png" alt="Public" />';
                            } else {
                                return '&nbsp;';
                            }
                        }
                    },
	                {
                        name:"name",
                        formatCellValue: function ( value, record, rowNum, colNum, grid ) {
                            return '<a href="/cp/dashboard/'+record.id+'" title="Edit Dashboard">'+value+'</a>';
                        }
                    },
	                {name:"author"},
	                {name:"changed",width:120},
                    {name:"datasetNames"},
	                {name:"reportnames"}
	            ],
                initialSort: [
                    {property:"changed", direction:"descending"}
                ]
	        });
	    }
	    
	    return this.dashboardList;
	},
	
	getDashboardInfo: function() {
		if ( typeof this.dashboardInfoLayout == 'undefined' ) {
            this.dashboardInfoLayout = isc.HLayout.create({
                ID: "dashboardInfoLayout",
                width: 1000,
                height: 10,
				layoutMargin: 0,
				membersMargin: 0,
                members: [
                    DashboardForm.getForm(),
                    DashboardSection.getDashboardActionBar()
                ]
            });
		}
		return this.dashboardInfoLayout;
	},

    getDashboardOptionsLayout: function() {
        if ( typeof this.dashboardOptionsLayout == 'undefined' ) {
            this.dashboardOptionsLayout = isc.HLayout.create({
                ID: "dashboardOptionsLayout",
                layoutTopMargin: 15,
                layoutBottomMargin: 10,
                members: [
                    this.getDashboardConfigButtons()
                ]
            })
        }

        return this.dashboardOptionsLayout;
    },

    getDashboardConfigButtons: function() {
        if ( typeof this.dashboardConfigButtonsLayout == 'undefined' ) {
            this.dashboardConfigButtonsLayout = isc.HLayout.create({
                ID: "dashboardConfigButtonsLayout",
                membersMargin: 2,
                memberOverlap: 18,
                layoutTopMargin: 5, /* needed for counts, otherwise they get chopped (GOVDB-1572) */
                members: [
                    isc.Button.create({
                        ID: "dashboardConfigButtonReports",
                        title: "Reports",
						baseStyle: "orangeCpButton",
						height: 35,
						width: 100,
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            DashboardConfigDropDowns.configButtonClick(this, "reports", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            DashboardConfigDropDowns.getReportsDropDown().moveBy(deltaX, deltaY);
                        }
                    }),
                    DashboardOptionCounts.getInstance().getReportCount(),
                    isc.LayoutSpacer.create({width:34}),

                    isc.Button.create({
                        ID: "dashboardConfigButtonFilter",
                        title: "Filter",
						baseStyle: "orangeCpButton",
						height: 35,
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            DashboardConfigDropDowns.configButtonClick(this, "filter", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            DashboardConfigDropDowns.getFilterDropDown().moveBy(deltaX, deltaY);
                        }
                    }),
                    DashboardOptionCounts.getInstance().getFilterCount(),
                    isc.LayoutSpacer.create({width:34}),

                    isc.Button.create({
                        ID: "dashboardConfigButtonLink",
                        title: "Link",
						baseStyle: "orangeCpButton",
						height: 35,
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            DashboardConfigDropDowns.configButtonClick(this, "link", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            DashboardConfigDropDowns.getLinkDropDown().moveBy(deltaX, deltaY);
                        }
                    }),
                    DashboardOptionCounts.getInstance().getDrilldownCount(),
                    isc.LayoutSpacer.create({width:34}),

                    isc.Button.create({
                        ID: "dashboardConfigButtonLayout",
                        title: "Display",
						baseStyle: "orangeCpButton",
						height: 35,
                        click: function() {
                            var x = this.getPageLeft();
                            var y = this.getPageTop() + this.getVisibleHeight();
                            DashboardConfigDropDowns.configButtonClick(this, "layout", x, y);
                        },
                        parentMoved: function(parent, deltaX, deltaY) {
                            DashboardConfigDropDowns.getLayoutDropDown().moveBy(deltaX, deltaY);
                        }
                    })
                ]
            });
        }

        return this.dashboardConfigButtonsLayout;
    },

    getDashboardCustomViewToggle: function() {
        if ( typeof this.dashboardCustomViewToggle == 'undefined' ) {
            this.dashboardCustomViewToggle = isc.HTMLFlow.create({
                ID: "DashboardCustomViewToggle",
                height: 20,
                contents: DashboardSection.getDashboardCustomViewToggleHtml()
            });
        }

        return this.dashboardCustomViewToggle;
    },

    getDashboardCustomViewToggleHtml: function() {
        var html =
            '<div id="customViewToggleContainer">' +
                '<input type="checkbox" id="customViewToggle" style="float:left;" name="customViewToggle"' +
                ' onclick="DashboardActions.toggleCustomView(this.checked);"';

        if ( Dashboard.getCustomView() ) {
            html += ' checked="true" ';
        }

        html +=
            '/> ' +
                '<label id="customViewToggleLabel" style="float:left;" for="customViewToggle">Toggle Custom View</label>' +
                '</div>';

        return html;
    },

    getDashboardPublicFlagLayout: function() {
        if ( typeof this.dashboardPublicFlagLayout == 'undefined') {
            this.dashboardPublicFlagLayout = isc.HTMLFlow.create({
                ID: "DashboardPublicFlagLayout",
                height: 20,
                contents: this.getPublicFlagHTML()
            });
        }

        return this.dashboardPublicFlagLayout;
    },

    getPublicFlagHTML: function() {
        var publicUrl = '';

        var html =
            '<div id="publicDashboardSwitchContainer">' +
                '<input type="checkbox" id="publicDashboard" name="publicDashboard"' +
                ' onclick="DashboardActions.clickPublic(this.checked);"';

        // need to check explicitly for > 0
        if ( Dashboard.getPublic() > 0 ) {
            html += ' checked="true" ';
            publicUrl = this.getPublicUrl();
        }

        html +=
            '/> ' +
                '<label id="publicDashboardLabel" for="publicDashboard">Public</label>' +
                '<div id="publicDashboardUrl">' + publicUrl + '</div>' +
                '</div>';

        return html;
    },

    getPublicUrl: function() {
        //  TODO Don't hard code HTTPS
        var url = 'https://' + window.location.host + '/public/dashboard/' + Dashboard.getId();
        return '<input id="publicDashboardUrlInput" type="text" value="' + url + '"/>';
    },

    getDashboardBuilder: function() {
		if ( typeof this.dashboardBuilderLayout == 'undefined' ) {
			this.dashboardBuilderLayout = isc.HLayout.create({
    			ID: "dashboardBuilderLayout",
				styleName:"dashboardBuilderLayout",
				layoutMargin: 6,
                membersMargin: 0,
				layoutBottomMargin: 30,
                width: 900,
                memberOverlap: 0,
                members: [
                    this.dashboardCanvasWrapper = isc.VLayout.create({
                        ID: "dashboardCanvasWrapper",
						styleName:"dashboardCanvasWrapper",
						layoutMargin: 9,
						width: 900,
                        members: [
                            DashboardCanvas.getInstance()
                        ]
                    })
                ]
            });
 		}
        
		return this.dashboardBuilderLayout;
	},

    getDashboardActionBar: function() {
	    if ( typeof this.dashboardActionBarLayout == 'undefined' ) {
	        this.dashboardActionBarLayout = isc.HLayout.create({
	        	ID: "dashboardActionBarLayout",
	            membersMargin: 5,
                width: 200
	        });
	    }

        this.dashboardActionBarLayout.addMember(
            isc.IButton.create({
                title: "Cancel",
                click: "DashboardActions.cancel()"
            })
        );

        this.dashboardActionBarLayout.addMember(
            isc.IButton.create({
                ID: "dashboardActionButtonDelete",
                title: "Delete",
                click: "DashboardActions.deleteDashboard()"
            })
        );

        this.dashboardActionBarLayout.addMember(
            isc.IButton.create({
                ID: "dashboardActionButtonSaveAs",
                title: "Save As...",
                click: "DashboardActions.promptSaveAs()"
            })
        );

        this.dashboardActionBarLayout.addMember(
            isc.IButton.create({
                title: "Save",
                click: "DashboardActions.save()"
            })
        );

	    return this.dashboardActionBarLayout;
	},

    loadPublicFlag: function() {
        if (GovDashboard['allowPublic']) {
            this.getDashboardPublicFlagLayout().setContents(this.getPublicFlagHTML());
            DashboardActions.clickPublic(Dashboard.getRecord()['public']=="1");
        }
    },

    loadCustomView: function() {
        DashboardSection.getDashboardCustomViewToggle().setContents(DashboardSection.getDashboardCustomViewToggleHtml());
        var member =  DashboardSection.getDashboardCustomViewSection().getMember('dashboardCustomViewEditor');
        if ( member != null ) {
            member.setValue('custom-view-code-editor-text', Dashboard.getCustomView());
        }
        DashboardCanvas.getInstance().setContents(Dashboard.getCustomView());
        DashboardActions.toggleCustomView(Dashboard.getCustomView());
    },

    showCustomViewSection: function () {
        DashboardSection.getViewBuilder().showMember(DashboardSection.getDashboardCustomViewSection());
    },

    hideCustomViewSection: function () {
        DashboardSection.getViewBuilder().hideMember(DashboardSection.getDashboardCustomViewSection());
    },

    applyCustomCode: function(code) {
        DashboardCanvas.getInstance().setContents(code);
        DashboardConfig.refreshReports();
    }
});

