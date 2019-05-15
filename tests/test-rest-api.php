<?php
include_once './cosy-address-book/cosy-address-book.php';

class Address_Book_Rest_Api_Test extends PHPUnit_Framework_TestCase {
	/**
	 * Test for Address_Book_Rest_Api::validate_form_type()
	 *
	 * @param array $supported_plugins
	 * @param string $form_type
	 * @param boolean $is_valid
	 *
	 * @dataProvider get_validate_form_type_data()
	 */
	public function test_validate_form_type( array $supported_plugins, $form_type, $is_valid ) {
		$rest_api = new CoSy_Address_Book_Rest_Api(
			$this->get_api_client(),
			$this->get_config( $supported_plugins )
		);

		$this->assertEquals( $is_valid, $rest_api->validate_form_type( $form_type ) );
	}

	/**
	 * Test for Address_Book_Rest_Api::get_field_mapping()
	 */
	public function test_get_field_mapping() {
		$field_mapping = [ 'field_1' => 'api_field_1', 'field_2' => 'api_field_2' ];

		$rest_api = new CoSy_Address_Book_Rest_Api(
			$this->get_api_client( $field_mapping ),
			$this->get_config()
		);

		$expected_wp_rest_response = new WP_REST_Response(
			$field_mapping,
			WP_Http::OK,
			[ 'Content-Type' => CoSy_Address_Book_Rest_Api::CONTENT_TYPE_FIELD_MAPPING ]
		);

		$this->assertEquals( $expected_wp_rest_response, $rest_api->get_field_mapping( new WP_REST_Request() ) );
	}

	/**
	 * Test for Address_Book_Rest_Api::save_field_mapping()
	 *
	 * @param array $request_data
	 * @param mixed $response_data
	 * @param string $content_type
	 * @param int $status_code
	 *
	 * @dataProvider get_save_field_mapping_data()
	 */
	public function test_save_field_mapping( array $request_data, $response_data, $content_type, $status_code ) {
		$expected_wp_response = new WP_REST_Response( $response_data, $status_code );

		$rest_api = new CoSy_Address_Book_Rest_Api(
			$this->get_api_client(),
			$this->get_config()
		);

		$request = new WP_REST_Request();
		$request->set_body( json_encode( $request_data ) );
		$request->set_header( 'Content-Type', $content_type );

		if ( $status_code == WP_Http::OK ) {
			$expected_wp_response->set_headers( [ 'Content-Type' => $content_type ] );
		}

		$this->assertEquals( $expected_wp_response, $rest_api->save_field_mapping( $request ) );
	}

	/**
	 * Test for Address_Book_Rest_Api::connect_api_key()
	 *
	 * @param array $request_data
	 * @param mixed $response_data
	 * @param string $request_content_type
	 * @param string $response_content_type
	 * @param int $status_code
	 *
	 * @dataProvider get_connect_api_key_data()
	 */
	public function test_connect_api_key(
		array $request_data,
		$response_data,
		$request_content_type,
		$response_content_type,
		$status_code
	) {
		$expected_wp_response = new WP_REST_Response( $response_data, $status_code );

		$rest_api = new CoSy_Address_Book_Rest_Api(
			$this->get_api_client(),
			$this->get_config()
		);

		$request = new WP_REST_Request();
		$request->set_body( json_encode( $request_data ) );
		$request->set_header( 'Content-Type', $request_content_type );

		if ( $status_code == WP_Http::OK ) {
			$expected_wp_response->set_headers( [ 'Content-Type' => $response_content_type ] );
		}

		$this->assertEquals( $expected_wp_response, $rest_api->connect_api_key( $request ) );
	}

	/**
	 * Retrieves data for testing Address_Book_Rest_Api::validate_form_type()
	 *
	 * @return array
	 */
	public function get_validate_form_type_data() {
		return [
			[ [], 'FormTypeOne', false ],
			[ [ 'FormTypeOne' ], 'FormTypeTwo', false ],
			[ [ 'FormTypeOne', 'FormTypeTwo' ], 'FormTypeThree', false ],
			[ [ 'FormTypeOne' ], 'FormTypeOne', true ],
			[ [ 'FormTypeOne', 'FormTypeTwo' ], 'FormTypeOne', true ],
			[ [ 'FormTypeOne', 'FormTypeTwo' ], 'FormTypeTwo', true ],
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Rest_Api::save_field_mapping_data()
	 *
	 * @return array
	 */
	public function get_save_field_mapping_data() {
		return [
			[
				[ CoSy_Address_Book_Rest_Api::PARAM_FIELD_MAPPING => [ 'field_1' => 'api_field_1' ] ],
				[ 'is_successful' => true, 'message' => '' ],
				CoSy_Address_Book_Rest_Api::CONTENT_TYPE_FIELD_MAPPING,
				200
			],
			[
				[ CoSy_Address_Book_Rest_Api::PARAM_FIELD_MAPPING => json_encode( [ 'field_1' => 'api_field_1' ] ) ],
				[ 'is_successful' => true, 'message' => '' ],
				CoSy_Address_Book_Rest_Api::CONTENT_TYPE_FIELD_MAPPING,
				200
			],
			[ [], 'invalid save field mapping request', 'garbage content type', 400 ],
			[ [], 'invalid save field mapping request', CoSy_Address_Book_Rest_Api::CONTENT_TYPE_FIELD_MAPPING, 400 ]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Rest_Api::connect_api_key()
	 *
	 * @return array
	 */
	public function get_connect_api_key_data() {
		return [
			[
				[ CoSy_Address_Book_Rest_Api::PARAM_API_KEY => '37E5NicvOeVeCAThvgrJybVp0EEdfxLTS3bpv/HxzZD3qf7W9lY0ag.1' ],
				[ 'is_successful' => true, 'message' => '' ],
				CoSy_Address_Book_Rest_Api::CONTENT_TYPE_API_KEY,
				CoSy_Address_Book_Rest_Api::CONTENT_TYPE_SUCCESS,
				200
			],
			[ [], 'invalid api key connection request', 'garbage content type', null, 400 ],
			[ [], 'invalid api key connection request', CoSy_Address_Book_Rest_Api::CONTENT_TYPE_API_KEY, null, 400 ],
			[
				[ CoSy_Address_Book_Rest_Api::PARAM_API_KEY => null ],
				'invalid api key connection request',
				CoSy_Address_Book_Rest_Api::CONTENT_TYPE_API_KEY,
				null,
				400
			],
			[
				[ CoSy_Address_Book_Rest_Api::PARAM_API_KEY => '' ],
				'invalid api key connection request',
				CoSy_Address_Book_Rest_Api::CONTENT_TYPE_API_KEY,
				null,
				400
			]
		];
	}

	/**
	 * Retrieves an instance Address_Book_Config
	 *
	 * @param array $api_fields api key to be retrieved
	 *
	 * @return CoSy_Address_Book_Config
	 */
	private function get_config( array $supported_plugin_types = [] ) {
		$address_book_config = $this->getMockBuilder( 'CoSy_Address_Book_Config' )
		                            ->disableOriginalConstructor()
		                            ->getMock();

		$address_book_config->expects( $this->any() )
		                    ->method( 'get_supported_plugin_types' )
		                    ->will( $this->returnValue( $supported_plugin_types ) );

		return $address_book_config;
	}

	/**
	 * Retrieves an instance of Address_Book_Api_Client according to given parameters
	 *
	 * @param string $message
	 *
	 * @return CoSy_Address_Book_Api_Client
	 */
	private function get_api_client( array $field_mapping = [] ) {
		$address_book_api_client = $this->getMockBuilder( 'CoSy_Address_Book_Api_Client' )
		                                ->disableOriginalConstructor()
		                                ->getMock();

		$address_book_api_client->expects( $this->any() )
		                        ->method( 'get_field_mapping' )
		                        ->will( $this->returnValue( $field_mapping ) );

		$address_book_api_client->expects( $this->any() )
		                        ->method( 'save_field_mapping' )
		                        ->will(
			                        $this->returnValue(
				                        new CoSy_Address_Book_Api_Response( true )
			                        )
		                        );

		$address_book_api_client->expects( $this->any() )
		                        ->method( 'connect_api_key' )
		                        ->will(
			                        $this->returnValue(
				                        new CoSy_Address_Book_Api_Response( true )
			                        )
		                        );

		return $address_book_api_client;
	}
}