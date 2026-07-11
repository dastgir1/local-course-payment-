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
 * Callback implementations for course_carousel
 *
 * @package    local_course_carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the payment areas supported by this plugin.
 * This registers 'local_course_carousel' as a payment service provider
 * so Moodle routes payment button clicks to our service_provider class.
 *
 * @return array
 */
function local_course_carousel_get_payable_areas(): array {
    return ['enrolment'];
}

function local_course_carousel_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {


    // For a plugin which does not specify the itemid, you may want to use the following to keep your code consistent:
    // $itemid = null;
    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (empty($args)) {
        // $args is empty => the path is '/'.
        $filepath = '/';
    } else {
        // $args contains the remaining elements of the filepath.
        $filepath = '/' . implode('/', $args) . '/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();

    $file = $fs->get_file($context->id, 'local_course_carousel', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        // The file does not exist.
        return false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file);
}
function course_carousel() {
    global $DB, $CFG;

    // Fetch all required comment, user, and role data.
    $courses = $DB->get_records_sql("SELECT id, fullname, summary , startdate, enddate FROM {course} WHERE id != 1");

    $combinedArray = [];
    $tempArray = [];
    $counter = 0;
    $firstSlide = true;
    $noslides = 0;
    foreach ($courses as $course) {
        global $USER;
        $course->summary = format_text($course->summary, FORMAT_HTML);



        if(has_capability('moodle/course:update', context_course::instance($course->id))){

            $course->link  = new moodle_url('/course/view.php', ['id' => $course->id]);
        }else{
                $course->link = new moodle_url('/local/course_carousel/course_info.php', ['id' => $course->id]);

            }

        // Get course image.
        $context = context_course::instance($course->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'itemid', false);

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

        $noslides++;
        $course->slideno=$noslides;
        $startdate = $course->startdate;

        // Date: Thursday, 24 July 2025
        $coursedate = userdate($startdate, '%A, %d %B %Y');

        // Time: 6:00 PM
        $coursetime = userdate($startdate, '%I:%M %p');

        $course->startdate = $coursedate;
        $course->starttime = $coursetime;
        if($course->enddate){
            $enddate = $course->enddate;
            $courseendtime = userdate($enddate, '%I:%M %p');

            $course->endtime=$courseendtime;
        }
        // Add record to temporary array.
        $tempArray[] = $course;
        $counter++;

        // Group every 3 record into the "slide".
        if ($counter == 3) {
            $combinedArray[] = [
                'course' => $tempArray,
                'isFirst' => $firstSlide,

            ];
            $tempArray = [];
            $counter = 0;
            $firstSlide = false;
        }
    }

    if (!empty($tempArray)) {
        $combinedArray[] = [
            'course' => $tempArray,
            'isFirst' => $firstSlide,
        ];
    }

    return $combinedArray;
}