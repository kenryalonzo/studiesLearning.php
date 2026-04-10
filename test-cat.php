<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
$cats = studies_get_course_categories();
echo "Count: " . count($cats) . "\n";
print_r($cats);
