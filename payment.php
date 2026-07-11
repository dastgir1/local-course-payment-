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
 * Payment page for course_carousel
 *
 * @package    local_course_carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

// Read params BEFORE header() so redirects work if validation fails.
$userid   = required_param('uid', PARAM_INT);
$courseid = required_param('cid', PARAM_INT);

$url = new moodle_url('/local/course_carousel/payment.php', ['uid' => $userid, 'cid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

require_login();

// Security: ensure the logged-in user matches the uid in the URL.
if ($USER->id != $userid) {
    throw new moodle_exception('accessdenied', 'admin');
}

// Fetch records with null safety.
$enrol  = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'fee'], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();

$cost = (float)$enrol->cost;
if ($cost <= 0) {
    $cost = (float)get_config('enrol_fee', 'cost');
}
$currency = $enrol->currency;
if (empty($currency)) {
    $currency = get_config('enrol_fee', 'currency');
}

$formattedcost = \core_payment\helper::get_cost_as_string($cost, $currency);

echo "<strong>Subscription Summary</strong><br>";
echo "You are going to subscribe to: <strong>" . format_string($course->fullname) . "</strong><br>";
echo "Subscription Charges: <strong>" . $formattedcost . "</strong><br><br>";

$data = [
    'itemid'      => $enrol->id,
    'coststring'  => $formattedcost,
    'description' => 'Enrolment in ' . format_string($course->fullname),
];
echo $OUTPUT->render_from_template('local_course_carousel/paynow', $data);

echo $OUTPUT->footer();

