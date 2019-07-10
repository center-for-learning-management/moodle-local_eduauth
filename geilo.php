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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/eduauth/lib.php');

/**
 * This is GEILO - General Exchange Interface for Little Outputs. ;-)
 *
 * @package    local_eduauth
 * @copyright  2019 Zentrum für Lernmanagement (http://www.lernmanagement.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_eduauth_geilo {
    public static function act($data) {
        global $CFG, $DB, $USER;
        require_login();
        $reply = (object) array();
        if (empty($data->act)) {
            return $reply;
        }
        switch ($data->act) {
            case 'myData':
                // Returns personal data about the user to the app.
                $context = context_user::instance($USER->id);
                $reply->user = array(
                    'email' => $USER->email,
                    'firstname' => $USER->firstname,
                    'lastname' => $USER->lastname,
                    'pictureurl' => $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/user/icon',
                    'userid' => $USER->id,
                    'username' => $USER->username,
                );
            break;
            case 'removeMe':
                // Removes the token(s) of that user from database.
                $params = array('userid' => $USER->id);
                if (!empty($data->onlyappid)) {
                    $params['appid'] = $data->onlyappid;
                }
                if (!empty($data->onlytoken)) {
                    $params['token'] = $data->onlytoken;
                }
                $DB->delete_records('local_eduauth', array('userid' => $USER->id, 'token' => optional_param('token', '', PARAM_TEXT)));
                $reply->status = 'ok';
            break;
            case 'wstoken':
                // Create a moodle mobile webservice token on behalf of that user.
                $serviceshortname = 'moodle_mobile_app';
                if (!empty($USER->confirmed)) {
                    require_once($CFG->dirroot . '/lib/externallib.php');
                    $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
                    $token = external_generate_token_for_current_user($service);
                    $reply->wstoken = $token->token;
                }
            break;
        }
        return $reply;
    }
}
