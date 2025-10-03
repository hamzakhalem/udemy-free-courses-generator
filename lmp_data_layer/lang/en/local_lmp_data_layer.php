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
 * Language strings for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'LMP Data Layer';
$string['privacy:metadata'] = 'The LMP Data Layer plugin does not store any personal data.';

// Event names
$string['event_bulk_grade_submitted'] = 'Bulk Grade Submitted';
$string['event_grade_reviewed'] = 'Grade Reviewed';

// Event descriptions
$string['event_bulk_grade_submitted_desc'] = 'When teacher submits multiple grades at once';
$string['event_grade_reviewed_desc'] = 'When teacher reviews/updates a single grade';

// Error messages
$string['error_invalidcourseorassignment'] = 'Invalid course or assignment';
$string['error_invalidgrade'] = 'Invalid grade data';
$string['error_eventpublishing'] = 'Event publishing failed';

// Event types
$string['event_user_graded'] = 'Individual Grade Submitted';
$string['event_quiz_attempt_submitted'] = 'Quiz Question Grades Submitted';
$string['event_grade_submitted'] = 'Grade Submitted';
$string['event_quiz_grade_submitted'] = 'Quiz Grades Submitted';