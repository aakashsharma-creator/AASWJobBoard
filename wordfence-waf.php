<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

// This file was the current value of auto_prepend_file during the Wordfence WAF installation (Wed, 12 Nov 2025 06:03:43 +0000)
if (file_exists('/usr/local/share/auto_prepends/auto_prepends.php')) {
	include_once '/usr/local/share/auto_prepends/auto_prepends.php';
}
if (file_exists(__DIR__.'/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", __DIR__.'/wp-content/wflogs/');
	include_once __DIR__.'/wp-content/plugins/wordfence/waf/bootstrap.php';
}