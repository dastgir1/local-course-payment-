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


$url = new moodle_url('/local/course_carousel/course_carousel.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->requires->js_call_amd('local_course_carousel/carousel', 'init');
$PAGE->requires->css(new moodle_url('/local/course_carousel/assets/css/carousel.css'));
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
$courseslist = course_carousel();

$templatecontext=[
    'courses'=>$courseslist,
];
echo $OUTPUT->render_from_template('local_course_carousel/course_carousel',$templatecontext);
echo $OUTPUT->footer();
