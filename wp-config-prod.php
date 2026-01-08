<?php
/**
 * The base configuration for WordPress
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'arsenal' );

/** Database username */
define( 'DB_USER', 'arsenal_usr' );

/** Database password */
define( 'DB_PASSWORD', 'jV:<Mn2E_&RPZckF' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', 'utf8mb4_unicode_ci' );

/** MySQL charset and collation for proper Cyrillic support */
define( 'DB_CLIENT_CHARSET', 'utf8mb4' );
define( 'DB_CHARSET_COLLATE', 'utf8mb4_unicode_ci' );

/** WordPress Address (URL) */
define( 'WP_SITEURL', 'http://1779917-cq85026.twc1.net' );

/** Site Address (URL) */
define( 'WP_HOME', 'http://1779917-cq85026.twc1.net' );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         'B8Y!xPm@#LkNq$7RwX%9Zc&2sFd(4)GhJvKlMnOp5QrStUvWxYz' );
define( 'SECURE_AUTH_KEY',  'A1b2C3d4E5f6G7h8I9j0K1l2M3n4O5p6Q7r8S9t0U1v2W3x4Y5' );
define( 'LOGGED_IN_KEY',    'K9j8H7g6F5e4D3c2B1a0Z9y8X7w6V5u4T3s2R1q0P9o8N7m6' );
define( 'NONCE_KEY',        'L5m6N7o8P9q0R1s2T3u4V5w6X7y8Z9a0B1c2D3e4F5g6H7i8' );
define( 'AUTH_SALT',        'M4n5O6p7Q8r9S0t1U2v3W4x5Y6z7A8b9C0d1E2f3G4h5I6j7' );
define( 'SECURE_AUTH_SALT', 'N3o4P5q6R7s8T9u0V1w2X3y4Z5a6B7c8D9e0F1g2H3i4J5k6' );
define( 'LOGGED_IN_SALT',   'O2p3Q4r5S6t7U8v9W0x1Y2z3A4b5C6d7E8f9G0h1I2j3K4l5' );
define( 'NONCE_SALT',       'P1q2R3s4T5u6V7w8X9y0Z1a2B3c4D5e6F7g8H9i0J1k2L3m4' );

/**#@-*/

/**
 * WordPress database table prefix.
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
