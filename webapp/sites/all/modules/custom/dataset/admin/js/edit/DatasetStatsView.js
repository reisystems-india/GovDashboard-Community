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
        throw new Error('DatasetStatsView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetStatsView requires GD');
    }

    var GD = global.GD;

    var DatasetStatsView = GD.View.extend({
        statsDisplay: null,
        statsContainer: null,
        datasetReferencedContainer: null,
        datasetReferenced: null,
        reportReferencedContainer: null,
        reportReferenced: null,
        dashboardReferencedContainer: null,
        dashboardReferenced: null,

        toggleCreateView: function () {
            this.getStatsContainer().hide();
        },

        loadedDataset: function ( dataset, reloadView ) {
            var _this = this;
            this.object = dataset;
            if ( reloadView ) {
                if ( this.object != null && this.object.getName() ) {
                    //  TODO Pull to widget and register loadReferenced event
                    GD.DatasetFactory.getReferenced(dataset.getName(), function ( data ) {
                        var markup = [];
                        var uniqueIds = [];

                        if ( typeof data.datasets != "undefined" ) {
                            _this.getDatasetReferenced().empty();
                            if ( data.datasets.length == 0 ) {
                                _this.getDatasetReferenced().append('(none)');
                            } else {
                                $.each(data.datasets, function (i, d) {
                                    if ( $.inArray(d['id'], uniqueIds) == -1 ) {
                                        markup.push('<a href="/cp/dataset/' + d['id'] + '">' + d['title'] + '</a>');
                                        uniqueIds.push(d['id']);
                                    }
                                });
                                _this.getDatasetReferenced().append(markup.join(', '));
                            }
                        }

                        if ( typeof data.reports != "undefined" ) {
                            _this.getReportReferenced().empty();
                            if ( data.reports.length == 0 ) {
                                _this.getReportReferenced().append('(none)');
                            } else {
                                markup = [];
                                uniqueIds = [];
                                $.each(data.reports, function (i, d) {
                                    if ( $.inArray(d['id'], uniqueIds) == -1 ) {
                                        markup.push('<a href="/cp/report/' + d['id'] + '">' + d['title'] + '</a>');
                                        uniqueIds.push(d['id']);
                                    }
                                });
                                _this.getReportReferenced().append(markup.join(', '));
                            }
                        }

                        if ( typeof data.dashboards != "undefined" ) {
                            _this.getDashboardReferenced().empty();
                            if ( data.dashboards.length == 0 ) {
                                _this.getDashboardReferenced().append('(none)');
                            } else {
                                markup = [];
                                uniqueIds = [];
                                $.each(data.dashboards, function (i, d) {
                                    if ( $.inArray(d['id'], uniqueIds) == -1 ) {
                                        markup.push('<a href="/cp/dashboard/' + d['id'] + '">' + d['title'] + '</a>');
                                        uniqueIds.push(d['id']);
                                    }
                                });
                                _this.getDashboardReferenced().append(markup.join(', '));
                            }
                        }
                    });
                }
            }
        },

        getDatasetReferenced: function () {
            if ( this.datasetReferenced == null ) {
                this.datasetReferenced = $('<small></small>');
            }
            return this.datasetReferenced;
        },

        getDatasetReferencedContainer: function () {
            if ( this.datasetReferencedContainer == null ) {
                this.datasetReferencedContainer = $('<div class="col-md-4"></div>');
                this.datasetReferencedContainer.append('<strong>Datasets</strong><br/>');
                this.datasetReferencedContainer.append(this.getDatasetReferenced());
            }

            return this.datasetReferencedContainer;
        },

        getReportReferenced: function () {
            if ( this.reportReferenced == null ) {
                this.reportReferenced = $('<small></small>');
            }
            return this.reportReferenced;
        },

        getReportReferencedContainer: function () {
            if ( this.reportReferencedContainer == null ) {
                this.reportReferencedContainer = $('<div class="col-md-4"></div>');
                this.reportReferencedContainer.append('<strong>Reports</strong><br/>');
                this.reportReferencedContainer.append(this.getReportReferenced());
            }

            return this.reportReferencedContainer;
        },

        getDashboardReferenced: function () {
            if ( this.dashboardReferenced == null ) {
                this.dashboardReferenced = $('<small></small>');
            }
            return this.dashboardReferenced;
        },

        getDashboardReferencedContainer: function () {
            if ( this.dashboardReferencedContainer == null ) {
                this.dashboardReferencedContainer = $('<div class="col-md-4"></div>');
                this.dashboardReferencedContainer.append('<strong>Dashboards</strong><br/>');
                this.dashboardReferencedContainer.append(this.getDashboardReferenced());
            }

            return this.dashboardReferencedContainer;
        },

        getStatsDisplay: function () {
            if ( this.statsDisplay == null ) {
                this.statsDisplay = $('<div class="row"></div>');
                this.statsDisplay.append(this.getDatasetReferencedContainer());
                this.statsDisplay.append(this.getReportReferencedContainer());
                this.statsDisplay.append(this.getDashboardReferencedContainer());
            }

            return this.statsDisplay;
        },

        getStatsContainer: function () {
            if ( this.statsContainer == null ) {
                this.statsContainer = $('<div class="col-md-7"></div>');
                this.statsContainer.append('<p>This dataset is being used in...</p>');
                this.statsContainer.append(this.getStatsDisplay());

                //  TODO Change to loadReferenced
                var _this = this;
                $(document).on('loadedDataset', function (e) {
                    _this.loadedDataset(e['dataset'], e['reloadView']);
                });

                $(document).on('createView', function () {
                    _this.toggleCreateView();
                });
            }

            return this.statsContainer;
        },

        render: function () {
            if ( this.container != null ) {
                $(this.container).append(this.getStatsContainer());
            } else {
                return this.getStatsContainer();
            }
        }
    });

    // add to global space
    global.GD.DatasetStatsView = DatasetStatsView;

})(typeof window === 'undefined' ? this : window, jQuery);