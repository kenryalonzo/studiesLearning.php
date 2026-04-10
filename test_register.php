<?php
require '/srv/http/wordpress/wp-load.php';
echo "Registered:\n";
register_post_type('lp_course', ['public' => true, 'label' => 'Courses']);
register_taxonomy('course_category', 'lp_course', ['public' => true, 'label' => 'Course Categories']);
$cats = get_terms(['taxonomy' => 'course_category', 'hide_empty' => false]);
print_r(count($cats));
