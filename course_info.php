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
 * TODO describe file course_carousel
 *
 * @package    local_course_carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/course_carousel/lib.php');


$url = new moodle_url('/local/course_carousel/course_info.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->requires->js_call_amd('local_course_carousel/carousel', 'init');
$PAGE->requires->css(new moodle_url('/local/course_carousel/assets/css/carousel.css'));
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
$courseid=required_param('id', PARAM_INT);
$course = get_course($courseid);
$courseContext = context_course::instance($courseid);

$fs = get_file_storage();
// Returns an array of `stored_file` instances.
$files = $fs->get_area_files($courseContext->id, 'course', 'overviewfiles', false, 'itemid', false);

if ($files) {
    $file = reset($files);
    $course->imageurl = file_encode_url(
            "$CFG->wwwroot/pluginfile.php",
            '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
            false
        );
} else {
    $course->imageurl = $CFG->wwwroot . '/theme/image.php?theme=boost&amp;component=theme&amp;image=no-image';
}

// print_object($course);
// Get the payment enrolment instance for the course.
$enrol = $DB->get_record('enrol', [
    'courseid' => $courseid,
    'enrol' => 'fee' // Use 'fee' if you're using the Fee enrolment plugin.
]);


$cost = (float)$enrol->cost;
if ($cost <= 0) {
    $cost = (float)get_config('enrol_fee', 'cost');
}
$currency = $enrol->currency;
if (empty($currency)) {
    $currency = get_config('enrol_fee', 'currency');
}

$coursedata = [
    'courseimageurl'=>$course->imageurl,
    'coursename'=>$course->fullname,
    'coursesummary'=>$course->summary,
    'courseprice'=>$cost,
    'currency'=>$currency,
    'signuplink'=>'/local/course_carousel/signupform.php?courseid='.$courseid,
];

$templatecontext=[
    'coursedata'=>$coursedata
];
echo $OUTPUT->render_from_template('local_course_carousel/course_info',$templatecontext);
echo $OUTPUT->footer();