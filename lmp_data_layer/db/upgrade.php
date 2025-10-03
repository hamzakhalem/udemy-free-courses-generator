<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for local_lmp_data_layer
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_lmp_data_layer_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // === تغيير الحقول id من char(36) إلى int auto-increment ===
    if ($oldversion < 2025100201) {

        $tables = ['local_lmp_outbox', 'local_lmp_inbox'];

        foreach ($tables as $tablename) {
            $table = new xmldb_table($tablename);
            $field = new xmldb_field('id');

            if ($dbman->field_exists($table, $field)) {
                try {
                    // إعادة تعريف الحقل الجديد
                    $newfield = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
                    var_dump("inside");
                    // نحاول تغيير النوع
                    $dbman->change_field_type($table, $newfield);
                    $dbman->change_field_sequence($table, $newfield);

                } catch (Exception $e) {
                    // ⚠️ لو فشل التحويل (مثلاً UUIDs قديمة)
                    debugging("Upgrade notice: failed to convert field 'id' in {$tablename}, error: " . $e->getMessage(), DEBUG_DEVELOPER);

                    // 👇 خيار 1: إعادة إنشاء الجدول (يمسح البيانات)
                    // $dbman->drop_table($table);
                    // $dbman->create_table($table);

                    // 👇 خيار 2: تخلي الحقل كما هو وتعمل ملاحظة
                    // debugging("Field 'id' in {$tablename} left as CHAR(36). Manual migration needed.", DEBUG_NORMAL);
                }
            }
        }

        // Upgrade savepoint.
        upgrade_plugin_savepoint(true, 2025100201, 'local', 'lmp_data_layer');
    }

    return true;
}
