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
 * Manage the course activities view navigation for the course activities block.
 *
 * @package    block_course_activities
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'core/custom_interaction_events',
    'block_course_activities/view',
    'core/ajax',
    'core/notification',
    'block_course_activities/view_courses'
],
function(
    $,
    CustomEvents,
    View,
    Ajax,
    Notification,
    ViewCourses
) {

    var SELECTORS = {
        COURSE_ACTIVITIES_DAY_FILTER: '[data-region="course-filter"]',
        COURSE_ACTIVITIES_DAY_FILTER_OPTION: '[data-from]',
        COURSE_ACTIVITIES_VIEW_SELECTOR: '[data-region="view-selector"]',
        DATA_DAYS_OFFSET: '[data-days-offset]',
        DATA_DAYS_LIMIT: '[data-days-limit]',
        COURSE_ACTIVITIES_COURSES_VIEW: '[data-region="view-courses"]',
    };

    /**
     * Generic handler to persist user preferences
     *
     * @param {string} type The name of the attribute you're updating
     * @param {string} value The value of the attribute you're updating
     */
    var updateUserPreferences = function(type, value) {
        var request = {
            methodname: 'core_user_update_user_preferences',
            args: {
                preferences: [
                    {
                        type: type,
                        value: value
                    }
                ]
            }
        };

        Ajax.call([request])[0]
            .fail(Notification.exception);
    };

    /**
     * Event listener for the day selector ("Next 7 days", "Next 30 days", etc).
     *
     * @param {object} root The root element for the course activities block
     * @param {object} courseActivitiesViewRoot The root element for the course activities view
     */
    var registerCourseActivitiesDaySelector = function(root, courseActivitiesViewRoot) {

        var courseActivitiesDaySelectorContainer = root.find(SELECTORS.COURSE_ACTIVITIES_DAY_FILTER);

        CustomEvents.define(courseActivitiesDaySelectorContainer, [CustomEvents.events.activate]);
        courseActivitiesDaySelectorContainer.on(
            CustomEvents.events.activate,
            SELECTORS.COURSE_ACTIVITIES_DAY_FILTER_OPTION,
            function(e, data) {
                // Update the user preference
                var filtername = $(e.currentTarget).data('filtername');
                var type = 'block_course_activities_user_course_preference';
                updateUserPreferences(type, filtername);

                var option = $(e.target).closest(SELECTORS.COURSE_ACTIVITIES_DAY_FILTER_OPTION);

                if (option.attr('aria-current') == 'true') {
                    // If it's already active then we don't need to do anything.
                    return;
                }

                var daysOffset = option.attr('data-from');
                var daysLimit = option.attr('data-to');
                var course = option.attr('data-course');
                var elementsWithDaysOffset = root.find(SELECTORS.DATA_DAYS_OFFSET);

                elementsWithDaysOffset.attr('data-days-offset', daysOffset);
                elementsWithDaysOffset.attr('data-course-id', course);
                if (daysLimit != undefined) {
                    elementsWithDaysOffset.attr('data-days-limit', daysLimit);
                } else {
                    elementsWithDaysOffset.removeAttr('data-days-limit');
                }
                // Reset the views to reinitialise the event lists now that we've
                // updated the day limits.
                View.reset(courseActivitiesViewRoot);

                data.originalEvent.preventDefault();
            }
        );
    };

    /**
     * Event listener for the "sort" button in the course activities navigation that allows for
     * changing between the course activities dates and courses views.
     *
     * On a view change we tell the course activities view module that the view has been shown
     * so that it can handle how to display the appropriate view.
     *
     * @param {object} root The root element for the course activities block
     * @param {object} courseActivitiesViewRoot The root element for the course activities view
     */
    var registerViewSelector = function(root, courseActivitiesViewRoot) {
        var viewSelector = root.find(SELECTORS.COURSE_ACTIVITIES_VIEW_SELECTOR);

        // Listen for when the user changes tab so that we can show the first set of courses
        // and load their events when they request the sort by courses view for the first time.
        viewSelector.on('shown shown.bs.tab', function(e) {
            View.shown(courseActivitiesViewRoot);
            $(e.target).removeClass('active');
        });


        // Event selector for user_sort
        CustomEvents.define(viewSelector, [CustomEvents.events.activate]);
        viewSelector.on(CustomEvents.events.activate, "[data-toggle='tab']", function(e) {
            var filtername = $(e.currentTarget).data('filtername');
            var type = 'block_course_activities_user_sort_preference';
            updateUserPreferences(type, filtername);
        });
    };

    /**
     * Initialise the course activities view navigation by adding event listeners to
     * the navigation elements.
     *
     * @param {object} root The root element for the course activities block
     * @param {object} courseActivitiesViewRoot The root element for the course activities view
     */
    var init = function(root, courseActivitiesViewRoot) {
        root = $(root);
        registerCourseActivitiesDaySelector(root, courseActivitiesViewRoot);
        registerViewSelector(root, courseActivitiesViewRoot);
    };

    /**
     * Reset the course activities dates and courses views to their original
     * state on first page load.
     *
     * This is called when configuration has changed for the event lists
     * to cause them to reload their data.
     *
     * @param {object} root The root element for the course activities view.
     */
    var reset = function(root) {
        var coursesViewRoot = root.find(SELECTORS.COURSE_ACTIVITIES_COURSES_VIEW);
        ViewCourses.reset(coursesViewRoot);
    };

    /**
     * Tell the course activities dates or courses view that it has been displayed.
     *
     * This is called each time one of the views is displayed and is used to
     * lazy load the data within it on first load.
     *
     * @param {object} root The root element for the course activities view.
     */
    var shown = function(root) {
        var coursesViewRoot = root.find(SELECTORS.COURSE_ACTIVITIES_COURSES_VIEW);
        ViewCourses.shown(coursesViewRoot);
    };

    return {
        init: init,
        reset:reset,
        shown:shown,
    };
});
