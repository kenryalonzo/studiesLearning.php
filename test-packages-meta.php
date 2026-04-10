<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
global $wpdb;

echo "--- META KEYS WITH PACKAGE ---\n";
print_r($wpdb->get_results("SELECT meta_key, count(*) as count FROM {$wpdb->postmeta} WHERE meta_key LIKE '%package%' GROUP BY meta_key", ARRAY_A));

echo "\n--- TERMS WITH PACKAGE ---\n";
print_r($wpdb->get_results("SELECT t.name, t.slug, tt.taxonomy FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE t.name LIKE '%package%' OR t.slug LIKE '%package%'", ARRAY_A));
