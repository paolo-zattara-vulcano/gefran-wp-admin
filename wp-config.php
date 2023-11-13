<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ===================================================
// LOCAL CONFIG
// ===================================================
if (file_exists(dirname(__FILE__) . '/local-config.php')) {

    define('WP_LOCAL_DEV', true);
    include(dirname(__FILE__) . '/local-config.php');

} else {

    // ===================================================
    // PRODUCTION CONFIG
    // ===================================================

    define('WP_LOCAL_DEV', false);

    // DISABLE AUTOUPDATE
    define('AUTOMATIC_UPDATER_DISABLED', true);
    define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);

    // DISABLE FILE EDIT
    define( 'DISALLOW_FILE_EDIT', true );
    define( 'DISALLOW_FILE_MODS', true );



    // ** Database settings - You can get this info from your web host ** //
    /** The name of the database for WordPress */
    define('DB_NAME', 'gefran');

    /** Database username */
    define('DB_USER', 'gefran');

    /** Database password */
    define('DB_PASSWORD', '3Z5VW83wIX62Da6');

    /** Database hostname */
    define('DB_HOST', 'localhost');

    /** Database charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8');

    /** The database collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');

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
    define('AUTH_KEY', 'tEH)1WkXRQ*:CA6YS)<^f8PL1y&b<>#j1+oLMRxFJ52{`nBx-g:;;[e|SJ,,3z,k');
    define('SECURE_AUTH_KEY', 'vFphm>=scc<T[ia1I0K%[y9uFf$?oqJ WKfl~5bR*Q(]2.!^HA3Lu@O&)sM|VlE~');
    define('LOGGED_IN_KEY', ']k`jBf8DmWGdK+)G5kRl kg*F^N~[lM(V;Zs@3q0Q_2;{Fy5v)PpDOu$%%]`@CA#');
    define('NONCE_KEY', '%FB{S9$083=U9UPp-Buq%h0[: /)y;_k:ez:zY8Rv$*^#J4%I{sSoJHFZh3pC##_');
    define('AUTH_SALT', '@EW~fMWs#=Y{TIhYJY2@_fsp[8({5:x~AmRk}-my5kOX44Ssj}>n{clkX2r>]+A|');
    define('SECURE_AUTH_SALT', 'jP2[J7!ND2U1zj4Cq%WszqHXTY{I6P^0w,LdLbn55CyhJDbWp>QRTC}!v?;P4b-y');
    define('LOGGED_IN_SALT', 'SHJTNY_$V/nj<FOVD1=bG:hQg|ebgnwOZTaF72W)4e<e{PwNASU%j#04@}!k+;Ry');
    define('NONCE_SALT', 'jj<S6pd>Y{kyNtW2HGD9g02e/Ux+HXnO..|E7}iL<5od~oU3Erc?XK_2@@A5KFL#');
    define('WP_CACHE_KEY_SALT', 'm}cXHx]7b`-i_!&C$az[AL&$)EWNRHK2>5a.=KrysgeVkVM%r!OB;vT=NU.`Nx:T');


    /**#@-*/

    /**
     * WordPress database table prefix.
     *
     * You can have multiple installations in one database if you give each
     * a unique prefix. Only numbers, letters, and underscores please!
     */
    $table_prefix = 'wp_';
    if (! defined('WP_DEBUG')) {
        define('WP_DEBUG', false);
    } // line added by the MyKinsta

    /**
     * For developers: WordPress debugging mode.
     *
     * Change this to true to enable the display of notices during development.
     * in their development environments.
     *
     * For information on other constants that can be used for debugging,
     * visit the documentation.
     *
     * @link https://wordpress.org/support/article/debugging-in-wordpress/
     */


    define('WP_MEMORY_LIMIT', '512M');


    define('WP_ALLOW_MULTISITE', true);
    define('MULTISITE', true);
    define('SUBDOMAIN_INSTALL', false);
    $base = '/';
    define('DOMAIN_CURRENT_SITE', 'gefran.kinsta.cloud');
    define('PATH_CURRENT_SITE', '/');
    define('SITE_ID_CURRENT_SITE', 1);
    define('BLOG_ID_CURRENT_SITE', 1);

    /* That's all, stop editing! Happy publishing. */

    /** Absolute path to the WordPress directory. */
    if (! defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }

    /** Sets up WordPress vars and included files. */
    require_once ABSPATH . 'wp-settings.php';


}
