<?php
global $brutebank_plugin_version, $wpdb;

/**
 * Upgrade database if needed
 */
if (get_option('brutebank_db_version') != $brutebank_db_version) {
    // v1.1
    if (get_option('brutebank_db_version') < 1.1) {
        $table_name = $wpdb->prefix.'brutebank_settings';
        
        $sql = "ALTER TABLE $table_name ADD COLUMN xmlrpc tinyint(1) DEFAULT 0 NOT NULL AFTER enabled";
        $wpdb->query($sql);
        
        update_option('brutebank_db_version', '1.1');
    }
    // v1.2
    if (get_option('brutebank_db_version') < 1.2) {
        $table_name = $wpdb->prefix.'brutebank_settings';
        
        $sql = "ALTER TABLE $table_name ADD COLUMN two_factor tinyint(1) DEFAULT 0 NOT NULL AFTER xmlrpc";
        $wpdb->query($sql);
        
        update_option('brutebank_db_version', '1.2');
    }
    // v1.3
    if (get_option('brutebank_db_version') < 1.3) {
        $table_name = $wpdb->prefix.'brutebank_blocks';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip_address varchar(255) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        $wpdb->query($sql);
        
        $table_name = $wpdb->prefix.'brutebank_settings';
        $sql = "ALTER TABLE $table_name ADD COLUMN updated_at timestamp";
        $wpdb->query($sql);
        $sql = "ALTER TABLE $table_name ADD COLUMN cache_updated timestamp";
        $wpdb->query($sql);
        
        update_option('brutebank_db_version', '1.3');
    }
}
?>