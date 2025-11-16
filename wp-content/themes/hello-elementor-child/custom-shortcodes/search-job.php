<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function deviceType() {
    $tablet_browser = 0;
    $mobile_browser = 0;

    // Tablet detection
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        return 'tablet';
    }

    // Mobile detection
    if (preg_match('/(mobi|android|iphone|ipod|blackberry|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        return 'mobile';
    }

    return 'desktop';
}

$device = deviceType();


if ($device == 'mobile') {
    include 'search-job-mobile.php';
} elseif ($device == 'tablet') {
    include 'search-job-mobile.php';
} else {
    include 'search-job-desktop.php';
}

?>