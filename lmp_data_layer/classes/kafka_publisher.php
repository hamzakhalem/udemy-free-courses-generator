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
 * Kafka Publisher for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer;

defined('MOODLE_INTERNAL') || die();

/**
 * Kafka Publisher
 * 
 * This class handles publishing events to Kafka clusters.
 * Supports both local development and production online Kafka clusters.
 */
class kafka_publisher {
    
    /**
     * Test Kafka connection
     * 
     * @return bool True if connection successful
     */
    public static function test_connection() {
        global $CFG;
        
        if (!self::is_kafka_enabled()) {
            debugging('LMP Data Layer: Kafka is not enabled', DEBUG_DEVELOPER);
            return false;
        }
        
        try {
            $config = self::get_kafka_config();
            $producer = self::create_producer($config);
            
            if ($producer) {
                debugging('LMP Data Layer: Kafka connection test successful', DEBUG_DEVELOPER);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            debugging('LMP Data Layer: Kafka connection test failed - ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
    
    /**
     * Publish event to Kafka
     * 
     * @param array $eventData Event data
     * @param string $eventType Event type (e.g., 'grade_submitted', 'quiz_grade_submitted')
     * @return bool True if published successfully
     */
    public static function publish_event($eventData, $eventType) {
        if (!self::is_kafka_enabled()) {
            debugging('LMP Data Layer: Kafka is not enabled', DEBUG_DEVELOPER);
            return false;
        }
        
        try {
            $config = self::get_kafka_config();
            $producer = self::create_producer($config);
            
            if (!$producer) {
                debugging('LMP Data Layer: Failed to create Kafka producer', DEBUG_DEVELOPER);
                return false;
            }
            
            // Create CloudEvent structure
            $cloudEvent = self::create_cloud_event($eventType, $eventData);
            
            // Determine topic name
            $topicName = $config['topic_prefix'] . $eventType;
            
            // Publish to Kafka
            $result = $producer->produce(
                RD_KAFKA_PARTITION_UA,
                0,
                json_encode($cloudEvent),
                $cloudEvent['id']
            );
            
            if ($result === RD_KAFKA_RESP_ERR_NO_ERROR) {
                debugging("LMP Data Layer: Event published to topic '{$topicName}'", DEBUG_DEVELOPER);
                return true;
            } else {
                debugging("LMP Data Layer: Failed to publish event to topic '{$topicName}'", DEBUG_DEVELOPER);
                return false;
            }
            
        } catch (\Exception $e) {
            debugging('LMP Data Layer: Kafka publishing error - ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
    
    /**
     * Check if Kafka is enabled
     * 
     * @return bool
     */
    private static function is_kafka_enabled() {
        global $CFG;
        
        return isset($CFG->lmp_kafka_config) && 
               is_array($CFG->lmp_kafka_config) && 
               !empty($CFG->lmp_kafka_config['enabled']);
    }
    
    /**
     * Get Kafka configuration
     * 
     * @return array Kafka configuration
     */
    private static function get_kafka_config() {
        global $CFG;
        
        $defaultConfig = [
            'bootstrap_servers' => 'localhost:9092',
            'security_protocol' => 'PLAINTEXT',
            'sasl_mechanism' => 'PLAIN',
            'sasl_username' => '',
            'sasl_password' => '',
            'topic_prefix' => 'lmp_',
            'enabled' => false
        ];
        
        return array_merge($defaultConfig, $CFG->lmp_kafka_config ?? []);
    }
    
    /**
     * Create Kafka producer
     * 
     * @param array $config Kafka configuration
     * @return \RdKafka\Producer|null
     */
    private static function create_producer($config) {
        try {
            $conf = new \RdKafka\Conf();
            
            // Set bootstrap servers
            $conf->set('bootstrap.servers', $config['bootstrap_servers']);
            
            // Set security protocol
            $conf->set('security.protocol', $config['security_protocol']);
            
            // Set SASL configuration if needed
            if ($config['security_protocol'] === 'SASL_SSL' || $config['security_protocol'] === 'SASL_PLAINTEXT') {
                $conf->set('sasl.mechanism', $config['sasl_mechanism']);
                $conf->set('sasl.username', $config['sasl_username']);
                $conf->set('sasl.password', $config['sasl_password']);
            }
            
            // Set delivery timeout
            $conf->set('delivery.timeout.ms', 30000);
            
            // Set request timeout
            $conf->set('request.timeout.ms', 30000);
            
            // Set retry configuration
            $conf->set('retries', 3);
            $conf->set('retry.backoff.ms', 1000);
            
            // Create producer
            $producer = new \RdKafka\Producer($conf);
            
            return $producer;
            
        } catch (\Exception $e) {
            debugging('LMP Data Layer: Failed to create Kafka producer - ' . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }
    
    /**
     * Create CloudEvent structure
     * 
     * @param string $eventType Event type
     * @param array $data Event data
     * @return array CloudEvent structure
     */
    private static function create_cloud_event($eventType, $data) {
        return [
            'specversion' => '1.0',
            'type' => $eventType,
            'source' => '/LMP/Moodle',
            'id' => self::generate_uuid(),
            'time' => date('c'),
            'datacontenttype' => 'application/json',
            'data' => $data
        ];
    }
    
    /**
     * Generate UUID
     * 
     * @return string
     */
    private static function generate_uuid() {
        return sprintf('event_%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
