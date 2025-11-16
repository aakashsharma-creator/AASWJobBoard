<?php
# Database Configuration
define( 'DB_NAME', 'wp_aaswjobstaging' );
define( 'DB_USER', 'aaswjobstaging' );
define( 'DB_PASSWORD', 'NJkzj3eeqoCC-RqQ20nA' );
define( 'DB_HOST', '127.0.0.1:3306' );
define( 'DB_HOST_SLAVE', '127.0.0.1:3306' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         'Aln~jxr0WNgn1^w)2z4,ueJp^pIi(htS4._#y4JZpjw-SEANcxNeQ)rg3ir?kE%F');
define('SECURE_AUTH_KEY',  'bLgy7#OPN1i5@ZJ,pF0UYEBc9GEg)X$WjcNSph,JzqdaDZ1,$eBVQKb)NwWKL~+*');
define('LOGGED_IN_KEY',    'Ukn+QMf$M7$44?Xr4H#NPYR^+V2CNn=(uGJ9qYX!$2_q1w_^7Cuip^86)XxONuK)');
define('NONCE_KEY',        'lMyC1_.Z3H4e2=mQ,k6Um+?%3LHmj&$yN_(qQ4dG!b^PtA8!7VMj^(E=*^++~R*.');
define('AUTH_SALT',        'm*l$u5yOIWQZl-%Q(On8=S?-#1-dI?(&ydPh!)OLnsmUOCS9P^?cu0Ic9dwQDm&o');
define('SECURE_AUTH_SALT', 'lH!w81d93Y8#r=h+1_$M-,KnBTOl*TB,k!hF#Y3%UC2&iPcx-MGd%3DQ9ZV3o9m.');
define('LOGGED_IN_SALT',   '&NvNrdkq-IY5bspmWk,#IcVP4ypqLvfx?4hb=_M16tYsm*7z$_QI%)_$(0mTeAz.');
define('NONCE_SALT',       'FdaKUrp3-7Uw~U~%p)4yry4dPw*7E!_4HL6zzhySwtw^9.3Pq$rIUZ_)jqO#R2,k');


# Localized Language Stuff
define( 'WP_DEBUG', value: false ); 

define( 'WP_CACHE', TRUE );

define( 'WP_AUTO_UPDATE_CORE', false );

define( 'PWP_NAME', 'aaswjobstaging' );

define( 'FS_METHOD', 'direct' );

define( 'FS_CHMOD_DIR', 0775 );

define( 'FS_CHMOD_FILE', 0664 );

define( 'WPE_APIKEY', 'ae07bd4167e9056d276d267ce238cd2b4c45b2d8' );

define( 'WPE_CLUSTER_ID', '227023' );

define( 'WPE_CLUSTER_TYPE', 'pod' );

define( 'WPE_ISP', true );

define( 'WPE_BPOD', false );

define( 'WPE_RO_FILESYSTEM', false );

define( 'WPE_LARGEFS_BUCKET', 'largefs.wpengine' );

define( 'WPE_SFTP_PORT', 2222 );

define( 'WPE_SFTP_ENDPOINT', '35.201.12.221' );

define( 'WPE_LBMASTER_IP', '' );

define( 'WPE_CDN_DISABLE_ALLOWED', true );

define( 'DISALLOW_FILE_MODS', FALSE );

define( 'DISALLOW_FILE_EDIT', FALSE );

define( 'DISABLE_WP_CRON', false );

define( 'WPE_FORCE_SSL_LOGIN', false );

define( 'FORCE_SSL_LOGIN', false );

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

define( 'WPE_EXTERNAL_URL', false );

define( 'WP_POST_REVISIONS', FALSE );

define( 'WPE_WHITELABEL', 'wpengine' );

define( 'WP_TURN_OFF_ADMIN_BAR', false );

define( 'WPE_BETA_TESTER', false );

umask(0002);

$wpe_cdn_uris=array ( );

$wpe_no_cdn_uris=array ( );

$wpe_content_regexs=array ( );

$wpe_all_domains=array ( 0 => 'aaswjobstaging.wpengine.com', 1 => 'aaswjobstaging.wpenginepowered.com', );

$wpe_varnish_servers=array ( 0 => '127.0.0.1', );

$wpe_special_ips=array ( 0 => '35.244.83.213', 1 => 'pod-227023-utility.pod-227023.svc.cluster.local', );

$wpe_netdna_domains=array ( );

$wpe_netdna_domains_secure=array ( );

$wpe_netdna_push_domains=array ( );

$wpe_domain_mappings=array ( );

$memcached_servers=array ( 'default' =>  array ( 0 => 'unix:///tmp/memcached.sock', ), );
define('WPLANG','');

# WP Engine ID

define( 'WP_HOME', 'https://aaswjobstaging.wpenginepowered.com' );
define( 'WP_SITEURL', 'https://aaswjobstaging.wpenginepowered.com' );


# WP Engine Settings

@ini_set( 'upload_max_filesize' , '512M' );
@ini_set( 'post_max_size', '512M');
@ini_set( 'memory_limit', '1024M' );
@ini_set( 'max_execution_time', '5000' );
@ini_set( 'max_input_time', '5000' );




# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', __DIR__ . '/');
require_once(ABSPATH . 'wp-settings.php');

