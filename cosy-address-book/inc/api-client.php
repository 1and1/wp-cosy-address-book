<?php

/**
 * Class responsible of handling of CoSy Address Book api data and form handlers
 */
class CoSy_Address_Book_Api_Client {
	const WP_OPTION_API_KEY_DATA = 'cosy_address_book_api_key_data';
	const WP_OPTION_FORM_TYPE = 'cosy_address_book_form_type';
	const OPTION_API_KEY = 'api_key';
	const OPTION_DOMAIN = 'domain';

	/**
	 * Instance of api client
	 *
	 * @param CoSy_Address_Book_Api_Client
	 */
	private static $instance;

	/**
	 * Instance of http client
	 *
	 * @var Address_Book_Rest_Client
	 */
	private $rest_client;

	/**
	 * Instance of Address_Book_Config
	 *
	 * @var CoSy_Address_Book_Config
	 */
	private $config;

	/**
	 * List of registered form handlers
	 *
	 * @var CoSy_Form_Handler[]
	 */
	private $form_handlers;

	/**
	 * retrieves an instance of CoSy_Address_Book_Api_Client
	 *
	 * @return CoSy_Address_Book_Api_Client
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			$config         = CoSy_Address_Book_Config::instance();
			$rest_client    = new Address_Book_Rest_Client( $config );
			self::$instance = new self( CoSy_Address_Book_Config::instance(), $rest_client );
		}

		return self::$instance;
	}

	/**
	 * Creates an instance of CoSy_Address_Book_Api_Client
	 *
	 * @param CoSy_Address_Book_Config $config
	 * @param Address_Book_Rest_Client $rest_Client
	 */
	public function __construct( CoSy_Address_Book_Config $config, Address_Book_Rest_Client $rest_Client ) {
		$this->is_connected  = false;
		$this->form_handlers = array();
		$this->config        = $config;
		$this->rest_client   = $rest_Client;
	}

	/**
	 * Connects domain of current website to CoSy Address Book via given api key and persists result into wp_options
	 *
	 * @param string $api_key
	 *
	 * @return CoSy_Address_Book_Api_Response object response determining if connection was successful or not
	 */
	public function connect_api_key( $api_key ) {
		$domain       = $this->fetch_domain();
		$is_connected = $this->connect_to_api( $domain, $api_key );
		$api_response = new CoSy_Address_Book_Api_Response();

		if ( $is_connected ) {
			$is_persisted_key = $this->persist_api_key( $domain, $api_key );
			$api_response->set_is_successful( $is_connected );

			if ( ! $is_persisted_key ) {
				$api_response->set_is_successful( false );
				$api_response->set_message(
					$this->get_translation( 'cosy_address_book_generic_error' )
				);
			}
		} else {
			$api_response->set_message(
				$this->get_translation( 'cosy_address_book_generic_error' )
			);
		}

		return $api_response;
	}

	/**
	 * Saves field mapping
	 *
	 * @param string $field_mapping
	 * @param string $form_type
	 *
	 * @return CoSy_Address_Book_Api_Response object response to check if persistence of field mapping was successful or not
	 */
	public function save_field_mapping( $field_mapping, $form_type ) {
		$persisted_field_mapping = json_encode( $this->get_field_mapping( $form_type ) );

		if ( strcasecmp( $persisted_field_mapping, $field_mapping ) == 0 ) {
			return new CoSy_Address_Book_Api_Response(
				true,
				$this->get_translation( 'cosy_address_book_field_mapping_successful' )
			);
		}

		$is_saved_mapping = update_option(
			$this->get_form_type_option_name( $form_type ),
			$field_mapping
		);

		$translation_key = ( $is_saved_mapping ) ? 'cosy_address_book_field_mapping_successful' : 'cosy_address_book_generic_error';

		return new CoSy_Address_Book_Api_Response(
			$is_saved_mapping,
			$this->get_translation( $translation_key )
		);
	}

	/**
	 * Retrieves persisted field mapping identified mapped to given form type
	 *
	 * @param string $form_type
	 *
	 * @return mixed
	 */
	public function get_field_mapping( $form_type ) {
		$field_mapping = get_option( $this->get_form_type_option_name( $form_type ) );

		if ( empty( $field_mapping ) ) {
			return $this->get_default_field_mapping( $form_type );
		}

		return json_decode( $field_mapping, true );
	}

	/**
	 * Adds a form handler to current list of registered form handlers
	 *
	 * @param CoSy_Form_Handler $form_handler
	 */
	public function add_form_handler( CoSy_Form_Handler $form_handler ) {
		$this->form_handlers[ $form_handler->get_type() ] = $form_handler;
	}

	/**
	 * Retrieves registered form handlers
	 *
	 * @return CoSy_Form_Handler[]
	 */
	public function get_form_handlers() {
		return $this->form_handlers;
	}

	/**
	 * Retrieves a form handler identified by given form type if found
	 *
	 * @param string $form_type form type to identify handler to be retrieved
	 *
	 * @return CoSy_Form_Handler|null
	 */
	public function get_form_handler( $form_type ) {
		if ( array_key_exists( $form_type, $this->form_handlers ) ) {
			return $this->form_handlers[ $form_type ];
		}

		return null;
	}

	/**
	 * Saves given data into CoSy Address Book
	 *
	 * @param array $data data to be sent
	 * @param string $form_type type of form to extract corresponding data to be sent to address book
	 *
	 * @return CoSy_Address_Book_Api_Response Api_Response object response to check if persistence of field mapping was successful or not
	 */
	public function save_data_into_address_book( array $data, $form_type ) {
		$form_handler  = $this->get_form_handler( $form_type );
		$field_mapping = $this->get_field_mapping( $form_type );

		$field_mapping_array = ( is_array( $field_mapping ) ) ? $field_mapping : json_decode( $field_mapping, true );

		$api_key      = $this->get_api_key();
		$api_response = new CoSy_Address_Book_Api_Response();

		if ( $form_handler instanceof CoSy_Form_Handler ) {
			if ( $form_handler->has_consent( $data ) ) {
				$request_body  = $form_handler->create_request_body( $data, $field_mapping_array );
				$is_saved_data = $this->rest_client->create_contacts( $api_key, $request_body );
				$api_response->set_is_successful( $is_saved_data );

				if ( ! $is_saved_data ) {
					$api_response->set_is_successful( false );
					$api_response->set_message( $this->rest_client->get_last_error() );
				}
			}
		}

		return $api_response;
	}

	/**
	 * Sets value of current config to the given one
	 *
	 * @param CoSy_Address_Book_Config $config
	 */
	public function set_config( CoSy_Address_Book_Config $config ) {
		$this->config = $config;
	}

	/**
	 * Checks whether current domain is connected to CoSy Address Book or not
	 *
	 * @return boolean
	 */
	public function is_connected() {
		$api_key = $this->get_api_key();

		if ( ! empty( $api_key ) ) {
			return $this->rest_client->get_subscription( $api_key )->is_successful();
		}

		return false;
	}

	/**
	 * Checks whether current domain is the which is subscribed to CoSy Address Book or not
	 *
	 * Note that a subscription update request will be sent to CoSy Middleware API
	 * if current domain is different from domain subscribed  to CoSy Address Book
	 *
	 * @return boolean
	 */
	public function check_subscription_update() {
		$api_key = $this->get_api_key();
		$domain  = $this->fetch_domain();

		if ( ! empty( $api_key ) ) {
			$subscription_response = $this->rest_client->get_subscription( $api_key );
			$is_subscribed         = $subscription_response->is_successful();
			$subscription          = $subscription_response->get_message();

			if ( $subscription !== $domain ) {
				$is_subscribed = $this->update_subscription( $subscription, $domain );
			}

			return $is_subscribed;
		}

		return false;
	}

	/**
	 * Checks if web form settings like field mapping shall be displayed on view or not
	 *
	 * Note that at least one web form of supported form handler type must be embedded on page to display form settings
	 *
	 * @return bool
	 */
	public function has_displayable_form_settings() {
		$has_displayable_form_settings = false;

		/* @var CoSy_Form_Handler $form_handler */
		foreach ( $this->form_handlers as $form_handler ) {
			if ( $form_handler->has_form_embedded_on_page() ) {
				$has_displayable_form_settings = true;
			}
		}

		return $has_displayable_form_settings;
	}

	/**
	 * Persists given api key into WP-Options
	 *
	 * @param string $domain
	 * @param string $api_key
	 *
	 * @return bool flags determining if persistence of api key was successful or not
	 */
	private function persist_api_key( $domain, $api_key ) {
		return update_option(
			self::WP_OPTION_API_KEY_DATA,
			array(
				self::OPTION_DOMAIN  => $domain,
				self::OPTION_API_KEY => $api_key
			)
		);
	}


	/**
	 * Connects given domain to CoSy Address Book via given api key
	 *
	 * @param string $domain
	 * @param string $api_key value of api_key to be used to connect to CoSy Address Book
	 *
	 * @return bool flag determining if connection was successful or not
	 */
	private function connect_to_api( $domain, $api_key ) {
		return $this->rest_client->create_subscription( $domain, $api_key );
	}

	/**
	 * Retrieves current value of api_key at which current domain is connected to CoSy Address Book to
	 *
	 * @return string
	 */
	private function get_api_key() {
		return $this->get_persisted_api_key_data( self::OPTION_API_KEY );
	}

	/**
	 * Retrieves form type wp option name matching to given form type
	 *
	 * @param string $form_type
	 *
	 * @return string
	 */
	private function get_form_type_option_name( $form_type ) {
		return sprintf( '%s_%s', self::WP_OPTION_FORM_TYPE, $form_type );
	}

	/**
	 * Retrieves current domain fetched from WordPress configuration
	 *
	 * @return string
	 */
	private function fetch_domain() {
		return parse_url( get_site_url(), PHP_URL_HOST );
	}

	/**
	 * Retrieves persisted api key data
	 *
	 * @param $key identifier of data type to be used
	 *
	 * @return string
	 */
	private function get_persisted_api_key_data( $key ) {
		$wp_option_api_key_data = get_option( self::WP_OPTION_API_KEY_DATA );
		if ( is_array( $wp_option_api_key_data ) && array_key_exists( $key, $wp_option_api_key_data ) ) {
			return $wp_option_api_key_data[ $key ];
		}

		return '';
	}

	/**
	 * Triggers api update request of persisted domain to CoSy Address Book API
	 *
	 * @param string $persisted_domain
	 * @param string $current_domain
	 *
	 * @return bool
	 */
	private function update_subscription( $persisted_domain, $current_domain ) {
		return $this->rest_client->update_subscription(
			$persisted_domain,
			$current_domain,
			$this->get_api_key()
		);
	}

	/**
	 * Retrieves default field mapping to be used in case user field mapping is not yet persisted
	 *
	 * @param string $form_type
	 *
	 * @return array
	 */
	private function get_default_field_mapping( $form_type ) {
		$default_field_mapping = [];

		$api_fields   = $this->config->get_api_fields();
		$form_handler = $this->get_form_handler( $form_type );
		if ( $form_handler instanceof CoSy_Form_Handler ) {
			foreach ( $api_fields as $api_field ) {
				$field_by_type = $form_handler->get_field_by_type( $api_field );
				if ( ! empty( $field_by_type ) ) {
					$default_field_mapping[ $field_by_type ] = $api_field;
				}
			}
		}

		return $default_field_mapping;
	}

	/**
	 * Retrieves translation of given key
	 *
	 * @param string $translation_key
	 *
	 * @return string
	 */
	private function get_translation( $translation_key ) {
		return __( $translation_key, 'cosy-address-book' );
	}
}