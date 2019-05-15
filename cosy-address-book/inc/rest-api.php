<?php

/**
 * This class is responsible of encapsulating handling of http requests sent to plugin such as:
 * fetching, saving mapping between form -and api fields or handling of api key for subscription to CoSy Address Book
 */
class CoSy_Address_Book_Rest_Api {
	/**
	 * content type for field mapping
	 *
	 * @var string
	 */
	CONST CONTENT_TYPE_FIELD_MAPPING = 'application/vnd.ionos.product.integration.cosy.wordpress.field-mapping-v1+json';

	/**
	 * content type for api key
	 *
	 * @var string
	 */
	CONST CONTENT_TYPE_API_KEY = 'application/vnd.ionos.product.integration.cosy.wordpress.api-key-v1+json';

	/**
	 * content type for generic success feedback
	 *
	 * @var string
	 */
	CONST CONTENT_TYPE_SUCCESS = 'application/vnd.ionos.product.integration.cosy.wordpress.success-v1+json';

	/**
	 * Request param for field mapping
	 *
	 * @var string
	 */
	CONST PARAM_FORM_TYPE = 'formType';

	/**
	 * Request param for field mapping
	 *
	 * @var string
	 */
	CONST PARAM_FIELD_MAPPING = 'field_mapping';

	/**
	 * Request param for api_key
	 *
	 * @var string
	 */
	CONST PARAM_API_KEY = 'api_key';

	/**
	 * Namespace of first version of rest api
	 */
	const NAMESPACE_V1 = 'cosy/v1';

	/**
	 * Instance of rest api
	 *
	 * @param CoSy_Address_Book_Rest_Api
	 */
	private static $instance;

	/**
	 * Instance of address book api client to communicate with CoSy Address Book api
	 *
	 * @var CoSy_Address_Book_Api_Client
	 */
	private $api_client;

	/**
	 * Instance of address book config to extract for example supported plugins
	 *
	 * @var CoSy_Address_Book_Config
	 */
	private $config;

	/**
	 * Flag determining if current user is authorised to access rest api
	 *
	 * @var bool
	 */
	private $is_authorised_current_user;

	/**
	 * retrieves an instance of Address_Book_Rest_Api
	 *
	 * @return CoSy_Address_Book_Rest_Api
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self(
				CoSy_Address_Book_Api_Client::instance(),
				CoSy_Address_Book_Config::instance()
			);
		}

		return self::$instance;
	}

	/**
	 * Address_Book_Internal_Rest_Api constructor.
	 *
	 * @param CoSy_Address_Book_Api_Client $api_client
	 */
	public function __construct( CoSy_Address_Book_Api_Client $api_client, CoSy_Address_Book_Config $config ) {
		$this->is_authorised_current_user = current_user_can( 'manage_options' );
		$this->api_client                 = $api_client;
		$this->config                     = $config;
	}

	/**
	 * Registers required rest routes to serve sent requests
	 */
	public function register_rest_routes() {
		register_rest_route( self::NAMESPACE_V1,
			'address-book/connect',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'connect_api_key' ),
					'permission_callback' => function () {
						return $this->is_authorised_current_user;
					}
				)
			)
		);

		register_rest_route( self::NAMESPACE_V1,
			'(?P<formType>\w+)/fields',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_field_mapping' ),
					'args'                => array(
						'formType' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_form_type' )
						)
					),
					'permission_callback' => function () {
						return $this->is_authorised_current_user;
					}
				)
			)
		);

		register_rest_route( self::NAMESPACE_V1,
			'(?P<formType>\w+)/fields/save',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_field_mapping' ),
					'args'                => array(
						'formType' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_form_type' )
						),
					),
					'permission_callback' => function () {
						return $this->is_authorised_current_user;
					}
				)
			)
		);
	}

	/**
	 * Validates form type used as request parameter
	 *
	 * @param string $form_type
	 *
	 * @return bool
	 */
	public function validate_form_type( $form_type ) {
		return in_array( $form_type, $this->config->get_supported_plugin_types() );
	}

	/**
	 * Retrieves field mapping data in accordance to form type provided by request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_field_mapping( WP_REST_Request $request ) {

		$field_mapping = $this->api_client->get_field_mapping( $request->get_param( self::PARAM_FORM_TYPE ) );

		return $this->create_success_rest_response( $field_mapping, self::CONTENT_TYPE_FIELD_MAPPING );
	}

	/**
	 * Persists field mapping data provided by request in accordance to a specific form type
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_field_mapping( WP_REST_Request $request ) {
		$is_valid_request = $this->validate_request( $request );

		if ( ! $is_valid_request ) {
			return $this->create_error_rest_response( 'save field mapping', WP_Http::BAD_REQUEST );
		}

		$request_body = json_decode( $request->get_body(), true );

		$response = $this->api_client->save_field_mapping(
			json_encode( $request_body[ self::PARAM_FIELD_MAPPING ] ),
			$request->get_param( self::PARAM_FORM_TYPE )
		)->to_array();

		return $this->create_success_rest_response( $response, self::CONTENT_TYPE_FIELD_MAPPING );
	}

	/**
	 * Connects domain of current installation to CoSy Address Book using api key provided by request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function connect_api_key( WP_REST_Request $request ) {
		$is_valid_request = $this->validate_request( $request );
		if ( ! $is_valid_request ) {
			return $this->create_error_rest_response( 'api key connection', WP_Http::BAD_REQUEST );
		}

		$request_body = json_decode( $request->get_body(), true );
		$response     = $this->api_client->connect_api_key( $request_body[ self::PARAM_API_KEY ] )->to_array();

		return $this->create_success_rest_response( $response, self::CONTENT_TYPE_SUCCESS );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	private function validate_request( WP_REST_Request $request ) {
		$is_valid_request = null;

		$content_type = $request->get_header( 'Content-Type' );

		switch ( $content_type ) {
			case self::CONTENT_TYPE_FIELD_MAPPING:
				$request_body     = json_decode( $request->get_body(), true );
				$is_valid_request = isset( $request_body[ self::PARAM_FIELD_MAPPING ] ) &&
				                    ! empty( $request_body[ self::PARAM_FIELD_MAPPING ] );
				break;

			case self::CONTENT_TYPE_API_KEY:
				$request_body     = json_decode( $request->get_body(), true );
				$is_valid_request = isset( $request_body[ self::PARAM_API_KEY ] ) &&
				                    ! empty( $request_body[ self::PARAM_API_KEY ] );
				break;

			default:
				$is_valid_request = false;

		}

		return $is_valid_request;
	}

	/**
	 * Creates an instance of WP_REST_Response with error message in according to given request type
	 *
	 * @param string $request_type
	 * @param int $status_code
	 *
	 * @return WP_REST_Response
	 */
	private function create_error_rest_response( $request_type, $status_code ) {
		return new WP_REST_Response( sprintf( 'invalid %s request', $request_type ), $status_code );
	}

	/**
	 * @param mixed $data data to be injected into success rest response
	 * @param string $content_type content type of retrieved resource
	 *
	 * @return WP_REST_Response
	 */
	private function create_success_rest_response( $data, $content_type ) {
		return new WP_REST_Response( $data, WP_Http::OK, [ 'Content-Type' => $content_type ] );
	}
}