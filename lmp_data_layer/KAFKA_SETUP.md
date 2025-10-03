# Kafka Setup for LMP Data Layer

## Quick Start

### 1. Install Dependencies
```bash
cd moodle/plugins/local/lmp_data_layer
composer install
```

### 2. Start Test Kafka
```bash
# Start Kafka with Docker
docker-compose -f docker-compose.test.yml up -d

# Wait for Kafka to be ready (about 30 seconds)
sleep 30
```

### 3. Test Kafka Publishing
```bash
# Run test script
php test_kafka.php
```

### 4. Verify Events
- **Kafka UI**: http://localhost:8080
- **Test Consumer**: `docker logs lmp_test_consumer`

## Configuration

### Enable Kafka in Moodle
Add to your `config.php`:

```php
// Enable Kafka publishing
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'localhost:9092',
    'security_protocol' => 'PLAINTEXT',
    'sasl_mechanism' => 'PLAIN',
    'sasl_username' => '',
    'sasl_password' => '',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

### Production Configuration
For production, update the configuration with your actual Kafka cluster details:

```php
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'your-kafka-cluster:9092',
    'security_protocol' => 'SASL_SSL',
    'sasl_mechanism' => 'PLAIN',
    'sasl_username' => 'your-username',
    'sasl_password' => 'your-password',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

## Online Kafka Setup

### Cloud Provider Examples

#### Confluent Cloud
```php
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'pkc-xxxxx.us-west-2.aws.confluent.cloud:9092',
    'security_protocol' => 'SASL_SSL',
    'sasl_mechanism' => 'PLAIN',
    'sasl_username' => 'your-api-key',
    'sasl_password' => 'your-api-secret',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

#### AWS MSK (Amazon Managed Streaming for Apache Kafka)
```php
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'your-msk-cluster.xxxxx.kafka.us-west-2.amazonaws.com:9092',
    'security_protocol' => 'SASL_SSL',
    'sasl_mechanism' => 'AWS_MSK_IAM',
    'sasl_username' => '',
    'sasl_password' => '',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

#### Azure Event Hubs
```php
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'your-namespace.servicebus.windows.net:9093',
    'security_protocol' => 'SASL_SSL',
    'sasl_mechanism' => 'PLAIN',
    'sasl_username' => '$ConnectionString',
    'sasl_password' => 'Endpoint=sb://your-namespace.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=your-key',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

#### Google Cloud Pub/Sub (via Kafka API)
```php
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'your-kafka-cluster.gcp.region.gcp.cloud:9092',
    'security_protocol' => 'SASL_SSL',
    'sasl_mechanism' => 'PLAIN',
    'sasl_username' => 'your-service-account',
    'sasl_password' => 'your-service-account-key',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

### Self-Hosted Kafka with SSL
```php
$CFG->lmp_kafka_config = [
    'bootstrap_servers' => 'your-kafka-server:9093',
    'security_protocol' => 'SSL',
    'sasl_mechanism' => 'PLAIN',
    'sasl_username' => '',
    'sasl_password' => '',
    'topic_prefix' => 'lmp_',
    'enabled' => true
];
```

## Event Flow

1. **Moodle Event** → Observer captures grade event
2. **Outbox Storage** → Event stored in `local_lmp_outbox` table
3. **Kafka Publishing** → Event published to Kafka topic
4. **External Consumption** → Other products consume from Kafka

## Topics Created

- `lmp_grade_submitted` - Individual grade submissions
- `lmp_quiz_grade_submitted` - Quiz grade submissions

## CloudEvent Format

Events are published in CloudEvent format:

```json
{
  "specversion": "1.0",
  "type": "grade_submitted",
  "source": "/LMP/Moodle",
  "id": "event_uuid",
  "time": "2025-01-01T10:30:00Z",
  "datacontenttype": "application/json",
  "data": {
    "eventid": "1234567890",
    "teacher": "john.doe",
    "student": "jane.smith",
    "courseshortname": "MATH101",
    "grade": 85,
    "finalgrade": 85,
    "timestamp": 1735744245
  }
}
```

## Troubleshooting

### Kafka Connection Failed
- Check if Kafka is running: `docker ps`
- Check Kafka logs: `docker logs lmp_kafka`
- Verify port 9092 is available

### Dependencies Not Found
- Run `composer install` in plugin directory
- Check PHP extensions: `php -m | grep rdkafka`

### Events Not Publishing
- Check Moodle debug logs
- Verify `$CFG->lmp_kafka_config['enabled'] = true`
- Check Kafka UI for message flow

