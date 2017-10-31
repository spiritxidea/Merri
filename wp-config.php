<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/** Enable W3 Total Cache */
 //Added by WP-Cache Manager

 
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_merri');
/** MySQL database username */
define('DB_USER', 'wp_merri');
/** MySQL database password */
define('DB_PASSWORD', '4b2c1b6d');
/** MySQL hostname */
define('DB_HOST', 'ap-cdbr-azure-southeast-b.cloudapp.net:3306');
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'bSso<.}A5_<5oA>im}nL@(0}g[A#USPVO+HjkrQZHu1d*!FQwAph!)Ah$}qC1lH;');
define('SECURE_AUTH_KEY',  '>;zP(q{-m~hCZutDIx,vT01o9Xd!)gb8F>oyvzU&K8+ow$vVOYE~0-5w&a{gl #b');
define('LOGGED_IN_KEY',    'e<xybEfD|wvqa2u;*fG QDcCf`Pu+4O)V>H  /az2L~B~f6cgg(r1b>^e[@grCJI');
define('NONCE_KEY',        '*V>S AwB)!|`<Xp:IBt_ek17~kv,<|^<&Y3NR!KtSAbzsgO6e0nN50v})44L2v4>');
define('AUTH_SALT',        '!nmC%YWUDZhQH9OJZj1oY0{2M?e=S7,pe=0.V6fR.ein|5UM*Qf)hKaa*n4L,|%:');
define('SECURE_AUTH_SALT', '~1!scCsD*c3zUZyK2L@515h~Vw0g|3<tG]:8Yr_./KdZ#>+@ofH$,/{dVSM-=sO(');
define('LOGGED_IN_SALT',   '1r,hHt8F[9|C[Ai#Tt#@`lE^&6<OxG$CZ}1X1s^X42+;`A3[e_nPKJl=W~^0=l=Z');
define('NONCE_SALT',       't<@obS{aJ)<eDoQKpHkq33INvtT47]$!r2#+WPk],y*wN~YfCLXB7%46(*aDi9r,');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');