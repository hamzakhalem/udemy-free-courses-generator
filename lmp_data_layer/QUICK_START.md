# LMP Data Layer Plugin - Quick Start Guide

## 🚀 Quick Setup (5 minutes)

### 1. Install the Plugin
```bash
# Copy plugin to Moodle
cp -r lmp_data_layer /path/to/moodle/local/plugins/

# Run database upgrade
php /path/to/moodle/admin/cli/upgrade.php
```

### 2. Verify Installation
Visit: `https://your-moodle-site.com/local/lmp_data_layer/setup.php`

### 3. Test the API
Visit: `https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events`

You should see a JSON response with events.

## 🧪 Quick Testing

### Option 1: Browser Testing
1. Log into Moodle as admin
2. Visit: `https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events`
3. You should see JSON response

### Option 2: Automated Testing
```bash
# Run API tests
php /path/to/moodle/local/lmp_data_layer/test_api.php

# Run event tests
php /path/to/moodle/local/lmp_data_layer/test_events.php
```

### Option 3: HTTP Testing
```bash
# Run HTTP tests
php /path/to/moodle/local/lmp_data_layer/test_http.php
```

## 📋 API Endpoints Quick Reference

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/v1/publisher/publishing-events` | GET | Get publishing events |
| `/v1/publisher/publishing-events/{id}` | PATCH | Toggle event status |
| `/v1/publisher/product-control` | GET/PATCH | Product control |
| `/v1/consumer/consuming-events` | GET | Get consuming events |
| `/v1/consumer/consuming-events/{id}` | PATCH | Toggle consuming event |
| `/v1/consumer/consuming-events/{id}/field-mappings` | PUT | Update field mappings |
| `/v1/integration/flows/active-events` | GET | Get active events |
| `/v1/integration/flows/{id}/schedule` | PATCH | Update schedule |

## 🔧 Configuration

### Global Settings
- **Product Control**: Master toggle for all publishing
- **Event Publishing**: Individual event control
- **Field Mappings**: Custom field mappings for consuming events
- **Schedule Configuration**: Event scheduling options

### Capabilities
- `local/lmp_data_layer:view_*` - View permissions
- `local/lmp_data_layer:manage_*` - Management permissions
- `local/lmp_data_layer:access_api` - API access

## 📊 Monitoring

### Check Events
```sql
-- View publishing events
SELECT * FROM local_lmp_outbox ORDER BY timecreated DESC LIMIT 10;

-- View consuming events  
SELECT * FROM local_lmp_inbox ORDER BY timecreated DESC LIMIT 10;

-- View audit logs
SELECT * FROM local_lmp_audit_log ORDER BY timecreated DESC LIMIT 10;
```

### Check API Status
```bash
# Test API health
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events" \
  -b "MoodleSession=your_session_cookie"
```

## 🐛 Troubleshooting

### Common Issues

1. **API returns HTML instead of JSON**
   - Check if user is logged in
   - Verify session cookies
   - Check file permissions

2. **Database errors**
   - Run: `php admin/cli/upgrade.php`
   - Check database permissions
   - Verify table creation

3. **Authentication errors**
   - Ensure user has required capabilities
   - Check role assignments
   - Verify session validity

### Debug Mode
```php
// In config.php
$CFG->debug = DEBUG_DEVELOPER;
$CFG->debugdisplay = 1;
```

## 📚 Documentation

- **[README.md](README.md)** - Plugin overview
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Complete API docs
- **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - Testing instructions
- **[setup.php](setup.php)** - Interactive setup

## 🎯 Next Steps

1. **Test the APIs** using the endpoints above
2. **Create real events** by grading assignments
3. **Configure production settings**
4. **Set up monitoring and alerts**
5. **Integrate with external systems**

## 🆘 Support

If you encounter issues:
1. Check the [TESTING_GUIDE.md](TESTING_GUIDE.md)
2. Run the automated tests
3. Check Moodle logs
4. Verify database tables
5. Test with different users/roles

---

**Happy Testing! 🎉**
