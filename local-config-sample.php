<?php
/*
This is a sample local-config.php file
In it, you *must* include the four main database defines

You may include other settings here that you only want enabled on your local development checkouts
*/


// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'gefran-admin' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'tEH)1WkXRQ*:CA6YS)<^f8PL1y&b<>#j1+oLMRxFJ52{`nBx-g:;;[e|SJ,,3z,k' );
define( 'SECURE_AUTH_KEY',   'vFphm>=scc<T[ia1I0K%[y9uFf$?oqJ WKfl~5bR*Q(]2.!^HA3Lu@O&)sM|VlE~' );
define( 'LOGGED_IN_KEY',     ']k`jBf8DmWGdK+)G5kRl kg*F^N~[lM(V;Zs@3q0Q_2;{Fy5v)PpDOu$%%]`@CA#' );
define( 'NONCE_KEY',         '%FB{S9$083=U9UPp-Buq%h0[: /)y;_k:ez:zY8Rv$*^#J4%I{sSoJHFZh3pC##_' );
define( 'AUTH_SALT',         '@EW~fMWs#=Y{TIhYJY2@_fsp[8({5:x~AmRk}-my5kOX44Ssj}>n{clkX2r>]+A|' );
define( 'SECURE_AUTH_SALT',  'jP2[J7!ND2U1zj4Cq%WszqHXTY{I6P^0w,LdLbn55CyhJDbWp>QRTC}!v?;P4b-y' );
define( 'LOGGED_IN_SALT',    'SHJTNY_$V/nj<FOVD1=bG:hQg|ebgnwOZTaF72W)4e<e{PwNASU%j#04@}!k+;Ry' );
define( 'NONCE_SALT',        'jj<S6pd>Y{kyNtW2HGD9g02e/Ux+HXnO..|E7}iL<5od~oU3Erc?XK_2@@A5KFL#' );
define( 'WP_CACHE_KEY_SALT', 'm}cXHx]7b`-i_!&C$az[AL&$)EWNRHK2>5a.=KrysgeVkVM%r!OB;vT=NU.`Nx:T' );


// TABLE PREFIX
$table_prefix = 'wp_';


// MEMORY LIMITS
define( 'WP_MEMORY_LIMIT', '1000M' );
set_time_limit( 300 );


// WPDEBUG INFO
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false);
define("WP_DEBUG_LOG", true);
// define("GRAPHQL_DEBUG", true);
// define( 'SCRIPT_DEBUG', true );


// MULTISITE CONFIGS
define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
// define( 'DOMAIN_CURRENT_SITE', 'gefran-admin.dvl' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
