<?php
/**
 * Plugin Name:  IONOS Address Book
 * Plugin URI:   https://wordpress.org/plugins/cosy-address-book
 * Description:  Sends visitor contact data generated by contact forms embedded in WordPress websites to IONOS Address Book
 * Version:      2.0.0
 * License:      MIT
 * Author:       IONOS
 * Author URI:   https://www.ionos.com
 * Text Domain:  cosy-address-book
 * Domain Path:  /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class CoSy_Address_Book {
	const VERSION = '2.0.0';
	const ADDRESS_BOOK_PAGE_ID = 'cosy-address-book';
	const LANGUAGE_DOMAIN = 'cosy-address-book';
	const PLUGIN_TYPE_MUST_USE = 'must-use-plugin';
	const PLUGIN_TYPE_DEFAULT = 'default-plugin-type';

	/**
	 * CoSy_Address_Book constructor
	 */
	public function __construct() {
		$this->load_global_files();
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
	}

	/**
	 * Activates menu entry in admin navigation bar
	 */
	public function add_address_book_menu() {
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			add_menu_page(
				__( 'Contacts', 'cosy-address-book' ),
				__( 'Contacts', 'cosy-address-book' ) . ' <span class="update-plugins count-1"><span class="plugin-count">!</span></span>',
				'manage_options',
				self::ADDRESS_BOOK_PAGE_ID,
				array( $this, 'load_menu_page' ),
				'dashicons-book',
				62
			);
		}
	}

	/**
	 * Loads main admin menu page to describe how to use current plugin
	 */
	public function load_menu_page() {
		CoSy_Address_Book_View::load_page();
	}

	/**
	 * Adds all required actions for handling admin menu-, plugin form- and IONOS Address Book data
	 */
	private function add_actions() {
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
		add_action( 'admin_menu', array( $this, 'add_address_book_menu' ) );
	}

	/**
	 * Binds all requires utility classes
	 */
	private function load_global_files() {
		include_once 'inc/view.php';
	}

	/**
	 * Checks if current installation is must-use plugin or not
	 */
	private function is_must_use_plugin() {
		return strpos( plugin_dir_path( __FILE__ ), 'mu-plugins' ) !== false;
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
}

$cosy_address_book = new CoSy_Address_Book();