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
 * This page handles the data exchange between local_eduauth and apps.
 *
 * @package    local_eduauth
 * @copyright  2019 Zentrum für Lernmanagement (http://www.lernmanagement.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("access-control-allow-origin: *");

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/eduauth/lib.php');
require_once($CFG->dirroot . '/local/eduauth/geilo.php');

$userid = optional_param('userid', 0, PARAM_INT);
$token = optional_param('token', '', PARAM_TEXT);
$appid = optional_param('appid', '', PARAM_TEXT);
$callforward = optional_param('callforward', '', PARAM_TEXT);

$context = context_system::instance();

$PAGE->set_url('/local/eduauth/connect.php?token=' . $token . '&appid=' . $appid . '&callforward=' . $callforward . '&userid=' . $userid);
$PAGE->set_context($context);
$strdata = optional_param('data', '', PARAM_RAW);

if (local_eduauth_lib::check_token($userid, $token, $appid)) {
    $data = json_decode($strdata);
    if (!empty($callforward)) {
        $reply = (object) array();
        $p = explode('_', $callforward);
        if (file_exists($CFG->dirroot . '/' . $p[0] . '/' . $p[1] . '/eduauth.php')) {
            require_once($CFG->dirroot . '/' . $p[0] . '/' . $p[1] . '/eduauth.php');
            $cname = $p[0] . '_' . $p[1] . '_eduauth';
            $cname::callforward($data, $reply);
        } else {
            $reply->error = 'invalid plugin specified';
        }
    } else {
        $reply = local_eduauth_geilo::act($data);
    }
} else {
    $reply = (object) array(
        'appid' => $appid,
        'data' => $strdata,
        'error' => 'invalid_login',
        'status' => 'error',
        'token' => $token,
        'userid' => $userid,
    );
}

echo json_encode($reply, JSON_NUMERIC_CHECK);
