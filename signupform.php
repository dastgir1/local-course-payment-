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
 * Signup form page for course_carousel
 *
 * @package    local_course_carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/datalib.php");
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$PAGE->requires->css(new moodle_url('/local/course_carousel/assets/css/carousel.css'));
// Must be read BEFORE header() so redirects still work.
$courseid = required_param('courseid', PARAM_INT);

$url = new moodle_url('/local/course_carousel/signupform.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);

$mform = new \local_course_carousel\forms\signupform(null, ['courseid' => $courseid]);

if ($mform->is_cancelled()) {
    // Redirect to course page on cancel.
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));

} else if ($fromform = $mform->get_data()) {

    $courseid = (int)$fromform->courseid;

    // Build user object.
    $userdata                = new stdClass();
    $userdata->firstname     = $fromform->firstname;
    $userdata->lastname      = $fromform->lastname;
    $userdata->email         = $fromform->email;
    $userdata->username      = strtolower($fromform->email); // username must be lowercase.
    $userdata->auth          = 'manual';
    $userdata->confirmed     = 1;
    $userdata->lang          = 'en';
    $userdata->timecreated   = time();
    $userdata->maildisplay   = 0;
    $userdata->mnethostid    = $CFG->mnet_localhost_id;

    // Generate a plain-text password (saved properly after user creation).
    $password = generate_password();

    // Create the user (do NOT hash password here; update_internal_user_password handles it).
    $newuserid = user_create_user($userdata, false, false);

    if ($newuserid) {
        // Now set the password correctly using Moodle's internal function.
        $newuser = \core_user::get_user($newuserid);
        update_internal_user_password($newuser, $password);

        // Send welcome email with credentials.
        // email_to_user($to, $from, $subject, $messagetext).
        // $from must be a user object or a string (noreply).
        $noreply = \core_user::get_noreply_user();
        $subject  = get_string('pluginname', 'local_course_carousel') . ' - Login Details';
        $message  = "Hello {$userdata->firstname},\n\n"
                  . "Your account has been created.\n"
                  . "Username: {$userdata->username}\n"
                  . "Password: {$password}\n\n"
                  . "Please login and change your password.\n";

        email_to_user($newuser, $noreply, $subject, $message);

        // Auto-login the newly created user.
        // Moodle manages its own session — never call session_start() manually.
        $newuser = complete_user_login($newuser);

        // Redirect to payment page.
        redirect(new moodle_url('/local/course_carousel/payment.php', [
            'uid' => $newuser->id,
            'cid' => $courseid,
        ]));
    }

} else {
    // Display the form on first load or validation failure.
    echo $OUTPUT->header();
    ?>

    <div class="signup-container">
    <?php
    $mform->display();
    ?>
    </div>
    <?php
    echo $OUTPUT->footer();
}
