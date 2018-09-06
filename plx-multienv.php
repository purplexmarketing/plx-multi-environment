<?php
/**
* Plugin Name: PLX Multi-Environments
* Description: Manage separate Development, Staging, and Production environments directly from the Wordpress Admin screen. Based on https://github.com/studio24/wordpress-multi-env-config by Studio 24 Ltd.
* Version: 1.0.1
* Author: Purplex
* Author URI: http://plx.mk
* License: MIT
*/

/*

												 TM
████████╗██╗     ███╗   ███╗
██╔═══██║██║      ███╗ ███╔╝
████████║██║       ██████╔╝
██╔═════╝██║      ███╔╝███╗
██║      ███████╗███╔╝  ███╗
╚═╝      ╚══════╝╚══╝   ╚══╝
    POWER YOUR WORDPRESS
       http://plx.mk

*/

// Set the path to this plugin
define( 'PLX_MULTIENV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLX_MULTIENV_PLUGIN_BASENAME', plugin_basename(__FILE__) );

function plx_multienv_admin_scripts() {
  $html = '
  <script type="text/javascript">
  	function changeTab(tabId) {
			jQuery(".tab-content").hide();
			jQuery("#plx-multienv-tab-" + tabId).show();
			jQuery(".nav-tab").removeClass("nav-tab-active");
			jQuery("#plx-multienv-nav-" + tabId).addClass("nav-tab-active");
		}
  	jQuery(document).ready(function() {
			jQuery("#plx_multienv_install_environment").on("change", function() {
				if (jQuery(this).val() == "development") {
					jQuery("#plx_multienv_config_dev_url").val("' . plx_multienv_current_siteurl() . '");
					jQuery("#plx_multienv_config_dev_url").prop("disabled", true);
					jQuery("#plx_multienv_config_stage_url").val("");
					jQuery("#plx_multienv_config_stage_url").prop("disabled", false);
					jQuery("#plx_multienv_config_prod_url").val("");
					jQuery("#plx_multienv_config_prod_url").prop("disabled", false);
				} else if (jQuery(this).val() == "staging") {
					jQuery("#plx_multienv_config_dev_url").val("");
					jQuery("#plx_multienv_config_dev_url").prop("disabled", false);
					jQuery("#plx_multienv_config_stage_url").val("' . plx_multienv_current_siteurl() . '");
					jQuery("#plx_multienv_config_stage_url").prop("disabled", true);
					jQuery("#plx_multienv_config_prod_url").val("");
					jQuery("#plx_multienv_config_prod_url").prop("disabled", false);
				} else if (jQuery(this).val() == "production") {
					jQuery("#plx_multienv_config_dev_url").val("");
					jQuery("#plx_multienv_config_dev_url").prop("disabled", false);
					jQuery("#plx_multienv_config_stage_url").val("");
					jQuery("#plx_multienv_config_stage_url").prop("disabled", false);
					jQuery("#plx_multienv_config_prod_url").val("' . plx_multienv_current_siteurl() . '");
					jQuery("#plx_multienv_config_prod_url").prop("disabled", true);
				}
			});
  	});
  </script>
  ';

  echo $html;
}
add_action('admin_footer', 'plx_multienv_admin_scripts');

// Create plugin options menu item
function plx_multienv_plugin_environments_menu() {
	add_submenu_page( 'options-general.php', 'PLX Multi-Environments Settings', 'Environments', 'manage_options', 'plx-multienv-menu-environments', 'plx_multienv_environments_page' );
}
add_action('admin_menu', 'plx_multienv_plugin_environments_menu');

function plx_multienv_plugin_page_link( $plx_multienv_settings_links) {
  $plx_multienv_settings_link = '<a href="options-general.php?page=plx-multienv-menu-environments">Settings</a>';
  array_unshift( $plx_multienv_settings_links, $plx_multienv_settings_link);
  return $plx_multienv_settings_links;
}

add_filter('plugin_action_links_' . PLX_MULTIENV_PLUGIN_BASENAME, 'plx_multienv_plugin_page_link');

// Check if the environments have been installed
if ( !(defined('PLX_MULTIENV_FILES_INSTALLED')) && !($_GET['page'] == 'plx-multienv-menu-environments') ) {

// Show plugin setup pending notice
function plx_multienv_setup_pending() {
?>
  <div class="notice notice-warning">
		<p><?php _e( '<strong>PLX Multi-Environments</strong> has been activated but your environments have not yet been installed. <a class="install-now button" href="/wp-admin/options-general.php?page=plx-multienv-menu-environments" aria-label="Install PLX Multi-Environments files now" data-name="PLX Multi-Environments files">Install Now</a>' ); ?></p>
	</div>
<?php
}
add_action( 'admin_notices', 'plx_multienv_setup_pending' );

} //END Check if the environments have been installed

// Generate current environment site url
function plx_multienv_current_siteurl() {

	// Define current hostname
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
	  $plx_multienv_hostname_current = $_SERVER['HTTP_X_FORWARDED_HOST'];
	} else {
	  $plx_multienv_hostname_current = $_SERVER['HTTP_HOST'];
	}

	// Filter and sanitize hostname
	$plx_multienv_hostname_current = filter_var($plx_multienv_hostname_current, FILTER_SANITIZE_STRING);

	// Define current protocol
	if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
	    $plx_multienv_protocol_current = 'https://';
	} else {
	    $plx_multienv_protocol_current = 'http://';
	}

	return $plx_multienv_protocol_current . rtrim($plx_multienv_hostname_current, '/');

}

// Create plugin options page
function plx_multienv_environments_page() {

	// Check if the installer routine has been initiated
	if (isset($_POST['plx_multienv_install_environment'])) {

		// Check if this is the development environment installation
		if ($_POST['plx_multienv_install_environment'] == 'development') {

			// Define development environment variables
			$plx_multienv_config_dev_siteurl = plx_multienv_current_siteurl();
			$plx_multienv_config_dev_dbname = DB_NAME;
			$plx_multienv_config_dev_dbuser = DB_USER;
			$plx_multienv_config_dev_dbpass = DB_PASSWORD;
			$plx_multienv_config_dev_dbhost = DB_HOST;
			$plx_multienv_config_dev_wpdebug = 'true';

			// Define staging environment variables
			$plx_multienv_config_stage_siteurl = $_POST['plx_multienv_config_stage_url'];
			$plx_multienv_config_stage_dbname = '';
			$plx_multienv_config_stage_dbuser = '';
			$plx_multienv_config_stage_dbpass = '';
			$plx_multienv_config_stage_dbhost = '';
			$plx_multienv_config_stage_wpdebug = 'false';

			// Define production environment variables
			$plx_multienv_config_prod_siteurl = $_POST['plx_multienv_config_prod_url'];
			$plx_multienv_config_prod_dbname = '';
			$plx_multienv_config_prod_dbuser = '';
			$plx_multienv_config_prod_dbpass = '';
			$plx_multienv_config_prod_dbhost = '';
			$plx_multienv_config_prod_wpdebug = 'false';

		// Check if this is the staging environment installation
		} else if ($_POST['plx_multienv_install_environment'] == 'staging') {

			// Define development environment variables
			$plx_multienv_config_dev_siteurl = $_POST['plx_multienv_config_dev_url'];
			$plx_multienv_config_dev_dbname = '';
			$plx_multienv_config_dev_dbuser = '';
			$plx_multienv_config_dev_dbpass = '';
			$plx_multienv_config_dev_dbhost = '';
			$plx_multienv_config_dev_wpdebug = 'true';

			// Define staging environment variables
			$plx_multienv_config_stage_siteurl = plx_multienv_current_siteurl();
			$plx_multienv_config_stage_dbname = DB_NAME;
			$plx_multienv_config_stage_dbuser = DB_USER;
			$plx_multienv_config_stage_dbpass = DB_PASSWORD;
			$plx_multienv_config_stage_dbhost = DB_HOST;
			$plx_multienv_config_stage_wpdebug = 'true';

			// Define production environment variables
			$plx_multienv_config_prod_siteurl = $_POST['plx_multienv_config_prod_url'];
			$plx_multienv_config_prod_dbname = '';
			$plx_multienv_config_prod_dbuser = '';
			$plx_multienv_config_prod_dbpass = '';
			$plx_multienv_config_prod_dbhost = '';
			$plx_multienv_config_prod_wpdebug = 'false';

		// Check if this is the production environment installation
		} else if ($_POST['plx_multienv_install_environment'] == 'production') {

			// Define development environment variables
			$plx_multienv_config_dev_siteurl = $_POST['plx_multienv_config_dev_url'];
			$plx_multienv_config_dev_dbname = '';
			$plx_multienv_config_dev_dbuser = '';
			$plx_multienv_config_dev_dbpass = '';
			$plx_multienv_config_dev_dbhost = '';
			$plx_multienv_config_dev_wpdebug = 'true';

			// Define staging environment variables
			$plx_multienv_config_stage_siteurl = $_POST['plx_multienv_config_stage_url'];
			$plx_multienv_config_stage_dbname = '';
			$plx_multienv_config_stage_dbuser = '';
			$plx_multienv_config_stage_dbpass = '';
			$plx_multienv_config_stage_dbhost = '';
			$plx_multienv_config_stage_wpdebug = 'false';

			// Define production environment variables
			$plx_multienv_config_prod_siteurl = plx_multienv_current_siteurl();
			$plx_multienv_config_prod_dbname = DB_NAME;
			$plx_multienv_config_prod_dbuser = DB_USER;
			$plx_multienv_config_prod_dbpass = DB_PASSWORD;
			$plx_multienv_config_prod_dbhost = DB_HOST;
			$plx_multienv_config_prod_wpdebug = 'false';

		}

		// Load template for wp-config.environments.php
		include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.environments.php");

		// Create new wp-config.environments.php file
		if (file_put_contents(ABSPATH . "wp-config.environments.php", $plx_multienv_template_envs) === false) {

			// Check if error message was already set
			if (isset($plx_multienv_error)) {

				// Set error message variable
				$plx_multienv_error = "";

			}

			// Set error message
			$plx_multienv_error .= "<code>wp-config.environments.php</code> ";

			//set php contents for manual entry
			$plx_multienv_manual_envs = $plx_multienv_template_envs;

		}

		// Load template for wp-config.development.php
		include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.development.php");

		// Create new wp-config.development.php file
		if (file_put_contents(ABSPATH . "wp-config.development.php", $plx_multienv_template_dev) === false) {

			// Check if error message was already set
			if (isset($plx_multienv_error)) {

				// Set error message variable
				$plx_multienv_error = "";

			}

			// Set error message
			$plx_multienv_error .= "<code>wp-config.development.php</code> ";

			//set php contents for manual entry
			$plx_multienv_manual_dev = $plx_multienv_template_dev;

		}

		// Load template for wp-config.staging.php
		include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.staging.php");

		// Create new wp-config.staging.php file
		if (file_put_contents(ABSPATH . "wp-config.staging.php", $plx_multienv_template_stage) === false) {

			// Check if error message was already set
			if (!(isset($plx_multienv_error))) {

				// Set error message variable
				$plx_multienv_error = "";

			}

			// Set error message
			$plx_multienv_error .= "<code>wp-config.staging.php</code> ";

			//set php contents for manual entry
			$plx_multienv_manual_stage = $plx_multienv_template_stage;

		}

		// Load template for wp-config.production.php
		include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.production.php");

		// Create new wp-config.production.php file
		if (file_put_contents(ABSPATH . "wp-config.production.php", $plx_multienv_template_prod) === false) {

			// Check if error message was already set
			if (!(isset($plx_multienv_error))) {

				// Set error message variable
				$plx_multienv_error = "";

			}

			// Set error message
			$plx_multienv_error .= "<code>wp-config.production.php</code> ";

			//set php contents for manual entry
			$plx_multienv_manual_prod = $plx_multienv_template_prod;

		}

		// Rename the existing wp-config.php file
		if ( rename(ABSPATH . "wp-config.php", ABSPATH . "wp-config.backup.php") ) {

			// Load template for wp-config.php
			include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.php");

			// Create new wp-config.php file
			if (!(file_put_contents(ABSPATH . "wp-config.php", $plx_multienv_template_default) === false)) {

				// Set success message variable
				$plx_multienv_success = "<strong>Success!</strong> Environment files were successfully installed";

			} else {

				// Check if error message was already set
				if (!(isset($plx_multienv_error))) {

					// Set error message variable
					$plx_multienv_error = "";

				}

				// Set error message
				$plx_multienv_error .= "<code>wp-config.php</code> ";

				//set php contents for manual entry
				$plx_multienv_manual_default = $plx_multienv_template_default;

			}

		} else {

			// Set error message
			$plx_multienv_error .= "<code>wp-config.backup.php</code> ";

		}

		// Check if there was an error installing files
		if (isset($plx_multienv_error)) {

			// Set error message variable
			$plx_multienv_error = "<strong>Error!</strong> Could not create the following files: " . $plx_multienv_error;

		} else {

			/** Lets the PLX Multi-Environments plugin know that environments have been installed **/
			define('PLX_MULTIENV_FILES_INSTALLED', true);

			/** Set the WP_ENV constant to tell the script which environment we are in **/
			define('WP_ENV', $_POST['plx_multienv_install_environment']);

			// Set success message variable
			$plx_multienv_success = "<strong>Success!</strong> Environment setup was completed. You can now enter your environment settings";

		}

	}

	// Check if the environments have been installed
	if ( defined('PLX_MULTIENV_FILES_INSTALLED') ) {

		// Check if the form was submitted
		if ( isset($_POST['plx_multienv_noncename']) && wp_verify_nonce( $_POST['plx_multienv_noncename'], PLX_MULTIENV_PLUGIN_BASENAME) ) {

			// Define development environment variables
			$plx_multienv_config_dev_siteurl = $_POST['plx_multienv_config_dev_url'];
			$plx_multienv_config_dev_dbname = $_POST['plx_multienv_config_dev_dbname'];
			$plx_multienv_config_dev_dbuser = $_POST['plx_multienv_config_dev_dbuser'];
			$plx_multienv_config_dev_dbpass = $_POST['plx_multienv_config_dev_dbpass'];
			$plx_multienv_config_dev_dbhost = $_POST['plx_multienv_config_dev_dbhost'];
			$plx_multienv_config_dev_wpdebug = $_POST['plx_multienv_config_dev_wpdebug'];

			// Define staging environment variables
			$plx_multienv_config_stage_siteurl = $_POST['plx_multienv_config_stage_url'];
			$plx_multienv_config_stage_dbname = $_POST['plx_multienv_config_stage_dbname'];
			$plx_multienv_config_stage_dbuser = $_POST['plx_multienv_config_stage_dbuser'];
			$plx_multienv_config_stage_dbpass = $_POST['plx_multienv_config_stage_dbpass'];
			$plx_multienv_config_stage_dbhost = $_POST['plx_multienv_config_stage_dbhost'];
			$plx_multienv_config_stage_wpdebug = $_POST['plx_multienv_config_stage_wpdebug'];

			// Define production environment variables
			$plx_multienv_config_prod_siteurl = $_POST['plx_multienv_config_prod_url'];
			$plx_multienv_config_prod_dbname = $_POST['plx_multienv_config_prod_dbname'];
			$plx_multienv_config_prod_dbuser = $_POST['plx_multienv_config_prod_dbuser'];
			$plx_multienv_config_prod_dbpass = $_POST['plx_multienv_config_prod_dbpass'];
			$plx_multienv_config_prod_dbhost = $_POST['plx_multienv_config_prod_dbhost'];
			$plx_multienv_config_prod_wpdebug = $_POST['plx_multienv_config_prod_wpdebug'];

			// Load template for wp-config.environments.php
			include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.environments.php");

			// Create new wp-config.environments.php file
			if (file_put_contents(ABSPATH . "wp-config.environments.php", $plx_multienv_template_envs) === false) {

				// Check if error message was already set
				if (isset($plx_multienv_error)) {

					// Set error message variable
					$plx_multienv_error = "";

				}

				// Set error message
				$plx_multienv_error .= "<code>wp-config.environments.php</code> ";

				//set php contents for manual entry
				$plx_multienv_manual_envs = $plx_multienv_template_envs;

			}

			// Load template for wp-config.development.php
			include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.development.php");

			// Create new wp-config.development.php file
			if (file_put_contents(ABSPATH . "wp-config.development.php", $plx_multienv_template_dev) === false) {

				// Check if error message was already set
				if (isset($plx_multienv_error)) {

					// Set error message variable
					$plx_multienv_error = "";

				}

				// Set error message
				$plx_multienv_error .= "<code>wp-config.development.php</code> ";

				//set php contents for manual entry
				$plx_multienv_manual_dev = $plx_multienv_template_dev;

			}

			// Load template for wp-config.staging.php
			include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.staging.php");

			// Create new wp-config.staging.php file
			if (file_put_contents(ABSPATH . "wp-config.staging.php", $plx_multienv_template_stage) === false) {

				// Check if error message was already set
				if (!(isset($plx_multienv_error))) {

					// Set error message variable
					$plx_multienv_error = "";

				}

				// Set error message
				$plx_multienv_error .= "<code>wp-config.staging.php</code> ";

				//set php contents for manual entry
				$plx_multienv_manual_stage = $plx_multienv_template_stage;

			}

			// Load template for wp-config.production.php
			include(PLX_MULTIENV_PLUGIN_PATH . "templates/wp-config.production.php");

			// Create new wp-config.production.php file
			if (file_put_contents(ABSPATH . "wp-config.production.php", $plx_multienv_template_prod) === false) {

				// Check if error message was already set
				if (!(isset($plx_multienv_error))) {

					// Set error message variable
					$plx_multienv_error = "";

				}

				// Set error message
				$plx_multienv_error .= "<code>wp-config.production.php</code> ";

				//set php contents for manual entry
				$plx_multienv_manual_prod = $plx_multienv_template_prod;

			}

			// Set plugin success message
			$plx_multienv_success = "<strong>Success!</strong> Environments were successfully updated";

		}

		// Load wp-config.environments.php
		include(ABSPATH . "wp-config.environments.php");

		// Load in development config file and strip out un-needed code
		$plx_multienv_config_dev_text = php_strip_whitespace(ABSPATH . "wp-config.development.php");
		$plx_multienv_config_dev_text = str_replace(array('<?php', '<?', '?>'), '', $plx_multienv_config_dev_text);
		$plx_multienv_config_dev_lines = explode(";", $plx_multienv_config_dev_text);
		$plx_multienv_config_dev_constants = array();

		// Extract constants from php code
		foreach ($plx_multienv_config_dev_lines as $plx_multienv_config_dev_line) {

		  // Skip blank lines
		  if (strlen(trim($plx_multienv_config_dev_line)) == 0)
		    continue;

		  preg_match('/^define\((\'.*\'|".*"),( )?(.*)\)$/', trim($plx_multienv_config_dev_line), $matches, PREG_OFFSET_CAPTURE);

		  if ($matches) {
		    $plx_multienv_config_dev_constant_name = substr($matches[1][0], 1, strlen($matches[1][0]) - 2);
		    $plx_multienv_config_dev_constant_value = $matches[3][0];
		    $plx_multienv_config_dev_constants[$plx_multienv_config_dev_constant_name] = trim($plx_multienv_config_dev_constant_value, "'");
		  }
		}

		// Define development environment variables
		$plx_multienv_config_dev_siteurl = $plx_multienv_hostname_dev;
		$plx_multienv_config_dev_dbname = $plx_multienv_config_dev_constants['DB_NAME'];
		$plx_multienv_config_dev_dbuser = $plx_multienv_config_dev_constants['DB_USER'];
		$plx_multienv_config_dev_dbpass = $plx_multienv_config_dev_constants['DB_PASSWORD'];
		$plx_multienv_config_dev_dbhost = $plx_multienv_config_dev_constants['DB_HOST'];
		$plx_multienv_config_dev_wpdebug = $plx_multienv_config_dev_constants['WP_DEBUG'];

		// Load in staging config file and strip out un-needed code
		$plx_multienv_config_stage_text = php_strip_whitespace(ABSPATH . "wp-config.staging.php");
		$plx_multienv_config_stage_text = str_replace(array('<?php', '<?', '?>'), '', $plx_multienv_config_stage_text);
		$plx_multienv_config_stage_lines = explode(";", $plx_multienv_config_stage_text);
		$plx_multienv_config_stage_constants = array();

		// Extract constants from php code
		foreach ($plx_multienv_config_stage_lines as $plx_multienv_config_stage_line) {

		  // Skip blank lines
		  if (strlen(trim($plx_multienv_config_stage_line)) == 0)
		    continue;

		  preg_match('/^define\((\'.*\'|".*"),( )?(.*)\)$/', trim($plx_multienv_config_stage_line), $matches, PREG_OFFSET_CAPTURE);

		  if ($matches) {
		    $plx_multienv_config_stage_constant_name = substr($matches[1][0], 1, strlen($matches[1][0]) - 2);
		    $plx_multienv_config_stage_constant_value = $matches[3][0];
		    $plx_multienv_config_stage_constants[$plx_multienv_config_stage_constant_name] = trim($plx_multienv_config_stage_constant_value, "'");
		  }
		}

		// Define development environment variables
		$plx_multienv_config_stage_siteurl = $plx_multienv_hostname_stage;
		$plx_multienv_config_stage_dbname = $plx_multienv_config_stage_constants['DB_NAME'];
		$plx_multienv_config_stage_dbuser = $plx_multienv_config_stage_constants['DB_USER'];
		$plx_multienv_config_stage_dbpass = $plx_multienv_config_stage_constants['DB_PASSWORD'];
		$plx_multienv_config_stage_dbhost = $plx_multienv_config_stage_constants['DB_HOST'];
		$plx_multienv_config_stage_wpdebug = $plx_multienv_config_stage_constants['WP_DEBUG'];

		// Load in production config file and strip out un-needed code
		$plx_multienv_config_prod_text = php_strip_whitespace(ABSPATH . "wp-config.production.php");
		$plx_multienv_config_prod_text = str_replace(array('<?php', '<?', '?>'), '', $plx_multienv_config_prod_text);
		$plx_multienv_config_prod_lines = explode(";", $plx_multienv_config_prod_text);
		$plx_multienv_config_prod_constants = array();

		// Extract constants from php code
		foreach ($plx_multienv_config_prod_lines as $plx_multienv_config_prod_line) {

		  // Skip blank lines
		  if (strlen(trim($plx_multienv_config_prod_line)) == 0)
		    continue;

		  preg_match('/^define\((\'.*\'|".*"),( )?(.*)\)$/', trim($plx_multienv_config_prod_line), $matches, PREG_OFFSET_CAPTURE);

		  if ($matches) {
		    $plx_multienv_config_prod_constant_name = substr($matches[1][0], 1, strlen($matches[1][0]) - 2);
		    $plx_multienv_config_prod_constant_value = $matches[3][0];
		    $plx_multienv_config_prod_constants[$plx_multienv_config_prod_constant_name] = trim($plx_multienv_config_prod_constant_value, "'");
		  }
		}

		// Define development environment variables
		$plx_multienv_config_prod_siteurl = $plx_multienv_hostname_prod;
		$plx_multienv_config_prod_dbname = $plx_multienv_config_prod_constants['DB_NAME'];
		$plx_multienv_config_prod_dbuser = $plx_multienv_config_prod_constants['DB_USER'];
		$plx_multienv_config_prod_dbpass = $plx_multienv_config_prod_constants['DB_PASSWORD'];
		$plx_multienv_config_prod_dbhost = $plx_multienv_config_prod_constants['DB_HOST'];
		$plx_multienv_config_prod_wpdebug = $plx_multienv_config_prod_constants['WP_DEBUG'];
	?>
	<div class="wrap">
		<h2>PLX Multi-Environments</h2>
		<ul class="nav-tab-wrapper" style="border-bottom:1px solid #ccc; padding-top: 0;">
			<li>
      	<a href="javascript:changeTab(1);" id="plx-multienv-nav-1" class="nav-tab nav-tab-active" style="color:#D90000;">Development</a>
			</li>
			<li>
      	<a href="javascript:changeTab(2);" id="plx-multienv-nav-2" class="nav-tab" style="color:#FF8000;">Staging</a>
			</li>
			<li>
      	<a href="javascript:changeTab(3);" id="plx-multienv-nav-3" class="nav-tab" style="color:#2DB200;">Production</a>
			</li>
    </ul>
		<form method="post">
			<table class="form-table tab-content" id="plx-multienv-tab-1">
        <tr valign="top">
        	<th scope="row">Development Site URL</th>
					<td>
						<input type="text" name="plx_multienv_config_dev_url" id="plx_multienv_config_dev_url" value="<?php echo $plx_multienv_config_dev_siteurl; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Host</th>
					<td>
						<input type="text" name="plx_multienv_config_dev_dbhost" id="plx_multienv_config_dev_dbhost" value="<?php echo $plx_multienv_config_dev_dbhost; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Name</th>
					<td>
						<input type="text" name="plx_multienv_config_dev_dbname" id="plx_multienv_config_dev_dbname" value="<?php echo $plx_multienv_config_dev_dbname; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Username</th>
					<td>
						<input type="text" name="plx_multienv_config_dev_dbuser" id="plx_multienv_config_dev_dbuser" value="<?php echo $plx_multienv_config_dev_dbuser; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Password</th>
					<td>
						<input type="password" name="plx_multienv_config_dev_dbpass" id="plx_multienv_config_dev_dbpass" value="<?php echo $plx_multienv_config_dev_dbpass; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Wordpress Debug Mode</th>
					<td>
						<input type="radio" name="plx_multienv_config_dev_wpdebug" id="plx_multienv_config_dev_wpdebug_on" value="true"<?php if ($plx_multienv_config_dev_wpdebug == 'true') { ?> checked="checked"<?php } ?> />  On
						<input type="radio" name="plx_multienv_config_dev_wpdebug" id="plx_multienv_config_dev_wpdebug_off" value="false"<?php if ($plx_multienv_config_dev_wpdebug == 'false') { ?> checked="checked"<?php } ?> /> Off
					</td>
        </tr>
      </table>
			<table class="form-table tab-content hidden" id="plx-multienv-tab-2">
        <tr valign="top">
        	<th scope="row">Staging Site URL</th>
					<td>
						<input type="text" name="plx_multienv_config_stage_url" id="plx_multienv_config_stage_url" value="<?php echo $plx_multienv_config_stage_siteurl; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Host</th>
					<td>
						<input type="text" name="plx_multienv_config_stage_dbhost" id="plx_multienv_config_stage_dbhost" value="<?php echo $plx_multienv_config_stage_dbhost; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Name</th>
					<td>
						<input type="text" name="plx_multienv_config_stage_dbname" id="plx_multienv_config_stage_dbname" value="<?php echo $plx_multienv_config_stage_dbname; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Username</th>
					<td>
						<input type="text" name="plx_multienv_config_stage_dbuser" id="plx_multienv_config_stage_dbuser" value="<?php echo $plx_multienv_config_stage_dbuser; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Password</th>
					<td>
						<input type="password" name="plx_multienv_config_stage_dbpass" id="plx_multienv_config_stage_dbpass" value="<?php echo $plx_multienv_config_stage_dbpass; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Wordpress Debug Mode</th>
					<td>
						<input type="radio" name="plx_multienv_config_stage_wpdebug" id="plx_multienv_config_stage_wpdebug_on" value="true"<?php if ($plx_multienv_config_stage_wpdebug == 'true') { ?> checked="checked"<?php } ?> />  On
						<input type="radio" name="plx_multienv_config_stage_wpdebug" id="plx_multienv_config_stage_wpdebug_off" value="false"<?php if ($plx_multienv_config_stage_wpdebug == 'false') { ?> checked="checked"<?php } ?> /> Off
					</td>
        </tr>
      </table>
			<table class="form-table tab-content hidden" id="plx-multienv-tab-3">
        <tr valign="top">
        	<th scope="row">Production Site URL</th>
					<td>
						<input type="text" name="plx_multienv_config_prod_url" id="plx_multienv_config_prod_url" value="<?php echo $plx_multienv_config_prod_siteurl; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Host</th>
					<td>
						<input type="text" name="plx_multienv_config_prod_dbhost" id="plx_multienv_config_prod_dbhost" value="<?php echo $plx_multienv_config_prod_dbhost; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Name</th>
					<td>
						<input type="text" name="plx_multienv_config_prod_dbname" id="plx_multienv_config_prod_dbname" value="<?php echo $plx_multienv_config_prod_dbname; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Username</th>
					<td>
						<input type="text" name="plx_multienv_config_prod_dbuser" id="plx_multienv_config_prod_dbuser" value="<?php echo $plx_multienv_config_prod_dbuser; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Database Password</th>
					<td>
						<input type="password" name="plx_multienv_config_prod_dbpass" id="plx_multienv_config_prod_dbpass" value="<?php echo $plx_multienv_config_prod_dbpass; ?>" class="regular-text" />
					</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Wordpress Debug Mode</th>
					<td>
						<input type="radio" name="plx_multienv_config_prod_wpdebug" id="plx_multienv_config_prod_wpdebug_on" value="true"<?php if ($plx_multienv_config_prod_wpdebug == 'true') { ?> checked="checked"<?php } ?> />  On
						<input type="radio" name="plx_multienv_config_prod_wpdebug" id="plx_multienv_config_prod_wpdebug_off" value="false"<?php if ($plx_multienv_config_prod_wpdebug == 'false') { ?> checked="checked"<?php } ?> /> Off
					</td>
        </tr>
      </table>
      <input type="hidden" name="plx_multienv_noncename" id="plx_multienv_noncename" value="<?php echo wp_create_nonce( PLX_MULTIENV_PLUGIN_BASENAME ); ?>" />
      <?php
			submit_button();
			?>
		</form>
	<?php
	} else {

		// Check if there was an error installing files
		if (isset($plx_multienv_error)) {
	?>
		<h2>Manual Setup of Environment Files</h2>
		<p>There was a problem creating the following files, you will need to manually create them and copy the contents below. Once you've done this just press the button below to finish installation. Don't leave this page until you've done this.</p>
		<?php
		// Check if the main wp-config.php file was created
		if (isset($plx_multienv_manual_default)) {
		?>
			<p>
				<strong><code>wp-config.php</code></strong> - It's recommended you rename your existing configuration file in case you need to revert to your previous settings<br />
				<textarea class="widefat" readonly="readonly" rows="6"><?php echo $plx_multienv_manual_default; ?></textarea>
			</p>
		<?php
		}

		// Check if the main wp-config.environment.php file was created
		if (isset($plx_multienv_manual_envs)) {
		?>
			<p>
				<strong><code>wp-config.environments.php</code></strong><br />
				<textarea class="widefat" readonly="readonly" rows="6"><?php echo $plx_multienv_manual_envs; ?></textarea>
			</p>
		<?php
		}

		// Check if the main wp-config.development.php file was created
		if (isset($plx_multienv_manual_dev)) {
		?>
			<p>
				<strong><code>wp-config.development.php</code></strong><br />
				<textarea class="widefat" readonly="readonly" rows="6"><?php echo $plx_multienv_manual_dev; ?></textarea>
			</p>
		<?php
		}

		// Check if the main wp-config.staging.php file was created
		if (isset($plx_multienv_manual_stage)) {
		?>
			<p>
				<strong><code>wp-config.staging.php</code></strong><br />
				<textarea class="widefat" readonly="readonly" rows="6"><?php echo $plx_multienv_manual_stage; ?></textarea>
			</p>
		<?php
		}

		// Check if the main wp-config.production.php file was created
		if (isset($plx_multienv_manual_prod)) {
		?>
			<p>
				<strong><code>wp-config.production.php</code></strong><br />
				<textarea class="widefat" readonly="readonly" rows="6"><?php echo $plx_multienv_manual_prod; ?></textarea>
			</p>
		<?php
		}
		?>
		<p class="submit">
			<a href="/wp-admin/options-general.php?page=plx-multienv-menu-environments">
				<button class="button button-primary">I've created these files. Finish Installation</button>
			</a>
		</p>
	<?php
		} else {
	?>
			<h2>Installation of Environment Files</h2>
			<p>The next step will migrate your current database settings to the environment you select from the drop-down below and create new environment files which you can specify the settings for on the next screen. It's recommended that you run this installation on your development environment and then migrate your WordPress files and database to your other environments.</p>
			<p><strong>The following file(s) will be renamed for backup purposes:</strong></p>
			<p><code>wp-config.php</code> will be renamed to <code>wp-config.backup.php</code> just in case you need to revert to your previous setup. You can safely delete this file if needed.</p>
			<p><strong>The following files will be created:</strong></p>
			<p>
				<code>wp-config.php</code><br />
				<code>wp-config.environments.php</code><br />
				<code>wp-config.development.php</code><br />
				<code>wp-config.staging.php</code><br />
				<code>wp-config.production.php</code>
			</p>
			<p>You can edit certain global wordpress settings in the new wp-config.php file but all other files are dynamically generated by the plugin and shouldn't be edited directly.
			<form method="post">
				<table class="form-table">
					<tr valign="top">
	        	<th scope="row">Which environment are you running this setup on?</th>
						<td>
							<select id="plx_multienv_install_environment" name="plx_multienv_install_environment">
								<option value="development">Development Site</option>
								<option value="staging">Staging Site</option>
								<option value="production">Production Site</option>
							</select>
						</td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row">Development Site URL</th>
						<td>
							<input type="text" name="plx_multienv_config_dev_url" id="plx_multienv_config_dev_url" value="<?php echo plx_multienv_current_siteurl(); ?>" class="regular-text" disabled="disabled" />
						</td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row">Staging Site URL</th>
						<td>
							<input type="text" name="plx_multienv_config_stage_url" id="plx_multienv_config_stage_url" value="" class="regular-text" />
						</td>
	        </tr>
	        <tr valign="top">
	        	<th scope="row">Production Site URL</th>
						<td>
							<input type="text" name="plx_multienv_config_prod_url" id="plx_multienv_config_prod_url" value="" class="regular-text" />
						</td>
	        </tr>
				</table>
				<?php
				submit_button('Start Installation');
				?>
			</form>
	<?php
		} //END Check if there was an error installing files

	} //END Check if the environments have been installed
	?>
	</div>
<?php
}