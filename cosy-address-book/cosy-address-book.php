<?php
/**
 * Plugin Name:  1&1 IONOS Address Book
 * Plugin URI:   https://wordpress.org/plugins/cosy-address-book
 * Description:  Sends visitor contact data generated by contact forms embedded in WordPress websites to 1&1 IONOS Address Book
 * Version:      1.0.0
 * License:      MIT
 * Author:       1&1 IONOS
 * Author URI:   https://www.ionos.com
 * Text Domain:  cosy-address-book
 * Domain Path:  /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class CoSy_Address_Book {
	const VERSION = '1.0.0';
	const ADDRESS_BOOK_PAGE_ID = 'cosy-address-book';
	const LANGUAGE_DOMAIN = 'cosy-address-book';
	const PLUGIN_TYPE_MUST_USE = 'must-use-plugin';
	const PLUGIN_TYPE_DEFAULT = 'default-plugin-type';

	/**
	 * @var CoSy_Address_Book_Api_Client
	 */
	private $api_client;

	/**
	 * @var bool
	 */
	private $show_admin_menu;

	/**
	 * CoSy_Address_Book constructor
	 */
	public function __construct() {
		$this->load_global_files();
		$this->init();
		$this->load_form_handlers();
		$this->add_actions();
	}

	/**
	 * Loads text domain to be able to use available translations
	 */
	public function load_text_domain() {
		$language_loaded = null;

		if( $this->is_must_use_plugin() ) {
			$language_loaded = $this->load_text_domain_by_plugin_type( self::PLUGIN_TYPE_MUST_USE );
		} else {
			$language_loaded = $this->load_text_domain_by_plugin_type( self::PLUGIN_TYPE_DEFAULT );
		}

		// Check whether language could be loaded properly. If not, use en_US as a fallback.
		if ( empty( $language_loaded ) ) {
			load_textdomain( self::LANGUAGE_DOMAIN, $this->get_default_language_file_path() );
		}
	}

	/**
	 * Activates menu entry in admin navigation bar
	 */
	public function add_address_book_menu() {
		if ( is_admin() && current_user_can( 'manage_options' ) && $this->show_admin_menu ) {
			add_menu_page(
				__( 'cosy_address_book_admin_page_title', 'cosy-address-book' ),
				__( 'cosy_address_book_menu_title', 'cosy-address-book' ),
				'manage_options',
				self::ADDRESS_BOOK_PAGE_ID,
				array( $this, 'load_menu_page' ),
				'dashicons-book',
				62
			);
		}
	}

	/**
	 * Adds available javascript and css scripts
	 */
	public function add_js_and_css_scripts() {
		wp_enqueue_style( 'cosy-address-book', plugins_url( 'css/address-book.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_script( 'cosy-address-book', plugins_url( 'js/address-book.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
	}

	/**
	 * Loads main admin menu page to describe how to use current plugin
	 */
	public function load_menu_page() {
		CoSy_Address_Book_View::load_page();
	}

	/**
	 * Initializes current available rest api instance and register requires rest routes
	 */
	public function address_book_rest_api_init() {
		CoSy_Address_Book_Rest_Api::instance()->register_rest_routes();
	}

	/**
	 * Adds all required actions for handling admin menu-, plugin form- and IONOS Address Book data
	 */
	private function add_actions() {
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
		add_action( 'admin_menu', array( $this, 'add_address_book_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_js_and_css_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'address_book_rest_api_init' ) );
	}

	/**
	 * Binds all requires utility classes
	 */
	private function load_global_files() {
		include_once 'inc/api-client.php';
		include_once 'inc/rest-api.php';
		include_once 'inc/api-response.php';
		include_once 'inc/http-response.php';
		include_once 'inc/rest-client.php';
		include_once 'inc/config.php';
		include_once 'inc/market.php';
		include_once 'inc/form-handler.php';
		include_once 'inc/view.php';
	}

	/**
	 * Binds and activates form handlers in dependence to active form plugins
	 */
	private function load_form_handlers() {
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			include_once 'inc/handlers/contact-form-7.php';
			$this->add_form_handler( new CoSy_Contact_Form_7_Handler() );
			$this->show_admin_menu();
			add_filter( 'wpcf7_posted_data', function ( $form_data ) {
				$this->api_client->save_data_into_address_book( $form_data, CoSy_Contact_Form_7_Handler::FORM_TYPE );
				return $form_data;
			} );
		}

		if ( is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {
			include_once 'inc/handlers/wp-forms-lite.php';
			$this->add_form_handler( new CoSy_WP_Forms_Lite_Handler() );
			$this->show_admin_menu();
			add_filter( 'wpforms_process_complete', function ( $form_data ) {
				$this->api_client->save_data_into_address_book( $form_data, CoSy_WP_Forms_Lite_Handler::FORM_TYPE );
			} );
		}
	}

	/**
	 * Initializes class instance variables
	 */
	private function init() {
		$this->api_client      = CoSy_Address_Book_Api_Client::instance();
		$this->show_admin_menu = false;
	}

	/**
	 * Adds given form handler and activates corresponding admin menu flag
	 *
	 * @param CoSy_Form_Handler $form_handler
	 * @param string $filter_function
	 * @param string $data_post_handler_function
	 */
	private function add_form_handler( CoSy_Form_Handler $form_handler ) {
		$this->api_client->add_form_handler( $form_handler );
	}

	/**
	 * Sets value of show_admin_menu flag to true
	 *
	 * In this case show admin menu icon will become visible in wp-admin navigation bar and
	 * corresponding views get accessible too
	 */
	private function show_admin_menu() {
		$this->show_admin_menu = true;
	}

	/**
	 * Retrieves default language file path
	 *
	 * @return string
	 */
	private function get_default_language_file_path() {
		return sprintf(	'%s/languages/%s-en_US.mo',__DIR__,	self::LANGUAGE_DOMAIN );
	}

	/**
	 * Loads text domain by given plugin type
	 *
	 * @param string $plugin_type in this case either a must-use-plugin or a plugin installable by user
	 *
	 * @return bool
	 */
	private function load_text_domain_by_plugin_type( $plugin_type ){
		$language_loaded = null;

		switch ( $plugin_type ) {
			case self::PLUGIN_TYPE_MUST_USE:
				$language_loaded = load_muplugin_textdomain(
					self::LANGUAGE_DOMAIN,
					basename( dirname( __FILE__ ) ). '/languages'
				);
				break;

			case self::PLUGIN_TYPE_DEFAULT:
				$language_loaded = load_plugin_textdomain(
					self::LANGUAGE_DOMAIN,
					false,
					dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
				break;

			default:
				$language_loaded = false;
		}

		return $language_loaded;
	}

	/**
	 * Checks if current installation is must-use plugin or not
	 */
	private function is_must_use_plugin()
	{
		return strpos( plugin_dir_path( __FILE__ ), 'mu-plugins' ) !== false;
	}
}

$cosy_address_book = new CoSy_Address_Book();