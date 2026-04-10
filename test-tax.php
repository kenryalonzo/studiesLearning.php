<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
global $wpdb;
$ts = $wpdb->get_results("SELECT taxonomy, count(*) as count FROM {$wpdb->term_taxonomy} GROUP BY taxonomy");
print_r($ts);
