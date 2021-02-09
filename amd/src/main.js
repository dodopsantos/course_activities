// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript to initialise the course activities block.
 *
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'block_course_activities/view_nav',
    'block_course_activities/view'
],
function(
    $,
    ViewNav,
    View
) {

    var SELECTORS = {
        COURSE_ACTIVITIES_VIEW: '[data-region="course-activities-view"]'
    };

    /**
     * Initialise all of the modules for the course activities block.
     *
     * @param {object} root The root element for the course activities block.
     */
    var init = function(root) {
        root = $(root);
        var viewRoot = root.find(SELECTORS.COURSE_ACTIVITIES_VIEW);

        // Initialise the course activities navigation elements.
        ViewNav.init(root, viewRoot);
        // Initialise the course activities view modules.
        View.init(viewRoot);
    };

    return {
        init: init
    };
});
