<?php
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

if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
    echo "<h3>Access Denied</h3>";
    drupal_exit();
}
?>
<div class="highcharts-title"><a href='javascript:;' onclick='parent.UserListActions.showActiveUsers()' target='_parent'><?php echo $user_chart['active']; ?> Active users</a> (<?php echo $user_chart['max']; ?> Max)</div>
<div class="highcharts-dial" id="active-user-dial"></div>

<script type="text/javascript">
    //<![CDATA[
    $(function() {
        function drawDial (options) {

            var renderTo = options.renderTo,
                    value = options.value,
                    centerX = options.centerX,
                    centerY = options.centerY,
                    min = options.min,
                    max = options.max,
                    minAngle = options.minAngle,
                    maxAngle = options.maxAngle,
                    tickInterval = options.tickInterval,
                    ranges = options.ranges;

            var renderer = new Highcharts.Renderer(
                    document.getElementById(renderTo),
                    400,
                    250
            );


            // internals
            var angle,
                    pivot;

            function valueToAngle (value) {
                return (maxAngle - minAngle) / (max - min) * value + minAngle;
            }

            function setValue (value) {
                // the pivot
                angle = valueToAngle(value);

                var path = [
                    'M',
                    centerX, centerY,
                    'L',
                    centerX + 110 * Math.cos(angle), centerY + 110 * Math.sin(angle)
                ];

                if (!pivot) {
                    pivot = renderer.path(path)
                            .attr({
                                stroke: '#828995',
                                'stroke-width': 3
                            })
                            .add();
                } else {
                    pivot.attr({
                        d: path
                    });
                }
            }

            // background area
            renderer.arc(centerX, centerY, 100, 0, minAngle, maxAngle)
                    .attr({
                        fill: {
                            linearGradient: [0, 0, 0, 200],
                            stops: [
                                [0, '#FFF'],
                                [1, '#EEE']
                            ]
                        },
                        stroke: '#EEE',
                        'stroke-width': 10
                    })
                    .add();


            // ranges
            $.each(ranges, function(i, rangesOptions) {
                renderer.arc(
                        centerX,
                        centerY,
                        170,
                        150,
                        valueToAngle(rangesOptions.from),
                        valueToAngle(rangesOptions.to)
                )
                        .attr({
                            fill: rangesOptions.color
                        })
                        .add();
            });

            // ticks
            for (var i = min; i <= max; i += tickInterval) {

                angle = valueToAngle(i);

                // draw the tick marker
                renderer.path([
                    'M',
                    centerX + 170 * Math.cos(angle), centerY + 170 * Math.sin(angle),
                    'L',
                    centerX + 150 * Math.cos(angle), centerY + 150 * Math.sin(angle)
                ])
                        .attr({
                            stroke: '#FFF',
                            'stroke-width': 3
                        })
                        .add();

                // draw the text
                renderer.text(
                        i,
                        centerX + 130 * Math.cos(angle),
                        centerY + 130 * Math.sin(angle)
                )
                        .attr({
                            align: 'center'
                        })
                        .add();

            }

            // the initial value
            setValue(value);

            // center disc
            renderer.circle(centerX, centerY, 8)
                    .attr({
                        fill: '#9CA1AB',
                        stroke: '#828995',
                        'stroke-width': 2
                    })
                    .add();

            return {
                setValue: setValue
            };

        }


        // Build the dial
        var dial = drawDial({
            renderTo: 'active-user-dial',
            value: <?php echo $user_chart['calculated']; ?>,
            centerX: 200,
            centerY: 200,
            min: 0,
            max: <?php echo $user_chart['max']; ?>,
            minAngle: -Math.PI,
            maxAngle: 0,
            tickInterval: <?php echo round(($user_chart['max'] * 20) / 100); ?>,
            ranges: [
                {
                    from: 0,
                    to: <?php echo round(($user_chart['max'] * 20) / 100); ?>,
                    color: '#1C3F95'
                },
                {
                    from: <?php echo round(($user_chart['max'] * 20) / 100); ?>,
                    to: <?php echo round(($user_chart['max'] * 60) / 100); ?>,
                    color: '#E98035'
                },
                {
                    from: <?php echo round(($user_chart['max'] * 60) / 100); ?>,
                    to: <?php echo $user_chart['max']; ?>,
                    color: '#C41230'
                }
            ]
        });

        // Button handlers
        $('#set0').click(function() {
            dial.setValue(0);
        });
        $('#set80').click(function() {
            dial.setValue(80);
        });
        $('#set160').click(function() {
            dial.setValue(160);
        });

    });
    //]]>
</script>

