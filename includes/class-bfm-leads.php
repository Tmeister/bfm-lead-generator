<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/includes
 * @author     Enrique Chavez <noone@tmeister.net>
 */
class Bfm_Leads {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bfm_Leads_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $bfm_leads    The string used to uniquely identify this plugin.
	 */
	protected $bfm_leads;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->bfm_leads = 'bfm-leads';
		$this->version = '1.0.4';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bfm_Leads_Loader. Orchestrates the hooks of the plugin.
	 * - Bfm_Leads_i18n. Defines internationalization functionality.
	 * - Bfm_Leads_Admin. Defines all hooks for the dashboard.
	 * - Bfm_Leads_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for Database Handler
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/db/class-bfm-leads-db.php';

		/**
		 * The class responsible for Settings
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendors/class.settings-api.php';

		/**
		 * The class responsible for Get Response API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendors/getresponse/class-get-response.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bfm-leads-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bfm-leads-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bfm-leads-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-bfm-leads-public.php';

		$this->loader = new Bfm_Leads_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bfm_Leads_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Bfm_Leads_i18n();
		$plugin_i18n->set_domain( $this->get_bfm_leads() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Bfm_Leads_Admin( $this->get_bfm_leads(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'update_db_check');
		$this->loader->add_action( 'admin_init', $plugin_admin, 'create_settings');
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menus' );
		$this->loader->add_action( 'wp_ajax_bfm_get_graph_data', $plugin_admin, 'get_graph_data' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Bfm_Leads_Public( $this->get_bfm_leads(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'init' );


		$this->loader->add_action( 'wp_ajax_add_email', $plugin_public, 'add_email' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_email', $plugin_public, 'add_email' );

		$this->loader->add_action( 'wp_ajax_add_name_phone_best', $plugin_public, 'add_name_phone_best');
		$this->loader->add_action( 'wp_ajax_nopriv_add_name_phone_best', $plugin_public, 'add_name_phone_best');

		$this->loader->add_action( 'wp_ajax_add_status_rent_manager', $plugin_public, 'add_status_rent_manager');
		$this->loader->add_action( 'wp_ajax_nopriv_add_status_rent_manager', $plugin_public, 'add_status_rent_manager');

		$this->loader->add_action( 'wp_ajax_add_comments', $plugin_public, 'add_comments');
		$this->loader->add_action( 'wp_ajax_nopriv_add_comments', $plugin_public, 'add_comments');

		$this->loader->add_action( 'wp_ajax_add_email_and_name', $plugin_public, 'add_email_and_name' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_email_and_name', $plugin_public, 'add_email_and_name' );

		$this->loader->add_action( 'wp_ajax_add_phone_best', $plugin_public, 'add_phone_best' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_phone_best', $plugin_public, 'add_phone_best' );

		$this->loader->add_action( 'wp_ajax_add_aditional_fields', $plugin_public, 'add_aditional_fields' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_aditional_fields', $plugin_public, 'add_aditional_fields' );

		$this->loader->add_action( 'wp_ajax_add_form_hit', $plugin_public, 'add_form_hit' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_form_hit', $plugin_public, 'add_form_hit' );

		$this->loader->add_filter( 'the_content', $plugin_public, 'add_form_to_posts' );


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_bfm_leads() {
		return $this->bfm_leads;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bfm_Leads_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
