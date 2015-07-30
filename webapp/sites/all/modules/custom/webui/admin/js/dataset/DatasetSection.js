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


var DatasetSection = isc.defineClass("DatasetSection");

DatasetSection = DatasetSection.addClassProperties({

    weight: -10,

    panes: {},

    getId: function () {
        return 'DatasetSection';
    },

    getName: function () {
        return 'dataset';
    },

    getTitle: function () {
        return 'Datasets';
    },

    getDefaultRoute: function () {
        return '/cp/dataset?ds=' + Datasource.getInstance().getName();
    },

    // main dataset
    getContainer: function() {
        if ( typeof this.container == "undefined" ) {
            this.container = isc.VLayout.create({
                ID: "datasetPane",
                membersMargin:0,
                autoDraw:false,
                width: "100%",
                height: "100%",
                layoutMargin: 0,
                members:[]
            });
        }
        return this.container;
    },

    getPane: function ( name ) {
        if ( typeof this.panes[name] === 'undefined' ) {
            var pane = null;
            if ( name === 'List' ) {
                pane = this.getDatasetListPane();
            } else if ( name === 'Create' ) {
                pane = this.getDatasetCreatePane();
            } else if ( name === 'Edit' ) {
                pane = this.getDatasetEditPane();
            }  else if ( name === 'Upload' ) {
                pane = this.getDatasetUploadPane();
            } else if ( name === 'UploadURL' ) {
                pane = this.getDatasetURLUploadPane();
            } else {
                throw new TypeError('Not a Dataset Pane: '+name);
            }

            this.panes[name] = pane;
            DatasetSection.getContainer().addMember(pane);
        }

        return this.panes[name];
    },

    showPaneDefault: function () {
        this.showPane('List');
    },

    showPane: function ( name ) {
        jQuery.each(this.panes,function(key,pane){
            if ( name !== key ) {
                pane.hide();
            }
        });
        this.getPane(name).show();
    },

    // for upload file section
    getDatasetUploadPane: function () {
        return isc.VLayout.create({
            ID: "datasetUploadPane",
            styleName: "datasetUploadPane",
            membersMargin:0,
            layoutMargin: 0,
            autoDraw:false,
            visibility:"hidden",
            width: "100%",
            height: "100%",
            members:[
                isc.HLayout.create({
                    width: "100%",
                    height: 50,
                    membersMargin: 0,
                    defaultLayoutAlign: "right",
                    styleName: "datasetHeader",
                    layoutMargin: 0,
                    members: [
                        this.getDatasetUploadPaneHeaderLabel()
                    ]
                }),
                DatasetSection.getDatasetUpload()
            ]
        });
    },

    getDatasetUploadPaneHeaderLabel: function () {
        if ( typeof this.datasetUploadPaneHeaderLabel === 'undefined' ) {
            this.datasetUploadPaneHeaderLabel = isc.Label.create({
                contents: '<h2>Create New Data</h2>',
                width: 770,
                height: 50,
                styleName: "sectionHeader"
            });
        }
        return this.datasetUploadPaneHeaderLabel;
    },

    // for upload URL section
    getDatasetURLUploadPane: function() {
        return isc.VLayout.create({
            ID: "datasetURLUploadPane",
            membersMargin:0,
            layoutMargin: 0,
            autoDraw:false,
            visibility:"hidden",
            width: "100%",
            height: "100%",
            members:[
                isc.HLayout.create({
                    width: "100%",
                    height: 50,
                    membersMargin: 0,
                    defaultLayoutAlign: "right",
                    styleName: "datasetHeader",
                    layoutMargin: 0,
                    members: [
                        this.getDatasetUploadURLPaneHeaderLabel()
                    ]
                }),
                DatasetSection.getDatasetURLUpload()
            ]
        });
    },

    getDatasetUploadURLPaneHeaderLabel: function () {
        if ( typeof this.datasetUploadURLPaneHeaderLabel === 'undefined' ) {
            this.datasetUploadURLPaneHeaderLabel = isc.Label.create({
                contents: '<h2>Create New Data</h2>',
                width: 770,
                height: 50,
                styleName: "sectionHeader"
            });
        }
        return this.datasetUploadURLPaneHeaderLabel;
    },

    // To show dataset list
    getDatasetListPane: function() {
        return isc.VLayout.create({
            ID: "datasetListPane",
            autoDraw:false,
            overflow:"hidden",
            width: "100%", //height: "100%",
            layoutLeftMargin: 0,
            layoutRightMargin: 0,
            layoutTopMargin: 10,
            members:[
                isc.HLayout.create({
                    width: "100%",
                    height: 50,
                    membersMargin: 0,
                    defaultLayoutAlign: "right",
                    styleName: "datasetHeader",
                    layoutMargin: 0,
                    members: [
                        isc.Label.create({
                            contents: '<h2>Data Management</h2>',
                            width: 770,
                            height: 50,
                            styleName: "sectionHeader"
                        }),
                        isc.LayoutSpacer.create(),
                        isc.HLayout.create({
                            styleName: "fancyCpButton",
                            contents: "<span>New Data</span>",
                            width: "*",
                            align:"right",
                            height: 50,
                            click: "DatasetActions.newDatasetInter()"
                        })
                    ]
                }),
                isc.HLayout.create({
                    width: "100%",
                    height: "1%",
                    layoutMargin: 0,
                    members: [
                        isc.Label.create({
                            ID:'datasetListSubHeaderId',
                            name:'datasetListSubHeaderName',
                            width: "*",
                            layoutMargin: 0,
                            contents: 'The following data has been uploaded and is available for use in reports.'
                        })
                    ]
                }),
                DatasetSection.getDatasetList()
            ]
        });
    },

    // for dataset edit/update section
    getDatasetEditPane: function() {
        return isc.VLayout.create({
            membersMargin:0,
            visibility: "hidden",
            width: 900,
            layoutLeftMargin: 0,
            layoutRightMargin: 0,
            layoutTopMargin: 0,
            members: [
                isc.HLayout.create({
                    width: "100%",
                    height: 50,
                    membersMargin: 0,
                    defaultLayoutAlign: "right",
                    styleName: "datasetHeader",
                    layoutMargin: 0,
                    members: [
                        isc.Label.create({
                            contents: '<h2>Data Management</h2>',
                            width: 770,
                            height: 50,
                            styleName: "sectionHeader"
                        })
                    ]
                }),
                DatasetSectionEdit.getSection()
            ]
        });
    },

    // for dataset create section
    getDatasetCreatePane: function() {
        return isc.VLayout.create({
            membersMargin:0,
            visibility: "hidden",
            width: 900,
            layoutLeftMargin: 0,
            layoutRightMargin: 0,
            layoutTopMargin: 0,
            members: [
                isc.HLayout.create({
                    width: "100%",
                    height: 50,
                    membersMargin: 0,
                    defaultLayoutAlign: "right",
                    styleName: "datasetHeader",
                    layoutMargin: 0,
                    members: [
                        isc.Label.create({
                            contents: '<h2>Data Management</h2>',
                            width: 770,
                            height: 50,
                            styleName: "sectionHeader"
                        })
                    ]
                }),
                DatasetSectionCreate.getSection()
            ]
        });
    },

    getDatasetList: function () {

   	    if ( typeof this.datasetList == "undefined" ) {
   	    	this.datasetList = isc.ListGrid.create({
                ID: "datasetList",
                styleName: "tableGrid",
                dataSource : DatasetDS.getInstance(),
                headerAutoFitEvent: "none",
                leaveScrollbarGap: false,
                wrapCells: true,
                layoutMargin: 0,
                fixedRecordHeights: false,
                autoFetchData : true,
                selectionType: "simple",
                initialCriteria: {datasource: Datasource.getInstance().getName()},
                fields: [
                    {
                        name:"publicName",
                        title:"Name",
                        width:"30%",
                        formatCellValue: function ( value, record, rowNum, colNum, grid ) {
                            if ( typeof record.nid !== "undefined" ) {
                                return '<a href="/cp/dataset/'+record.name+'" title="Edit Dataset">'+value+'</a>';
                            } else {
                                return value;
                            }
                        }
                    },
                    {
                        name:"description",title:"Description", width:"35%"
                    },
                    {
                        name:"changed",
                        title:"Last Modified",
                        width:"15%",
                        type:"datetime",
                        formatCellValue: function ( value, record, rowNum, colNum, grid ) {
                            if ( typeof value !== "undefined" ) {
                                var date = new Date(DateFormat.parseISO8601(value));
                                return date.toUSShortDateTime();
                            } else {
                                return '&nbsp;';
                            }
                        }
                    },
                    {
                        name:"author",
                        title:"Author",
                        width:"20%",
                        formatCellValue: function ( value, record, rowNum, colNum, grid ) {
                            if ( typeof record.author !== "undefined" ) {
                                return record.author.name+' ('+record.author.email+')';
                            } else {
                                return '&nbsp;';
                            }
                        }
                    }
                ],
                initialSort: [
                    {property:"changed", direction:"descending"}
                ]
   	        });
   	    }
   	    return this.datasetList;
   	},

    getDatasetURLUpload: function () {
		if ( typeof this.datasetURLUploadForm == 'undefined' ) {
            this.datasetURLUploadForm = UploadForm.create({
                // location of our backend
                ID: 'datasetURLUploadForm',
                compoundEditor:this,
		        autoDraw:false,
                width: "100%",
                canSubmit:true,
                action: '/datafile/upload.json',
                fields: [
                    {
                        name:'staticUpdateText',
                        type:'BlurbItem',
                        title:'',
                        showTitle:false,
                        value:'',
                        visible:true
                    },
                    {
                        name: 'uploadFormID',
                        type: 'hidden',
                        value: 'datasetURLUploadForm'
                    },
                    {
                        name: 'datasetName',
                        type: 'hidden',
                        value: ''
                    },
                    {
                        name: 'datasource',
						cellClassName: 'topicSelect',
                        type: 'BlurbItem',
                        title: 'Topic',
                        value: Datasource.getInstance().getRecord().name,
                        showTitle:true
                    },
                    {
                        name: 'datafileUrl',
                        ID:'urlUploadFieldId',
                        type: 'text',
                        height:30,
                        required:true,
                        title: 'URL of the File',
                        defaultValue :'',
                        showTitle:true,
						width: 350
                    },
                    {
                        name:"appendTypeRadio",
						cellClassName: 'topicUpload',
                        type:"radioGroup",
                        vertical: true,
                        valueMap:{append:"This file appends the existing data.",
                                  replace:"This file replaces the existing data."},
                        defaultValue:"append",
                        showTitle:true,
                        visible:false,
                        title:"Upload Options"
                    },
                    {
                        name:'datasetUploadInfoName',
                        type: 'BlurbItem',
                        title:'',
                        showTitle:true,
                        value: 'Acceptable format: Comma separated text file in ' +
                            'uncompressed or compressed (zip) format.'
                    },
                    {type: "BlurbItem", value:''},
                    {type: "BlurbItem", colSpan:1, endRow:false, textAlign:"right", cellStyle:"datasetUploadCancelLink", value:'<a href="javascript:DatasetActions.datasetList();">Cancel</a>'},
                    {type: "submit", colSpan:1, startRow:false, title: 'Upload Data'}
                ],
                submitDone: function ( result ) {
                    DatasetActions.handleDataSubmit(result);
                }
            });
		}

        return this.datasetURLUploadForm;
    },

    getDatasetUpload: function () {
 	    if ( typeof this.datasetUploadForm == 'undefined' ) {
            this.datasetUploadForm = UploadForm.create({
                // location of our backend
                ID: 'datasetUploadForm',
                compoundEditor:this,
 		        autoDraw:false,
                showInlineErrors:true,
                width: "90%",
                action: '/datafile/upload.json',
                fields: [
                    {
                        name:'staticUpdateText',
                        type:'BlurbItem',
                        title:'',
                        showTitle:false,
                        value:'',
                        visible:false
                    },
                    {
                        name: 'datasource',
                        cellClassName: 'topicSelect',
                        type: 'BlurbItem',
                        title: 'Topic',
                        value: Datasource.getInstance().getRecord().name,
                        showTitle:true
                    },
                    {
                        name: 'datasetName',
                        type: 'hidden',
                        value: ''
                    },
                    {
                        name: 'files[datafile]',
                        cellClassName:'uploadField',
                        type: 'UploadItem',
                        required:true,
                        title: 'Select File',
                        width: 300,
                        defaultValue :'',
                        showTitle:true
                    },
                    {
                        name:"appendTypeRadio",
                        cellClassName: 'fileSelect',
                        height: 20,
                        type:"radioGroup",
                        vertical: true,
                        valueMap:{append:"This file appends the existing data.",
                        replace:"This file replaces the existing data."},
                        defaultValue:"append",
                        showTitle:true,
                        visible:false,
                        title:"Upload Options"
                    },
                    {
                        name:'datasetUploadInfoName',
                        type: 'BlurbItem',
                        title:'',
                        showTitle:true,
                        value: 'Acceptable format: Comma separated text file in uncompressed or compressed (zip) format.'
                    },
                    {
                        type: "BlurbItem",
                        name: "datasetUploadBlurb1",
                        value:''
                    },
                    {
                        type: "BlurbItem",
                        name: "datasetUploadBlurb2",
                        colSpan:1,
                        endRow:false,
                        textAlign:"right",
                        cellStyle:"datasetUploadCancelLink",
                        value:'<a href="javascript:DatasetActions.datasetList();">Cancel</a>'
                    },
                    {
                        type: "submit",
                        colSpan:1,
                        startRow:false,
                        title: 'Upload Data'
                    }
                ],
                submitDone: function ( result ) {
                    DatasetActions.handleDataSubmit(result);
                }
            });
 		}
        return this.datasetUploadForm;
    }

});
