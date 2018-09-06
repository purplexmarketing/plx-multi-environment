<?php
/**
 * Note: If you decided to edit this template file you should also edit:
 *
 * /wp-content/plugins/plx-multienv/plx-multienv.php
 *
 * Change the Version number in the comment block at the top to 999.9.9
 * This will disable updates to the plugin from Wordpress.org and ensure
 * your changes don't get overwritten.
 *
 */

global $wpdb;
$const = get_defined_constants();

$plx_multienv_template_default = <<<PLX
<?php
/**
 * This is a modified version of the base configurations of the WordPress.
 *
 * This file has the following configurations: Table Prefix, Secret Keys,
 * and WordPress Language. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can modify any of the settings below before
 * the PLX Multi-Environments section.
 *
 * If you re-run the installation routine for PLX Multi-Environments then you
 * should backup your custom settings below as this file gets overwritten as
 * part of the installation routine.
 *
 * For more information on PLX Multi-Environments please visit the project page
 * at http://plx.mk/multi-environments
 *
 * @package WordPress
 */

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', '{$const['DB_CHARSET']}');

/** The Database Collate type. Don\'t change this if in doubt. */
define('DB_COLLATE', '{$const['DB_COLLATE']}');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '{$const['AUTH_KEY']}');
define('SECURE_AUTH_KEY',  '{$const['SECURE_AUTH_KEY']}');
define('LOGGED_IN_KEY',    '{$const['LOGGED_IN_KEY']}');
define('NONCE_KEY',        '{$const['NONCE_KEY']}');
define('AUTH_SALT',        '{$const['AUTH_SALT']}');
define('SECURE_AUTH_SALT', '{$const['SECURE_AUTH_SALT']}');
define('LOGGED_IN_SALT',   '{$const['LOGGED_IN_SALT']}');
define('NONCE_SALT',       '{$const['NONCE_SALT']}');


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
\$table_prefix  = '$wpdb->base_prefix';

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/**
 * PLX Multi-Environments
 *
 * This plugin has modifed your wp-config.php file to use one of three environment
 * files to support multiple development environments. It's recommended not to edit
 * anything below this point and to use the Settings > Environments menu in the
 * WordPress admin screen to make any changes to your database configuration
 */

/** Lets the PLX Multi-Environments plugin know that environments have been installed **/
define('PLX_MULTIENV_FILES_INSTALLED', true);

/** Try environment variable 'WP_ENV' and Filter non-alphabetical characters for security **/
if (getenv('WP_ENV') !== false) {
  define('WP_ENV', preg_replace('/[^a-z]/', '', getenv('WP_ENV')));
}

/** Define site host **/
if (isset(\$_SERVER['HTTP_X_FORWARDED_HOST']) && !empty(\$_SERVER['HTTP_X_FORWARDED_HOST'])) {
  \$plx_multienv_hostname_current = \$_SERVER['HTTP_X_FORWARDED_HOST'];
} else {
  \$plx_multienv_hostname_current = \$_SERVER['HTTP_HOST'];
}

/** If WordPress has been bootstrapped via WP-CLI detect environment from --env=<environment> argument **/
if (PHP_SAPI == 'cli' && defined('WP_CLI_ROOT')) {
	foreach (\$argv as \$arg) {
  	if (preg_match('/--env=(.+)/', \$arg, \$m)) {
    	define('WP_ENV', \$m[1]);
		}
	}
	\$plx_multienv_hostname_current = 'localhost';
}

/** Filter **/
\$plx_multienv_hostname_current = filter_var(\$plx_multienv_hostname_current, FILTER_SANITIZE_STRING);

/** Are we in SSL mode? **/
if ((!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] != 'off') || (!empty(\$_SERVER['HTTP_X_FORWARDED_PROTO']) && \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    \$plx_multienv_protocol_current = 'https://';
} else {
    \$plx_multienv_protocol_current = 'http://';
}

/** Load the environments file **/
include_once(ABSPATH . 'wp-config.environments.php');

/** Check to see which environment we are currently in **/
switch (\$plx_multienv_protocol_current . \$plx_multienv_hostname_current) {
	case \$plx_multienv_hostname_dev:
		define('WP_ENV', 'development');
		break;

	case \$plx_multienv_hostname_stage:
		define('WP_ENV', 'staging');
		break;

	case \$plx_multienv_hostname_prod:
	default:
		define('WP_ENV', 'production');
}

/** Load database configuration for the current environment **/
include(ABSPATH . 'wp-config.' . WP_ENV . '.php');

/** Define WordPress Site URLs if not already set in config files **/
define('WP_SITEURL', \$plx_multienv_protocol_current . rtrim(\$plx_multienv_hostname_current, '/'));
define('WP_HOME', \$plx_multienv_protocol_current . rtrim(\$plx_multienv_hostname_current, '/'));

// Define W3 Total Cache hostname
if (defined('WP_CACHE')) {
    define('COOKIE_DOMAIN', \$plx_multienv_hostname_current);
}

/** Clean up **/
unset(\$plx_multienv_hostname_current, \$plx_multienv_protocol_current, \$plx_multienv_hostname_dev, \$plx_multienv_hostname_stage, \$plx_multienv_hostname_prod);

/** END of PLX Multi-Environments section **/

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
PLX;
