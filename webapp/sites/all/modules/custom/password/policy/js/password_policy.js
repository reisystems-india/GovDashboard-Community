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

Drupal.evaluatePasswordStrength = function (value) {
    var policy = Drupal.settings.password_policy;
    var specials = ['!','#','$','%','-','_','=','+','<','>'];
    var numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
    var messages = [], strength = 100;
    if (!value.length || value.length < policy['length']) {
        strength -= 20;
        messages.push("Make it at least 12 characters");
    }

    var upper = 0,lower = 0, number = 0, symbols = 0;
    for(var i = 0; i < value.length; i++) {
        var c = value.charAt(i);
        if (jQuery.inArray(c, specials) !== -1) {
            symbols++;
        } else if (jQuery.inArray(parseInt(c), numbers) !== -1) {
            number++;
        } else if(c == c.toLowerCase()) {
            lower++;
        } else if (c == c.toUpperCase()) {
            upper++;
        }
    }

    if (upper < policy['upper']) {
        strength -= 20;
        messages.push("Add at least " + policy['upper'] + " upper case letters");
    }

    if (lower < policy['lower']) {
        strength -= 20;
        messages.push("Add at least " + policy['lower'] + " lower case letters");
    }

    if (number < policy['number']) {
        strength -= 20;
        messages.push("Add at least " + policy['number'] + " numbers");
    }

    if (symbols < policy['symbols']) {
        strength -= 20;
        messages.push("Add at least " + policy['symbols'] + " special characters: ! # $ % - _ = + < >");
    }

    var message = '';
    if (strength < 100) {
        message = "The password does not include enough variation to be secure.<ul><li>"+ messages.join("</li><li>") +"</li></ul>";
    }

    var level = strength >= 66 ? "high" : (strength >= 33 ? "medium" : "low");
    return { strength: strength, indicatorText: level, message: message };
};