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

namespace local_course_carousel\payment;

/**
 * Class service_provider
 *
 * @package    local_course_carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_provider implements \core_payment\local\callback\service_provider{
     /**
     * Callback function that returns the invoice cost and the invoice id
     * for the course that $instanceid enrolment instance belongs to.
     *
     * @param string $paymentarea Payment area
     * @param int $instanceid The enrolment instance id
     * @return \core_payment\local\entities\payable
     */
    public static function get_payable(string $paymentarea, int $instanceid): \core_payment\local\entities\payable {
      global $DB,$COURSE;
        $enrol = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        $total = $enrol->cost;
        $currency = $enrol->currency;

        return new \core_payment\local\entities\payable($total, $currency, 1);
    }
     /**
     * Callback function that returns the URL of the page the user should be redirected to in the case of a successful payment.
     *
     * @param string $paymentarea Payment area
     * @param int $instanceid The enrolment instance id
     * @return \moodle_url
     */
    public static function get_success_url(string $paymentarea, int $instanceid): \moodle_url {
        global $DB, $USER;

        $enrol = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        return new \moodle_url('/course/view.php', ['id' => $enrol->courseid]);
    }

    /**
     * Callback function that delivers what the user paid for to them.
     * Called by Moodle payment subsystem after a successful payment.
     *
     * @param string $paymentarea
     * @param int $instanceid The enrolment instance id
     * @param int $paymentid payment id as inserted into the 'payments' table
     * @param int $userid The userid to enrol
     * @return bool Whether successful or not
     */
    public static function deliver_order(string $paymentarea, int $instanceid, int $paymentid, int $userid): bool {
        global $DB;

        $enrol = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        // Get the fee enrolment plugin instance and enrol the user.
        $enrolplugin = enrol_get_plugin('fee');
        if (!$enrolplugin) {
            return false;
        }

        $course = $DB->get_record('course', ['id' => $enrol->courseid], '*', MUST_EXIST);
        $user   = \core_user::get_user($userid, '*', MUST_EXIST);

        if ($enrol->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $enrol->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }

        // Enrol the user with the fee plugin.
        $enrolplugin->enrol_user($enrol, $user->id, $enrol->roleid, $timestart, $timeend);

        return true;
    }
}
