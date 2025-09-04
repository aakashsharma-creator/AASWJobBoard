<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'aaswjobboarddb' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'a{:kiTrL`-]R$CoKJc!{5?nODAS3Ah%BHhCbipvX^V?W<B#!NBfMG!d++$s8YSH7' );
define( 'SECURE_AUTH_KEY',  ']w4EsL{Z}Y@DqY</A%2sY-rUwIt4{16/Z5%EenHH^y=}gkzcSEyAtR,JBauyK:S-' );
define( 'LOGGED_IN_KEY',    'v3p8=55cxf4AY92o+u0H1>XoZDVKXl2_P%4Y!<@[x!0I|i*3h6pzo>g!ajX^M3/b' );
define( 'NONCE_KEY',        'q$s,b<=Dc/[k!F)vq{ZFHDRpg/eQz9Oi8vVXt<lj#1+Ro.)jKXumxQB}[h%Jq@K(' );
define( 'AUTH_SALT',        '}>v#e>eh2V$M$RgS1]UM$5z<pb/G|pu)khfM?F3fczdozcN}cMW`RKn!Vj#e`<k$' );
define( 'SECURE_AUTH_SALT', 'w!==zzFrU!l?ma~b, fq#=#|0YuZ-y>:nVozI,)?&];p82o{EzTclfVnF1q^ ,)a' );
define( 'LOGGED_IN_SALT',   'Q?BZb77y0f{Ig:lihq!/Qd{eOXZT)<y^JQO%o?]PtPZ}-Y=~7G(w,W3e>onzk4 o' );
define( 'NONCE_SALT',       'D)mf>fYL9e5p;e*IEb x76b210*XL~q^.JF5*>pk$FXo|*NuJtC}Z!z>&lYN5;IH' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

//define('FORCE_SSL_ADMIN', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
