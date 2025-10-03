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
 * Event observers for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    // Individual grade submission events
    array(
        'eventname'   => '\core\event\user_graded',
        'callback'    => 'local_lmp_data_layer_observer::user_graded',
    ),
    
    // Quiz question grading events
    array(
        'eventname'   => '\mod_quiz\event\attempt_submitted',
        'callback'    => 'local_lmp_data_layer_observer::quiz_attempt_submitted',
    ),
);
