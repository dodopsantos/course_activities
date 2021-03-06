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
 * Class containing data for course activities block.
 *
 * @package    block_course_activities
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_course_activities\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_course\external\course_summary_exporter;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/blocks/course_activities/lib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Class containing data for course activities block.
 *
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /** Number of courses to load per page */
    const COURSES_PER_PAGE = 2;

    /**
     * @var string The current filter preference
     */
    public $filter;

    /**
     * @var string The current sort/order preference
     */
    public $order;

    /**
     * @var string The current limit preference
     */
    public $limit;

    /**
     * main constructor.
     *
     * @param string $course Constant course value from ../course_activities/lib.php
     * @param string $order Constant sort value from ../course_activities/lib.php
     * @param string $filter Constant filter value from ../course_activities/lib.php
     * @param string $limit Constant limit value from ../course_activities/lib.php
     */
    public function __construct($order, $filter, $limit, $course) {
        $this->course = $course ? $course : BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE1;
        $this->order = $order ? $order : BLOCK_COURSE_ACTIVITIES_SORT_BY_DATES;
        $this->filter = $filter ? $filter : BLOCK_COURSE_ACTIVITIES_FILTER_BY_7_DAYS;
        $this->limit = $limit ? $limit : BLOCK_COURSE_ACTIVITIES_ACTIVITIES_LIMIT_DEFAULT;
    }

    /**
     * Test the available filters with the current user preference and return an array with
     * bool flags corresponding to which is active
     *
     * @return array
     */
    protected function get_filters_as_booleans() {
        $filters = [
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_NONE => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_OVERDUE => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_7_DAYS => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_30_DAYS => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_3_MONTHS => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_6_MONTHS => false
        ];

        // Set the selected filter to true.
        $filters[$this->filter] = true;

        return $filters;
    }

    /**
     * Test the available filters with the current user preference and return an array with
     * bool flags corresponding to which is active
     *
     * @return array
     */
    protected function get_course_as_booleans() {
        $courses = [
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE1 => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE2 => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE3 => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE4 => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE5 => false,
            BLOCK_COURSE_ACTIVITIES_FILTER_BY_COURSE6 => false
        ];

        // Set the selected filter to true.
        $courses[$this->course] = true;

        return $courses;
    }

    /**
     * Get the offset/limit values corresponding to $this->filter
     * which are used to send through to the context as default values
     *
     * @return array
     */
    private function get_filter_offsets() {

        $limit = false;

        if (in_array($this->filter, [BLOCK_COURSE_ACTIVITIES_FILTER_BY_NONE, BLOCK_COURSE_ACTIVITIES_FILTER_BY_OVERDUE])) {
            $offset = -14;
            if ($this->filter == BLOCK_COURSE_ACTIVITIES_FILTER_BY_OVERDUE) {
                $limit = 0;
            }
        } else {
            $offset = 0;
            $limit = 7;

            switch($this->filter) {
                case BLOCK_COURSE_ACTIVITIES_FILTER_BY_30_DAYS:
                    $limit = 30;
                    break;
                case BLOCK_COURSE_ACTIVITIES_FILTER_BY_3_MONTHS:
                    $limit = 90;
                    break;
                case BLOCK_COURSE_ACTIVITIES_FILTER_BY_6_MONTHS:
                    $limit = 180;
                    break;
            }
        }

        return [
            'daysoffset' => $offset,
            'dayslimit' => $limit
        ];
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $nocoursesurl = $output->image_url('courses', 'block_course_activities')->out();
        $noeventsurl = $output->image_url('activities', 'block_course_activities')->out();

        $requiredproperties = course_summary_exporter::define_properties();
        $fields = join(',', array_keys($requiredproperties));
        $courses = course_get_enrolled_courses_for_logged_in_user(0, 0, null, $fields);
        list($inprogresscourses, $processedcount) = course_filter_courses_by_timeline_classification(
            $courses,
            COURSE_TIMELINE_INPROGRESS,
            self::COURSES_PER_PAGE
        );
        $formattedcourses = array_map(function($course) use ($output) {
            \context_helper::preload_from_record($course);
            $context = \context_course::instance($course->id);
            $exporter = new course_summary_exporter($course, ['context' => $context]);
            return $exporter->export($output);
        }, $inprogresscourses);

        $courses = $DB->get_records('course');
        $listCourses = [];
        foreach ($courses as $course) {
            array_push($listCourses, (object)[
                'id' => $course->id,
                'name' => $course->fullname,
                'datafilter' => 'course'.$course->id,
            ]);
        }

        $filters = $this->get_filters_as_booleans();
        $offsets = $this->get_filter_offsets();
//        $courseID = $this->get_course_as_booleans();
        $contextvariables = [
            'selectorcourse' => $listCourses,
            'midnight' => usergetmidnight(time()),
            'coursepages' => [$formattedcourses],
            'urls' => [
                'nocourses' => $nocoursesurl,
                'noevents' => $noeventsurl
            ],
            'sortcourseactivitiescourses' => $this->order == BLOCK_COURSE_ACTIVITIES_SORT_BY_COURSES,
            'selectedfilter' => $this->filter,
            'selectedcourse' => $this->course,
            'hasdaysoffset' => true,
            'hasdayslimit' => $offsets['dayslimit'] !== false ,
            'nodayslimit' => $offsets['dayslimit'] === false ,
            'limit' => $this->limit
        ];
        return array_merge($contextvariables, $filters, $offsets);
    }
}
