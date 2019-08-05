<?php
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
define( 'DB_NAME', 'katiesnutrition' );

/** MySQL database username */
define( 'DB_USER', 'votanlean' );

/** MySQL database password */
define( 'DB_PASSWORD', 'votanlean' );

/** MySQL hostname */
define( 'DB_HOST', 'votanlean.cuvco21ek71w.ap-southeast-1.rds.amazonaws.com' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('FS_METHOD','direct');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'K3eS@y?m:~(0(0#!;4xhF#e_t2c5|+-@Rw@(mnOPrg6!1gXzVP&9AaizOwVm0L:1' );
define( 'SECURE_AUTH_KEY',  'Ws#$Mv*u3LW[ck4~pG9~c=ItD Z~fdC]tjN0CPjL@{{kGSs2uxe2aDV2<<{gUTZo' );
define( 'LOGGED_IN_KEY',    'S28Nb[59e?G:^so!o %N#d0hU1ehh&Qh7Mgkx0dtUD,Hm.[ug81w)/VC.LP=C9E{' );
define( 'NONCE_KEY',        '$Cl#>!Y,B1k$SN.mr;_Z;*{4=HgQ_<+Y?w3}xnAC`MDF=/Zu<3E]`]$RO!-,_NAc' );
define( 'AUTH_SALT',        '$vivR%V%N8.Zh{GOd&nf$4HsdRVaz2jc%4p2beFIq;m58@8Kn-O*iwpK/l)|MwEH' );
define( 'SECURE_AUTH_SALT', 'X3(Rgzom}%^i}VNC`|xqi M3#MD>{ AwE/}Xz_7MYqqSN!YaRN!L[PHofY;Crtr*' );
define( 'LOGGED_IN_SALT',   'Ti,>5:fJ07T;GCCg6ONa6BxH!.+6;I8(/a%Jwh_5dEYl%05W[L!Z-{rYgqrsYC~8' );
define( 'NONCE_SALT',       'i]jfR5o$Hj4keI(mPo)xH+6f??k,z~4j64jFFkR82B,u>bRM25~y.;KF&Z :G71*' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

