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

require_once($CFG->dirroot . '/local/lmp_data_layer/classes/event_publisher.php');

/**
 * Event observer for local_lmp_data_layer.
 */
class local_lmp_data_layer_observer {

    /**
     * Handle user graded event
     * 
     * @param \core\event\user_graded $event The user graded event
     * @return void
     */
    public static function user_graded(\core\event\user_graded $event) {
        try {
            // Build event data
            $eventData = self::build_user_graded_data($event);
            
            if ($eventData) {
                // Publish event
                \local_lmp_data_layer\event_publisher::publish_grade_submitted($eventData);
            }
            
        } catch (\Exception $e) {
            debugging("LMP Data Layer - Error processing user_graded event: " . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
    
    /**
     * Handle quiz attempt submitted event
     * 
     * @param \mod_quiz\event\attempt_submitted $event The quiz attempt event
     * @return void
     */
    public static function quiz_attempt_submitted(\mod_quiz\event\attempt_submitted $event) {
        try {
            // Build event data
            $eventData = self::build_quiz_attempt_data($event);
            
            if ($eventData) {
                // Publish event
                \local_lmp_data_layer\event_publisher::publish_quiz_grade_submitted($eventData);
            }
            
        } catch (\Exception $e) {
            debugging("LMP Data Layer - Error processing quiz_attempt_submitted event: " . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
    
    /**
     * Build user graded event data
     * 
     * @param \core\event\user_graded $event The user graded event
     * @return array|null Event data or null on failure
     */
    private static function build_user_graded_data(\core\event\user_graded $event) {
        global $DB;
        
        try {
            require_once($CFG->libdir . '/gradelib.php');
            require_once($CFG->dirroot . '/grade/lib.php');
            
            // Get grade information
            $grade = grade_grade::fetch(['id' => $event->objectid]);
            if (!$grade) {
                return null;
            }
            
            $gradeItem = grade_item::fetch(['id' => $grade->itemid]);
            if (!$gradeItem) {
                return null;
            }
            
            $userGrade = grade_get_course_grade($event->relateduserid, $event->courseid);
            if (!$userGrade) {
                return null;
            }
            
            // Get user and course information
            $teacher = $DB->get_record('user', ['id' => $event->userid]);
            $student = $DB->get_record('user', ['id' => $event->relateduserid]);
            $course = $DB->get_record('course', ['id' => $event->courseid]);
            
            if (!$teacher || !$student || !$course) {
                return null;
            }
            
            return [
                'eventid' => $teacher->id . $student->id . $course->id . time(),
                'teacher' => $teacher->username,
                'student' => $student->username,
                'idnumber' => $gradeItem->idnumber,
                'courseshortname' => $course->shortname,
                'grademax' => $gradeItem->grademax,
                'grademin' => $gradeItem->grademin,
                'grade' => $grade->finalgrade,
                'finalgrade' => floatval($userGrade->grade),
                'timestamp' => time()
            ];
            
        } catch (\Exception $e) {
            debugging("LMP Data Layer - Error building user graded data: " . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }
    
    /**
     * Build quiz attempt event data
     * 
     * @param \mod_quiz\event\attempt_submitted $event The quiz attempt event
     * @return array|null Event data or null on failure
     */
    private static function build_quiz_attempt_data(\mod_quiz\event\attempt_submitted $event) {
        global $DB;
        
        try {
            require_once($CFG->dirroot . '/mod/quiz/locallib.php');
            
            $cmid = $event->contextinstanceid;
            $userid = $event->userid;
            $quizid = $event->other['quizid'];
            
            // Get attempt information
            $attempt = $DB->get_record_sql(
                'SELECT * FROM {quiz_attempts} WHERE quiz = ? AND userid = ? ORDER BY id DESC LIMIT 1',
                array($quizid, $userid)
            );
            
            if (!$attempt) {
                return null;
            }
            
            // Get course module and user information
            $cm = $DB->get_record('course_modules', array('id' => $cmid));
            $user = $DB->get_record('user', array('id' => $userid));
            $course = $DB->get_record('course', array('id' => $event->courseid));
            
            if (!$cm || !$user || !$course) {
                return null;
            }
            
            // Get teacher information
            $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
            $context = \context_course::instance($event->courseid);
            $teachers = get_role_users($role->id, $context);
            $teacher = !empty($teachers) ? reset($teachers) : $DB->get_record('user', array('id' => 2));
            
            return [
                'eventid' => $teacher->id . $user->id . $course->id . time(),
                'teacher' => $teacher->username,
                'student' => $user->username,
                'courseshortname' => $course->shortname,
                'quizid' => $quizid,
                'attemptid' => $attempt->id,
                'grade' => $attempt->sumgrades ?? 0,
                'maxgrade' => $attempt->sumgrades ?? 0,
                'timestamp' => time()
            ];
            
        } catch (\Exception $e) {
            debugging("LMP Data Layer - Error building quiz attempt data: " . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }
}
