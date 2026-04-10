<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
global $wpdb;
$query = "SELECT t.term_id as id, t.name, t.slug, tt.count 
          FROM {$wpdb->terms} t 
          INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
          WHERE tt.taxonomy = 'course_category' AND tt.count > 0 
          ORDER BY tt.count DESC LIMIT 15";
$results = $wpdb->get_results($query, ARRAY_A);
echo "Count: " . count($results) . "\n";
print_r($results);
