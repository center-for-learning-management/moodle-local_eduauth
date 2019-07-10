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
 * @package    local_eduauth
 * @copyright  2019 Zentrum für Lernmanagement (http://www.lernmanagement.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Class to handle general functions.
 *
 * @package    local_eduauth
 * @copyright  2019 Zentrum für Lernmanagement (http://www.lernmanagment.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_eduauth_lib {
    // This cache helps to load certain objects from database only once.
    public static $cache = array();
    public static $verifieduserid = 0;
    /**
     * Check a token for validity.
     * Sets the verified_userid token is valid.
     * @param userid userid the token belongs to.
     * @param token token to check.
     * @param appid App-Identification that the token belongs to.
     * @return true if token is valid.
     */
    public static function check_token($userid, $token, $appid) {
        global $DB, $USER;
        // If there is a logged in user and he differs from userid, log him out.
        if ($USER->id > 0 && !isguestuser($USER) && $USER->id != $userid) {
            require_logout();
            global $PAGE;
            redirect($PAGE->url);
            die();
        }
        $entry = $DB->get_record('local_eduauth',
                    array(
                        'appid' => $appid,
                        'userid' => $userid,
                        'token' => $token,
                    )
                );
        if (!empty($entry->userid) && $entry->userid == $userid) {
            self::verified_userid($userid);
            $entry->used = time();
            $DB->update_record('local_eduauth', $entry);
            self::user_login();
            return true;
        }
        return false;
    }
    /**
     * Does the real user-login.
     */
    public static function user_login() {
        global $CFG, $DB, $USER;
        $userid = self::verified_userid();
        $user = $DB->get_record('user', array('id' => $userid));
        if (empty($user->id)) return;
        if (isguestuser($user)) return;
        if (empty($user->confirmed)) return;
        if ($USER->id != $userid) {
            complete_user_login($user);
        }
        return $user;
    }
    /**
     * Sets and gets the userid of a verified user.
     * @param $userid (optional) If given will set this as verified user.
     */
    public static function verified_userid($userid = '') {
        if (!empty($userid)) {
            self::$verifieduserid = $userid;
        }
        return empty(self::$verifieduserid) ? 0 : self::$verifieduserid;
    }
}
