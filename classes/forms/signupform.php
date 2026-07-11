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

namespace local_course_carousel\forms;

/**
 * Class signupform
 *
 * @package    local_course_carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class signupform extends \moodleform {
     public function definition() {

        $mform = $this->_form; // Don't forget the underscore!
        global $CFG;
        // Form Heading
        $mform->addElement('header', 'signupform', get_string('signupform', 'local_course_carousel'));
        // Hidden Field
        $courseid = $this->_customdata['courseid'];
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        // E Mail Address
        $mform->addElement('text', 'email', get_string('email','local_course_carousel'),['placeholder'=>'Enter Email']);
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('required','local_course_carousel'), 'required', null, 'client');
        // Firstname
        $mform->addElement('text', 'firstname', get_string("firstname", "local_course_carousel"), ['placeholder'=>'Enter First Name']);
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('required', 'local_course_carousel'), 'required', null, 'client');
        // Lastname
        $mform->addElement('text', 'lastname', get_string("lastname", "local_course_carousel"), ['placeholder'=>'Enter Last Name']);
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('required', 'local_course_carousel'), 'required', null, 'client');


        $buttonarray=array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}
