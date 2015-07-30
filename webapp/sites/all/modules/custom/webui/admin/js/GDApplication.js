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


var global_NumericDataTypeArray = ['integer', 'number', 'currency', 'percent'];

var global_PercentNumberDataTypeArray = ['percentage', 'percent'];

var global_DateDataTypeArray = ['date', 'datetime',  'date:year', 'date:month', 'date:quarter', 'name@common:month_def', 'code@common:quarter_def'];

var GDApplication = isc.defineClass("GDApplication", "Layout");

GDApplication = GDApplication.addProperties({

    styleName: "GDApplication",
    overflow:"visible",
    accountName:'Default Account',
    mainTab:null,
    account:null,
    user:null,
    token: null,
    environment: null,

    sections: null,

    initWidget:function () {
        this.Super("initWidget", arguments);

        this.urlParams = (function(a) {
            if (a == "") return {};
            var b = {};
            for (var i = 0; i < a.length; ++i)
            {
                var p=a[i].split('=');
                if (p.length != 2) continue;
                b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
            }
            return b;
        })(window.location.search.substr(1).split('&'));

        if ( typeof arguments[0].datasources != 'undefined' ) {
            this.datasources = [];
            for ( var i = 0,datasource_count=arguments[0].datasources.length; i < datasource_count; i++ ) {
                this.datasources.push(new GD.Datasource(arguments[0].datasources[i]));
            }
        }

        if ( typeof arguments[0].datasource != 'undefined' ) {
            this.datasource = new GD.Datasource(arguments[0].datasource);
        }

        Datasource.getInstance().setRecord(arguments[0].datasource);

        if ( typeof arguments[0].sections != 'undefined' ) {
            this.sections = arguments[0].sections;
        }

        if ( typeof arguments[0].environment != 'undefined' ) {
            this.environment = arguments[0].environment;
        }

        if ( typeof arguments[0].token != 'undefined' ) {
            this.token = arguments[0].token;
            var _this = this;
            jQuery.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    xhr.setRequestHeader("X-CSRF-Token", _this.token);
                }
            });
        }
    },

    run: function ( path ) {

        this.addMember(this.getMainContent());

        this.renderSectionsNav();
        this.renderDatasourceSelector();

        if ( path ) {
            GDNavigation.dispatch(this,path);
        }
    },

    getMainContent: function () {
        if ( typeof this.mainContent == 'undefined' ) {
            this.mainContent = isc.VLayout.create({
                members:[
                    isc.VLayout.create({
                        ID:"GDMainContent",
                        members:[
                            MessageSection.getContainer(),
                            this.getBody()
                        ]
                    })
                ]
            });
        }

        return this.mainContent;
    },

    getBody: function () {
        if ( typeof this.body == "undefined" ) {

            this.body = isc.VLayout.create({
                autoDraw:false,
                members:[]
            });

            //var _this = this;
            //jQuery.each(this.getSections(),function(){
            //    _this.body.addMember(this.getContainer());
            //});
        }

        return this.body;
    },

    getSections: function () {
        if ( this.sections === null ) {
            this.sections = [
                DatasetSection,
                ReportSection,
                DashboardSection,
                AccountSection
            ];
        }

        return this.sections;
    },

    renderSectionsNav: function () {
        this.uri = new GD.UriHandler();
        // prepare section order
        var sections = [];
        jQuery.each(this.sections,function(k,S){
            sections.push(S);
        });
        sections.sort(function(a, b) {
            if ( a.weight < b.weight ) {
                return -1;
            }
            if ( a.weight > b.weight ) {
                return 1;
            }
            return 0;
        });

        var parts = this.uri.path.slice(1).split('/');
        var menu = '<ul id="gd-admin-main-nav" class="nav navbar-nav">';
        for ( var i = 0,count=sections.length; i < count; i++ ) {
            var active = '';
            if ( parts.length == 1 && sections[i].getName() == 'dataset' || parts[1] == sections[i].getName() ) {
                active = ' class="active"';
            }
            menu += '<li'+active+'><a tabindex="10" class="gd-admin-main-nav-item" href="'+sections[i].getDefaultRoute()+'">'+sections[i].getTitle()+'</a></li>';
        }

        menu += '</ul>';

        jQuery('div.container',jQuery('#gd-navbar')).append(menu);
    },

    renderDatasourceSelector: function () {
        var selectView = new GD.DatasourceSelectView(this.datasources,jQuery('div.container',jQuery('#gd-navbar')));
        selectView.render();
    },

    getAccount: function () {
        return this.account;
    },

    getUser: function () {
        return this.user;
    },

    getDatasource: function () {
        return this.datasource;
    },

    getConfigurationFlag: function ( name ) {
        if ( this.environment ) {
            if ( typeof this.environment[name] != 'undefined' ) {
                return this.environment[name];
            }
        }
        return null;
    }
});