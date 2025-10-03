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
 * Integration Flows API for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer\api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/base_api.php');

/**
 * Integration Flows API
 * 
 * Handles all integration flows related API endpoints
 */
class integration_flows_api extends base_api {
    
    /**
     * Update schedule for an event
     * 
     * @param string $eventId Event ID
     * @param array $data Request data
     * @return array API response
     */
    public function update_event_schedule($eventId, $data) {
        global $DB, $USER;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:manage_integration_flows');
            
            // Validate input
            if (!isset($data['scheduleConfig']) || !is_array($data['scheduleConfig'])) {
                return $this->error_response('Invalid scheduleConfig value', 400);
            }
            
            // Validate schedule configuration
            $validation = $this->validate_schedule_config($data['scheduleConfig']);
            if ($validation !== true) {
                return $this->error_response($validation, 400);
            }
            
            // Get event record (check both outbox and inbox)
            $event = $DB->get_record('local_lmp_outbox', ['id' => $eventId]);
            $isOutbox = true;
            
            if (!$event) {
                $event = $DB->get_record('local_lmp_inbox', ['id' => $eventId]);
                $isOutbox = false;
            }
            
            if (!$event) {
                return $this->error_response('Event not found', 404);
            }
            
            // Update schedule configuration
            $event->scheduleconfig = json_encode($data['scheduleConfig']);
            $event->updatedby = $USER->email;
            $event->updatedat = time();
            $event->timemodified = time();
            
            $tableName = $isOutbox ? 'local_lmp_outbox' : 'local_lmp_inbox';
            $result = $DB->update_record($tableName, $event);
            
            if ($result) {
                return [
                    'id' => $event->id,
                    'eventId' => $event->eventid,
                    'eventName' => $event->eventname,
                    'scheduleConfig' => $data['scheduleConfig'],
                    'updatedBy' => $event->updatedby,
                    'updatedAt' => date('c', $event->updatedat),
                    'message' => 'Schedule configuration updated successfully'
                ];
            } else {
                return $this->error_response('Failed to update schedule configuration', 500);
            }
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to update event schedule', $e);
        }
    }
    
    /**
     * Get active consumer events only from product
     * 
     * @return array API response
     */
    public function get_active_consumer_events() {
        global $DB;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:view_integration_flows');
            
            // Get active consuming events from inbox table
            $events = $DB->get_records('local_lmp_inbox', [
                'eventconsumingenabled' => 1,
                'status' => 'received'
            ], 'timecreated DESC');
            
            $activeEvents = [];
            foreach ($events as $event) {
                $scheduleConfig = json_decode($event->scheduleconfig, true);
                
                $activeEvents[] = [
                    'id' => $event->id,
                    'eventId' => $event->eventid,
                    'eventName' => $event->eventname,
                    'description' => $event->description,
                    'status' => $event->status,
                    'scheduleConfig' => $scheduleConfig,
                    'fieldMappings' => json_decode($event->fieldmappings, true),
                    'createdBy' => $event->createdby,
                    'createdAt' => date('c', $event->createdat),
                    'lastProcessed' => $this->get_last_processed_time($event->id)
                ];
            }
            
            return [
                'activeEvents' => $activeEvents,
                'totalCount' => count($activeEvents),
                'lastUpdated' => date('c')
            ];
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to get active consumer events', $e);
        }
    }
    
    /**
     * Get integration flow status for an event
     * 
     * @param string $eventId Event ID
     * @return array API response
     */
    public function get_integration_flow_status($eventId) {
        global $DB;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:view_integration_flows');
            
            // Get event record (check both outbox and inbox)
            $event = $DB->get_record('local_lmp_outbox', ['id' => $eventId]);
            $isOutbox = true;
            
            if (!$event) {
                $event = $DB->get_record('local_lmp_inbox', ['id' => $eventId]);
                $isOutbox = false;
            }
            
            if (!$event) {
                return $this->error_response('Event not found', 404);
            }
            
            $scheduleConfig = json_decode($event->scheduleconfig, true);
            
            return [
                'id' => $event->id,
                'eventId' => $event->eventid,
                'eventName' => $event->eventname,
                'type' => $isOutbox ? 'publishing' : 'consuming',
                'status' => $event->status,
                'scheduleConfig' => $scheduleConfig,
                'isActive' => $this->is_event_active($event, $isOutbox),
                'lastProcessed' => $this->get_last_processed_time($event->id),
                'nextScheduled' => $this->get_next_scheduled_time($scheduleConfig),
                'retryCount' => (int) $event->retrycount,
                'errorMessage' => $event->errormessage
            ];
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to get integration flow status', $e);
        }
    }
    
    /**
     * Validate schedule configuration
     * 
     * @param array $config Schedule configuration
     * @return bool|string True if valid, error message if invalid
     */
    private function validate_schedule_config($config) {
        $requiredFields = ['enabled', 'frequency'];
        
        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                return "Missing required field in scheduleConfig: {$field}";
            }
        }
        
        if (!is_bool($config['enabled'])) {
            return "scheduleConfig.enabled must be a boolean";
        }
        
        if (!in_array($config['frequency'], ['immediate', 'hourly', 'daily', 'weekly', 'custom'])) {
            return "Invalid frequency value. Must be one of: immediate, hourly, daily, weekly, custom";
        }
        
        if ($config['frequency'] === 'custom' && (!isset($config['cronExpression']) || empty($config['cronExpression']))) {
            return "cronExpression is required when frequency is 'custom'";
        }
        
        return true;
    }
    
    /**
     * Check if event is active
     * 
     * @param object $event Event record
     * @param bool $isOutbox Whether it's an outbox event
     * @return bool
     */
    private function is_event_active($event, $isOutbox) {
        if ($isOutbox) {
            return (bool) $event->eventpublishingenabled;
        } else {
            return (bool) $event->eventconsumingenabled;
        }
    }
    
    /**
     * Get last processed time for an event
     * 
     * @param string $eventId Event ID
     * @return string|null ISO timestamp or null
     */
    private function get_last_processed_time($eventId) {
        global $DB;
        
        try {
            $log = $DB->get_record('local_lmp_audit_log', [
                'component' => 'integration_flow',
                'eventid' => $eventId
            ], 'timecreated DESC');
            
            return $log ? date('c', $log->timecreated) : null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get next scheduled time based on schedule configuration
     * 
     * @param array $scheduleConfig Schedule configuration
     * @return string|null ISO timestamp or null
     */
    private function get_next_scheduled_time($scheduleConfig) {
        if (!$scheduleConfig || !$scheduleConfig['enabled']) {
            return null;
        }
        
        $now = time();
        
        switch ($scheduleConfig['frequency']) {
            case 'immediate':
                return date('c', $now);
            case 'hourly':
                return date('c', $now + 3600);
            case 'daily':
                return date('c', $now + 86400);
            case 'weekly':
                return date('c', $now + 604800);
            case 'custom':
                // For custom cron expressions, you would need a cron parser
                // This is a simplified implementation
                return date('c', $now + 3600); // Default to 1 hour
            default:
                return null;
        }
    }
}
