=== PLX Multi-Environments ===
Contributors: mattstone-plx
Tags: developer,environment,dev,staging,production,live,plx,database,multiple
Requires at least: 3.5
Tested up to: 4.9
Requires PHP: 5.6
Stable tag: 1.0.1
License: MIT
License URI: https://opensource.org/licenses/MIT

Manage separate Development, Staging, and Production environments directly from the Wordpress Admin screen.

== Description ==
PLX Multi-Environments manages separate Development, Staging, and Production environments directly from within the Wordpress Admin screen.

Once the separate configuration files have been installed and your existing wp-config.php settings have been backed up
you\'re then free to enter each of your environments database settings. When you push your files between servers you no
longer need to edit the configuration.

Important: Although the plugin will automatically backup your current settings to wp-config.backup.php, we strongly recommend backing up
your wp-config.php file before completing the plugin installation.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/plx-multienv` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the \'Plugins\' screen in WordPress.
3. After successful activation go to the Settings->Environments screen or click on the Install button that appears at the top of the Admin screen.
4. Follow the on-screen instructions to install the environment files.
5. After a successful installation you can enter your Development, Staging, and Production credentials on each tab and then Save Changes.
6. Copy all of the wp-config.*.php files in the root of your Wordpress site to your other environments.

== Frequently Asked Questions ==
= I only use a development and production environment, can I still use this plugin? =

Yes, the plugin loads the relevant environment based on the current hostname so if you don\'t wish to use all the environments just leave those sections blank.

= How can I add custom code to my database configuration without the plugin overwriting it? =

There are several ways you can do this:
* Add your code directly in the wp-config.php file, this file is created during the initial installation of the environments and can be safely edited without affecting the plugin, these settings apply globally to all environments.
* Un-install the plugin and directly edit each environment file (`wp-config.development.php`, `wp-config.staging.php`, `wp-config.production.php`). Once the environment files have been created the plugin is only used to edit the files and is not needed for the environments to function correctly.
* Modify the template files found in `/wp-content/plugins/plx-multienv/templates`, you will also need to edit the version number in `/wp-content/plugins/plx-multienv/plx-multienv.php` to 999.9.9, this will prevent updates to the plugin that would otherwise overwrite your changes. Please note this method is not recommended as it disables future updates that may fix security vulnerabilities and provide compatibility with future versions of WordPress.

== Screenshots ==
1. Initial setup of the environment files
2. Quickly and easily edit your environments

== Changelog ==
= 1.0.0 =
* Initial release

= 1.0.1 =
* Tested for compatibility with WordPress 4.9
* Fixed CSS layout issues with tabs in admin screen
