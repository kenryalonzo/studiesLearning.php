<?php
require_once dirname( __FILE__ ) . '/../../../wp-load.php';
$terms = wp_get_post_terms(32958, 'course_category');
print_r($terms);
$meta = get_post_meta(32958);
echo "\n--- META ---\n";
print_r(array_keys($meta));
