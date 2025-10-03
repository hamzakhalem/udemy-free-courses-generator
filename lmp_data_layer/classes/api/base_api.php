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
 * Base API class for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Base API class
 * 
 * Provides common functionality for all API classes
 */
abstract class base_api {
    
    /**
     * Require user to be logged in
     * 
     * @throws \moodle_exception
     */
    protected function require_login() {
        require_login();
    }
    
    /**
     * Require specific capability
     * 
     * @param string $capability Capability name
     * @throws \moodle_exception
     */
    protected function require_capability($capability) {
        require_capability($capability, \context_system::instance());
    }
    
    /**
     * Handle API errors
     * 
     * @param string $message Error message
     * @param \Exception $exception Exception object
     * @return array Error response
     */
    protected function handle_error($message, $exception) {
        debugging("LMP Data Layer API Error: {$message} - " . $exception->getMessage(), DEBUG_DEVELOPER);
        
        return [
            'error' => true,
            'message' => $message,
            'code' => 500,
            'details' => $exception->getMessage()
        ];
    }
    
    /**
     * Create error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @return array Error response
     */
    protected function error_response($message, $code = 400) {
        return [
            'error' => true,
            'message' => $message,
            'code' => $code
        ];
    }
    
    /**
     * Create success response
     * 
     * @param mixed $data Response data
     * @param int $code HTTP status code
     * @return array Success response
     */
    protected function success_response($data, $code = 200) {
        return [
            'success' => true,
            'data' => $data,
            'code' => $code
        ];
    }
    
    /**
     * Validate JSON input
     * 
     * @param string $json JSON string
     * @return array|null Decoded JSON or null if invalid
     */
    protected function validate_json($json) {
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Get tenant ID from request headers or default
     * 
     * @return string Tenant ID
     */
    protected function get_tenant_id() {
        $headers = getallheaders();
        return $headers['X-TENANT'] ?? 'default_tenant';
    }
    
    /**
     * Validate required fields
     * 
     * @param array $data Data to validate
     * @param array $requiredFields Required field names
     * @return bool|string True if valid, error message if invalid
     */
    protected function validate_required_fields($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return "Missing required field: {$field}";
            }
        }
        return true;
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    protected function sanitize_input($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize_input'], $data);
        } elseif (is_string($data)) {
            return clean_param($data, PARAM_TEXT);
        }
        return $data;
    }
}
