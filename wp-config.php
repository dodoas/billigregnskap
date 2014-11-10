<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'dodonydi_billigr');

/** MySQL database username */
define('DB_USER', 'dodonydi_billigr');

/** MySQL database password */
define('DB_PASSWORD', 'P9v1iSta47');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'nsyw3sjtlsfqesywlcdzmkq7jsu6wglyanurhh9zf28pvi64jmipbviylutge2jn');
define('SECURE_AUTH_KEY',  'iov2gsozx8kyonmvkkvb8olys2bzp0mvnauauxqxsm5ha55ggt4nyizvotockqmr');
define('LOGGED_IN_KEY',    'kimworsni2yb41aarnf41nzuvc6n27exqvcyfckauputt1zzflcf54fktrnzukyi');
define('NONCE_KEY',        'yhvh3fabglhxmrrcxecvburlxbnwofqgjks23eegyxvx1rjisbf07zblcqgmrscd');
define('AUTH_SALT',        '6zkqwplrzlhfsaxkixwptm0k3vwdfe4p0oy6si570anrdlfuqzbw4bg6ufcbs4zo');
define('SECURE_AUTH_SALT', 'g9ecp8g5lrhj5jut2egrj40lpy90i9d1ckjv8b8nh0riszsgq0irtwiwp08dlcin');
define('LOGGED_IN_SALT',   'xebc2mykocayv2ihxntf8bxd6jjcolhavvg5dudtks2e9vc5mjcagb0eovqtemxh');
define('NONCE_SALT',       'tin3zemrypwbkqpazuxuy5qza7lmkd8djue3ezh9dirmteof85fyvugxxrnsxkgr');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define ('WPLANG', 'en_US');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
