<?php
// Load WordPress
require __DIR__ . '/wp-load.php';

// Optional: Log start
error_log("Job Alert Cron started at " . date('Y-m-d H:i:s'));

$args = array(
'post_type'      => 'job_alert',
'post_status'    => 'publish',
'posts_per_page' => -1,
);

$alerts = get_posts($args);

if (empty($alerts)) {
echo "No job_alert posts found.<br>";
exit;
}

foreach ($alerts as $alert) {
$post_id = $alert->ID;
$meta = get_post_meta($post_id);

echo "========================================<br>";
echo "Job Alert ID: {$post_id}<br>";
echo "----------------------------------------<br>";

// Parse alert_search_terms
$search_terms = maybe_unserialize($meta['alert_search_terms'][0] ?? '');

if (!empty($search_terms) && is_array($search_terms)) {
echo "Search Terms:<br>";

// Handle categories
if (!empty($search_terms['categories'])) {
echo "- Categories:<br>";
foreach ($search_terms['categories'] as $cat_id) {
$term = get_term($cat_id, 'job_listing_category');
if ($term && !is_wp_error($term)) {
echo "   => {$term->name} (ID: {$cat_id})<br>";
} else {
echo "    Invalid category ID: {$cat_id}<br>";
}
}
}

// Handle regions

/*if (!empty($search_terms['regions'])) {
echo "- Regions:<br>";
foreach ($search_terms['regions'] as $region_id) {
$term = get_term($region_id, 'job_listing_region');
if ($term && !is_wp_error($term)) {
echo "   => {$term->name} (ID: {$region_id})<br>";
}
}
}*/

// Handle types

/*if (!empty($search_terms['types'])) {
echo "- Types:<br>";
foreach ($search_terms['types'] as $type_id) {
$term = get_term($type_id, 'job_listing_type');
if ($term && !is_wp_error($term)) {
echo "   => {$term->name} (ID: {$type_id})<br>";
}
}
}*/

echo "<br>";
}

//  Other alert info
echo "Email: " . ($meta['alert_email'][0] ?? 'N/A') . "<br>";
echo "Keyword: " . ($meta['alert_keyword'][0] ?? 'N/A') . "<br>";
echo "Location: " . ($meta['alert_location'][0] ?? 'N/A') . "<br>";
echo "Frequency: " . ($meta['alert_frequency'][0] ?? 'N/A') . "<br>";

echo "========================================<br><br>";
}

error_log("Job Alert Cron finished at " . date('Y-m-d H:i:s'));
