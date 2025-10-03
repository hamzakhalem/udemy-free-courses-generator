<?php
/**
 * Test Kafka Publishing for LMP Data Layer
 *
 * This script tests the Kafka publishing functionality.
 * Run this from Moodle root: php plugins/local/lmp_data_layer/test_kafka.php
 */

// Moodle bootstrap
require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/classes/kafka_publisher.php');

use local_lmp_data_layer\kafka_publisher;

echo "=== LMP Data Layer Kafka Test ===\n\n";

// Test 1: Check Kafka connection
echo "1. Testing Kafka connection...\n";
$connectionTest = kafka_publisher::test_connection();
if ($connectionTest) {
    echo "   ✅ Kafka connection successful\n\n";
} else {
    echo "   ❌ Kafka connection failed\n";
    echo "   Make sure Kafka is running: docker-compose -f plugins/local/lmp_data_layer/docker-compose.test.yml up -d\n\n";
    exit(1);
}

// Test 2: Publish test grade event
echo "2. Publishing test grade event...\n";
$testEventData = [
    'eventid' => 'test_' . time(),
    'teacher' => 'test.teacher',
    'student' => 'test.student',
    'courseshortname' => 'TEST101',
    'grade' => 85,
    'finalgrade' => 85,
    'timestamp' => time()
];

$publishResult = kafka_publisher::publish_event($testEventData, 'grade_submitted');
if ($publishResult) {
    echo "   ✅ Test event published successfully\n\n";
} else {
    echo "   ❌ Failed to publish test event\n\n";
}

// Test 3: Publish test quiz event
echo "3. Publishing test quiz event...\n";
$testQuizData = [
    'eventid' => 'test_quiz_' . time(),
    'teacher' => 'test.teacher',
    'student' => 'test.student',
    'courseshortname' => 'TEST101',
    'quizid' => 123,
    'attemptid' => 456,
    'grade' => 90,
    'maxgrade' => 100,
    'timestamp' => time()
];

$quizPublishResult = kafka_publisher::publish_event($testQuizData, 'quiz_grade_submitted');
if ($quizPublishResult) {
    echo "   ✅ Test quiz event published successfully\n\n";
} else {
    echo "   ❌ Failed to publish test quiz event\n\n";
}

echo "=== Test Complete ===\n";
echo "Check Kafka UI at: http://localhost:8080\n";
echo "Check test consumer logs: docker logs lmp_test_consumer\n";


