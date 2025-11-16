<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

$device = deviceType();


if ($device == 'mobile') {
    include 'search-locum-mobile.php';
} elseif ($device == 'tablet') {
    include 'search-locum-mobile.php';
} else {
    include 'search-locum-desktop.php';
}

?>