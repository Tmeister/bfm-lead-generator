<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.bellflowermedia.com/
 * @since             1.0.0
 * @package           Bfm_Leads
 *
 * @wordpress-plugin
 * Plugin Name:       Bellflower Lead Generator
 * Plugin URI:        http://www.bellflowermedia.com/
 * Description:       Lead Generator for Real Estate Sites, Allows to add Steps forms to collect leads with full analytics, leads listing and Get Response integration to collect email address as well.
 * Version:           1.0.4
 * Author:            Enrique Chavez
 * Author URI:        http://enriquechavez.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bfm-leads
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('BMF_LEADS_DB_VERSION', '1.0.11');

/**
 * The class responsible for Update the Plugin via GitHub
 */
require_once plugin_dir_path( __FILE__ )  . 'includes/vendors/BFIGitHubPluginUploader.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bfm-leads-activator.php
 */
function activate_bfm_leads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bfm-leads-activator.php';
	Bfm_Leads_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bfm-leads-deactivator.php
 */
function deactivate_bfm_leads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bfm-leads-deactivator.php';
	Bfm_Leads_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bfm_leads' );
register_deactivation_hook( __FILE__, 'deactivate_bfm_leads' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bfm-leads.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bfm_leads() {

	if ( is_admin() ) {
	    new BFIGitHubPluginUpdater( __FILE__, 'Tmeister', "bfm-lead-generator", "8ff73cf06dc92df1648c537bdf86b1cd00486b75" );
	}

	$plugin = new Bfm_Leads();
	$plugin->run();

}
run_bfm_leads();
