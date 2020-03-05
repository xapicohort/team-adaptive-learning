<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// Delete Table
global $wpdb;

$table_name = $wpdb->prefix . 'social_stream_data';

$wpdb->query( "DROP TABLE IF EXISTS $table_name" );