<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
global $wpdb;

echo "--- TITLES WITH PACKAGE ---\n";
$titles = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'lp_course' AND post_title LIKE '%package%'", ARRAY_A);
print_r($titles);
if (empty($titles)) {
    echo "No courses have 'package' in their title.\n";
}
