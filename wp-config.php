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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wpuser' );

/** Database username */
define( 'DB_USER', 'wpUser' );

/** Database password */
define( 'DB_PASSWORD', '1234567890' );

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
define( 'AUTH_KEY',         'CQ`K8BN=F=!91AaOn5o~,k.nGR=4.h4|9w{*kkb,4st;5lWaVic?LS7dy@xw?MK6' );
define( 'SECURE_AUTH_KEY',  'J~*JVT}Ac^JP?x!-+65VKp@^ZWdQk[6l$iF{J;6t_)rIfk|5eiSWA<.#M(u&N& W' );
define( 'LOGGED_IN_KEY',    '8w|~M2<u3{KtFC;] yI4CcPp@EW[-ov-LFmle^j5yc.aL|}z Qo<CJ7JG,L(?BYD' );
define( 'NONCE_KEY',        '!~OBAQ4*NS5EByS<!X$y_kv Bg;g)pX{#Z{&FJ:/n8zRnes>tDq:%qzxrIKhwMU$' );
define( 'AUTH_SALT',        'uiw=]eI<zNCx?>rTQ~W$M6nTY=(PT<D>aP|ur/jE5xO7j!I^`E*$s;J Qo7/AFZP' );
define( 'SECURE_AUTH_SALT', '.c`,30:+PY[I0WnIW743OpM_=hj 3G7NEll*NBzEoq8eVjBJbyH)Sh7TMjq*G0[Q' );
define( 'LOGGED_IN_SALT',   'mB}EYZgiZQ{y^s;G2bH6HnB@zo0~1I2m6}|u,qTeW*.g#E%=!?sd:8#9aLbk6 @j' );
define( 'NONCE_SALT',       ':}p=I{DK?fywg1)1yJSR4]WX(X?_[)9<QMa5wR=3mHz]CA;,A0y|rFs8gscV&4mi' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
