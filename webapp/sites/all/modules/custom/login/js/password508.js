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

(function ($) {
    Drupal.behaviors.password508 = {
        attach: function (context, settings) {
            $('#gd-login-password-change-form', context).one('ready', function() {
                $(this).find('div.password-strength-title').attr('tabindex', 2902);
                $(this).find('div.password-strength-text').attr('tabindex', 2903);
                $(this).find('div.password-confirm').attr('tabindex', 3000);
                $(this).find('div.password-suggestions').attr('tabindex', 3000);
                var _this = this;
                $(this).find('div.password-suggestions').focus(function () {
                    $(_this).find('div.password-suggestions ul').attr('tabindex', 3000);
                });
            });
        }
    };
})(jQuery);
