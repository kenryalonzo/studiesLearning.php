<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
global $wpdb;

echo "--- POST TYPES ---\n";
print_r($wpdb->get_results("SELECT post_type, count(*) as count FROM {$wpdb->posts} GROUP BY post_type", ARRAY_A));

echo "\n--- lp_course META KEYS ---\n";
print_r($wpdb->get_results("SELECT meta_key, count(*) as count FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.post_type = 'lp_course' GROUP BY meta_key LIMIT 30", ARRAY_A));
