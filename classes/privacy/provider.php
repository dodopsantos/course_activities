<?php
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
 * Privacy Subsystem implementation for block_course_activities.
 *
 * @package    block_course_activities
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_activities\privacy;

defined('MOODLE_INTERNAL') || die();
use \core_privacy\local\metadata\collection;

/**
 * Privacy Subsystem for block_course_activities.
 *
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta-data information about the myoverview block.
     *
     * @param  \core_privacy\local\metadata\collection $collection A collection of meta-data.
     * @return \core_privacy\local\metadata\collection Return the collection of meta-data.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('block_course_activities_user_sort_preference', 'privacy:metadata:courseactivitiessortpreference');
        $collection->add_user_preference('block_course_activities_user_filter_preference', 'privacy:metadata:courseactivitiesfilterpreference');
        $collection->add_user_preference('block_course_activities_user_limit_preference', 'privacy:metadata:courseactivitieslimitpreference');
        $collection->add_user_preference('block_course_activities_user_course_preference', 'privacy:metadata:courseactivitiescoursepreference');
        return $collection;
    }

    /**
     * Export all user preferences for the myoverview block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('block_course_activities_user_sort_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_course_activities', 'block_course_activities_user_sort_preference',
                    get_string($preference, 'block_course_activities'),
                    get_string('privacy:metadata:courseactivitiessortpreference', 'block_course_activities')
            );
        }

        $preference = get_user_preferences('block_course_activities_user_filter_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_course_activities', 'block_course_activities_user_filter_preference',
                    get_string($preference, 'block_course_activities'),
                    get_string('privacy:metadata:courseactivitiesfilterpreference', 'block_course_activities')
            );
        }

        $preference = get_user_preferences('block_course_activities_user_course_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_course_activities', 'block_course_activities_user_course_preference',
                get_string($preference, 'block_course_activities'),
                get_string('privacy:metadata:courseactivitiescoursepreference', 'block_course_activities')
            );
        }

        $preference = get_user_preferences('block_course_activities_user_limit_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_course_activities', 'block_course_activities_user_limit_preference',
                    $preference,
                    get_string('privacy:metadata:courseactivitieslimitpreference', 'block_course_activities')
            );
        }
    }
}
