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
 * This page handles the login for various apps.
 * After the login was done we expect the page to be closed and logout
 * the user automatically, so that a new call will start a new login-procedure.
 *
 * @package    local_eduauth
 * @copyright  2019 Zentrum für Lernmanagement (http://www.lernmanagement.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Allow Access of AJAX-Queries.
header('Access-Control-Allow-Origin: *');

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$token = required_param('token', PARAM_TEXT);
$appid = required_param('appid', PARAM_TEXT);
$act = optional_param('act', 'login', PARAM_TEXT);

$context = context_system::instance();

$PAGE->set_url('/local/eduauth/login.php?token=' . $edmtoken . '&appid=' . $appid . '&act=' . $act);
$PAGE->set_context($context);

switch ($act) {
    case 'getuser':
        $o = array();
        $chk = $DB->get_record('local_eduauth', array('token' => $edmtoken, 'appid' => $appid, 'redeemed' => 0));
        if (!empty($chk->userid)) {
            $user = $DB->get_record('user', array('id' => $chk->userid));
            // We can reveal the edmtoken.
            if (!empty($user->id)) {
                $chk->redeemed++;
                $context = context_user::instance($user->id);
                $DB->update_record('local_eduauth', $chk);
                $site = get_site();
                $o = array(
                    'appid' => $appid,
                    'token' => $token,
                    'sitename' => $site->fullname,
                    'userid' => $user->id,
                    'wwwroot' => $CFG->wwwroot,
                );
            } else {
                $o = array('error' => 'user_does_not_exist', 'userid' => $chk->userid);
            }
        } else {
            $o = array('error' => 'no_data_for_token');
        }
        die(json_encode($o, JSON_NUMERIC_CHECK));
    break;
    case 'login':
        if ($USER->id == 0 || isguestuser($USER)) {
            $SESSION->wantsurl = $PAGE->url;
            redirect(get_login_url());
            echo $OUTPUT->header();
            $params = array(
                'content' => get_string('login:required_login', 'local_eduauth'),
                'script' => 'location.href = "' . get_login_url() . '";',
                'type' => 'success',
                'url' => get_login_url(),
            );
            echo $OUTPUT->render_from_template('local_eduauth/alert', $params);
            echo $OUTPUT->footer();
        } else {
            $o = $DB->get_record('local_eduauth', array('userid' => $USER->id, 'token' => $token, 'appid' => $appid));
            if (empty($o->userid)) {
                $o = array('userid' => $USER->id, 'token' => $token, 'appid' => $appid, 'redeemed' => 0, 'created' => time());
                $DB->insert_record('local_eduauth', (object)$o);
            }
            echo $OUTPUT->header();
            $params = array(
                'content' => get_string('login:successful', 'local_eduauth'),
                'script' => 'window.top.close(); /* If we are in a webapp, close popup. */',
                'type' => 'success',
                'url' => 'javascript:window.top.close();',
            );
            echo $OUTPUT->render_from_template('local_eduauth/alert', $params);
            echo $OUTPUT->footer();
            $authsequence = get_enabled_auth_plugins(); // auths, in sequence
            foreach($authsequence as $authname) {
                $authplugin = get_auth_plugin($authname);
                $authplugin->logoutpage_hook();
            }
            require_logout();
        }
    break;
}
