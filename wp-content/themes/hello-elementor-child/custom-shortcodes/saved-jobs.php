<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

$device = deviceType();


if ($device == 'mobile') {
    include 'saved-jobs-mobile.php';
} elseif ($device == 'tablet') {
    include 'saved-jobs-mobile.php';
} else {
    include 'saved-jobs-desktop.php';
}

?>