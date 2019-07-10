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
 * @package    local_edumessenger
 * @copyright  2018 Digital Education Society (http://www.dibig.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eduauth\privacy;
use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die;

class provider implements \core_privacy\local\metadata\provider {

    public static function get_metadata(collection $collection) : collection {
        // Here you will add more items into the collection.
        $collection->add_database_table(
            'local_eduauth',
            [
                'userid' => 'privacy:metadata:eduauth:userid',
                'token' => 'privacy:metadata:eduauth:token',
            ],
            'privacy:metadata:eduauth'
        );
        return $collection;
    }
}
