<?php
include_once './cosy-address-book/cosy-address-book.php';

class CoSy_Address_Book_Api_Client_Test extends PHPUnit_Framework_TestCase {
	/**
	 * @var string
	 */
	const FORM_TYPE = 'test-form';

	/**
	 * Initializes some set up paramaters
	 */
	public function setUp() {
		delete_option( CoSy_Address_Book_Api_Client::WP_OPTION_API_KEY_DATA );
		delete_option( sprintf( '%s_%s', CoSy_Address_Book_Api_Client::WP_OPTION_FORM_TYPE, self::FORM_TYPE ) );
	}

	/**
	 * Test for Address_Book_Api_Client::check_subscription_update()
	 *
	 * @param boolean $do_domain_update
	 * @param boolean $has_api_key
	 * @param boolean $is_successful_request
	 * @param boolean $is_subscribed
	 *
	 * @dataProvider get_check_subscription_update_data()
	 */
	public function test_check_subscription_update( $do_domain_update, $has_api_key, $is_successful_request, $is_subscribed ) {
		$subscription = null;

		if ( $do_domain_update ) {
			$subscription = 'cosy.domain-to-updated.lan';
		}

		if ( $has_api_key ) {
			add_option(
				CoSy_Address_Book_Api_Client::WP_OPTION_API_KEY_DATA,
				[ CoSy_Address_Book_Api_Client::OPTION_API_KEY => 'test-key' ]
			);
		}

		$api_client = new CoSy_Address_Book_Api_Client(
			$this->get_config(),
			$this->get_rest_client(
				[
					'get_subscription'    => new CoSy_Address_Book_Api_Response( $is_successful_request, $subscription ),
					'update_subscription' => $is_subscribed
				]
			)
		);

		$this->assertEquals( $is_subscribed, $api_client->check_subscription_update() );
	}

	/**
	 * Test for Address_Book_Api_Client::is_connected()
	 *
	 * @param boolean  $has_api_key
	 * @param boolean  $is_successful_request
	 * @param boolean  $is_connected
	 *
	 * @dataProvider get_is_connected_data()
	 */
	public function test_is_connected( $has_api_key, $is_successful_request, $is_connected) {
		if ( $has_api_key ) {
			add_option(
				CoSy_Address_Book_Api_Client::WP_OPTION_API_KEY_DATA,
				[ CoSy_Address_Book_Api_Client::OPTION_API_KEY => 'test-key' ]
			);
		}

		$api_client = new CoSy_Address_Book_Api_Client(
			$this->get_config(),
			$this->get_rest_client(
				[
					'get_subscription' => new CoSy_Address_Book_Api_Response( $is_successful_request )
				]
			)
		);

		$this->assertEquals( $is_connected, $api_client->is_connected() );
	}

	/**
	 * Test for Address_Book_Api_Client::display_form_settings()
	 *
	 * @param array $form_handlers
	 * @param boolean $has_displayable_form_settings
	 *
	 * @dataProvider get_display_web_form_settings()
	 */
	public function test_display_web_form_settings( array $form_handlers, $has_displayable_form_settings ) {
		$api_client = new CoSy_Address_Book_Api_Client(
			$this->get_config(),
			$this->get_rest_client( [] )
		);

		/* @var  CoSy_Form_Handler $form_handler */
		foreach ( $form_handlers as $form_handler ) {
			$api_client->add_form_handler( $form_handler );
		}

		$this->assertEquals( $has_displayable_form_settings, $api_client->has_displayable_form_settings() );
	}

	/**
	 * Test for Address_Book_Api_Client::connect_api_key()
	 *
	 * @param boolean $has_domain_change
	 * @param array $rest_mock_config
	 * @param bool $is_successful_connection
	 * @param string $error
	 *
	 * @dataProvider get_connect_api_key_data();
	 */
	public function test_connect_api_key( $has_domain_change, array $rest_mock_config, $is_successful_connection, $error ) {
		if ( $has_domain_change ) {
			add_option( CoSy_Address_Book_Api_Client::WP_OPTION_API_KEY_DATA, [ CoSy_Address_Book_Api_Client::OPTION_DOMAIN => 'test-domain' ] );
		}

		$api_client   = new CoSy_Address_Book_Api_Client( $this->get_config(), $this->get_rest_client( $rest_mock_config ) );
		$api_response = $api_client->connect_api_key( 'test-key' );

		$this->assertEquals( $is_successful_connection, $api_response->is_successful() );
		$this->assertEquals( $this->get_translation( $error ), $api_response->get_message() );
	}

	/**
	 * Test for Address_Book_Api_Client::get_field_mapping()
	 *
	 * @param boolean $is_default_field_mapping
	 * @param array $expected_field_mapping
	 *
	 * @dataProvider get_field_mapping_data()
	 */
	public function test_get_field_mapping( $is_default_field_mapping, array $expected_field_mapping ) {
		$api_client = new CoSy_Address_Book_Api_Client( $this->get_config(), $this->get_rest_client( [] ) );

		if ( $is_default_field_mapping ) {
			$api_client->set_config( $this->get_config( array_values( $expected_field_mapping ) ) );
			$api_client->add_form_handler( $this->get_form_handler( $expected_field_mapping ) );
		} else {
			add_option(
				sprintf( '%s_%s', CoSy_Address_Book_Api_Client::WP_OPTION_FORM_TYPE, self::FORM_TYPE ),
				json_encode( $expected_field_mapping )
			);
		}

		$this->assertEquals( $expected_field_mapping, $api_client->get_field_mapping( self::FORM_TYPE ) );
	}


	/**
	 * Test for Address_Book_Api_Client::save_field_mapping()
	 *
	 * @param boolean $replaces_existing_field_mapping
	 * @param boolean $is_successfully_saved
	 *
	 * @dataProvider get_save_field_mapping_data()
	 */
	public function test_save_mapping( $replaces_existing_field_mapping, $is_successfully_saved ) {
		$field_mapping = [ 'api_field_1' => 'form_field_1', 'api_field_2' => 'form_field_2' ];
		if ( $replaces_existing_field_mapping ) {
			add_option(
				sprintf( '%s_%s', CoSy_Address_Book_Api_Client::WP_OPTION_FORM_TYPE, self::FORM_TYPE ),
				json_encode( $field_mapping )
			);
		}

		$api_client   = new CoSy_Address_Book_Api_Client( $this->get_config(), $this->get_rest_client( [] ) );
		$api_response = $api_client->save_field_mapping( $field_mapping, self::FORM_TYPE );

		$this->assertEquals( $is_successfully_saved, $api_response->is_successful() );

		$this->assertEquals(
			$this->get_translation( 'cosy_address_book_field_mapping_successful' ),
			$api_response->get_message()
		);
	}

	/**
	 * Test for Address_Book_Api_Client::save_data_into_address_book()
	 *
	 * @param boolean $has_consent
	 * @param boolean $is_successfully_saved
	 * @param string $error
	 *
	 * @dataProvider  get_save_data_into_address_book_data()
	 */
	public function test_save_data_into_address_book( $has_consent, $is_successfully_saved, $error ) {
		$api_client = new CoSy_Address_Book_Api_Client(
			$this->get_config(),
			$this->get_rest_client( [ 'create_contacts' => $is_successfully_saved, 'get_last_error' => $error ] )
		);

		$api_client->add_form_handler(
			$this->get_form_handler( [], self::FORM_TYPE, $has_consent )
		);

		$api_response = $api_client->save_data_into_address_book( [], self::FORM_TYPE );

		$this->assertEquals( $is_successfully_saved, $api_response->is_successful() );
		$this->assertEquals( $error, $api_response->get_message() );
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::confirm_or_update_connection()
	 *
	 * @return array
	 */
	public function get_is_connected_data() {
		// has_api_key , $is_successful_request, $is_connected
		return [
			[ true, true, true ],
			[ true, false, false ],
			[ false, false, false ]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::check_subscription_update()
	 *
	 * @return array
	 */
	public function get_check_subscription_update_data() {
		// $do_domain_update, has_api_key , $is_successful_request, $is_subscribed
		return [
			[ true, true, true, true ],
			[ true, true, false, false ],
			[ false, true, true, true ],
			[ false, true, false, false ],
			[ false, false, false, false ]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::display_web_form_settings()
	 *
	 * @return array
	 */
	public function get_display_web_form_settings() {
		return [
			[
				[ $this->get_form_handler( [] ) ],
				true
			],
			[
				[ $this->get_form_handler( [], self::FORM_TYPE, true, false ) ],
				false
			],
			[
				[
					$this->get_form_handler( [], 'form-type-1' ),
					$this->get_form_handler( [], 'form-type-2' )
				],
				true
			],
			[
				[
					$this->get_form_handler( [], 'form-type-1', false, true ),
					$this->get_form_handler( [], 'form-type-2', false, false )
				],
				true
			],
			[
				[
					$this->get_form_handler( [], 'form-type-1', false, false ),
					$this->get_form_handler( [], 'form-type-2', false, true )
				],
				true
			],
			[
				[
					$this->get_form_handler( [], 'form-type-1', false, false ),
					$this->get_form_handler( [], 'form-type-2', false, false )
				],
				false
			]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::connect_api_key()
	 *
	 * @return array
	 */
	public function get_connect_api_key_data() {
		return [
			[ false, [ 'create_subscription' => false ], false, 'cosy_address_book_generic_error' ],
			[ false, [ 'create_subscription' => true ], true, '' ],
			[ true, [ 'create_subscription' => true, 'update_subscription' => true ], true, '' ]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::connect_api_key()
	 *
	 * @return array
	 */
	public function get_field_mapping_data() {
		return [
			[ false, [ 'api_field_1' => 'form_field_1', 'api_field_2' => 'form_field_2' ] ],
			[
				true,
				[
					'first-name'   => 'firstName',
					'last-name'    => 'lastName',
					'email-value'  => 'email',
					'phone-number' => 'phone',
					'message'      => 'notes',
					'-'            => 'consent'
				]
			]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::save_field_mapping()
	 *
	 * @return array
	 */
	public function get_save_field_mapping_data() {
		return [
			[ true, true ],
			[ false, true ]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Api_Client::save_data_into_address_book()
	 *
	 * @return array
	 */
	public function get_save_data_into_address_book_data() {
		return [
			[ true, true, '' ],
			[ true, false, 'validation error on contact data' ],
			[ false, false, '' ]
		];
	}

	/**
	 * Retrieves an instance Address_Book_Config
	 *
	 * @param array $api_fields api key to be retrieved
	 *
	 * @return CoSy_Address_Book_Config
	 */
	private function get_config( array $api_fields = [] ) {
		$address_book_config = $this->getMockBuilder( 'CoSy_Address_Book_Config' )
		                            ->disableOriginalConstructor()
		                            ->getMock();

		$address_book_config->expects( $this->any() )
		                    ->method( 'get_api_fields' )
		                    ->will( $this->returnValue( $api_fields ) );

		return $address_book_config;
	}

	/**
	 * Retrieves an instance Rest_Client
	 *
	 * @param array $mock_config key -> value pair of mocked methods with retrieved mocked result
	 *
	 * @return Address_Book_Rest_Client
	 */
	private function get_rest_client( array $mock_config ) {
		$rest_client = $this->getMockBuilder( 'Address_Book_Rest_Client' )
		                    ->disableOriginalConstructor()
		                    ->getMock();

		foreach ( $mock_config as $method_name => $result ) {
			$rest_client->expects( $this->any() )
			            ->method( $method_name )
			            ->will( $this->returnValue( $result ) );
		}


		return $rest_client;
	}

	/**
	 * Retrieves an instance of Form_Handler
	 *
	 * @param array $field_mapping field mapping to be used
	 * @param string $form_type form type to be used
	 * @param boolean $has_consent flag determining if consent is activated or not
	 * @param boolean $has_form_embedded_on_page flag determining if web form is embedded on an page or not
	 *
	 * @return CoSy_Form_Handler
	 */
	private function get_form_handler(
		array $field_mapping,
		$form_type = self::FORM_TYPE,
		$has_consent = true,
		$has_form_embedded_on_page = true
	) {
		$form_handler = $this->getMockBuilder( 'CoSy_Form_Handler' )
		                     ->disableOriginalConstructor()
		                     ->getMock();

		$form_handler->expects( $this->any() )
		             ->method( 'get_field_by_type' )
		             ->will(
			             $this->returnCallback( function ( $api_field ) use ( $field_mapping ) {
				             return ( in_array( $api_field, $field_mapping ) ) ? array_search( $api_field, $field_mapping ) : 'x';
			             } )
		             );

		$form_handler->expects( $this->any() )
		             ->method( 'get_type' )
		             ->will(
			             $this->returnValue( $form_type )
		             );

		$form_handler->expects( $this->any() )
		             ->method( 'has_consent' )
		             ->will(
			             $this->returnValue( $has_consent )
		             );

		$form_handler->expects( $this->any() )
		             ->method( 'has_form_embedded_on_page' )
		             ->will(
			             $this->returnValue( $has_form_embedded_on_page )
		             );


		return $form_handler;
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