<?php
include_once './cosy-address-book/cosy-address-book.php';

class Address_Book_Rest_Client_Test extends PHPUnit_Framework_TestCase {
	/**
	 * Test for Address_Book_Rest_Client::has_subscription()
	 *
	 * @param string $end_point
	 * @param bool $is_successful
	 * @param string $message
	 *
	 * @dataProvider get_subscription_data()
	 */
	public function test_get_subscription( $end_point, $is_successful, $message ) {
		$rest_client           = new Address_Book_Rest_Client( $this->get_config( $end_point ) );
		$subscription_response = $rest_client->get_subscription( 'test-key' );

		$this->assertEquals( $is_successful, $subscription_response->is_successful() );
		$this->assertEquals( $message, $subscription_response->get_message() );
	}

	/**
	 * Test for Address_Book_Rest_Client::create_subscription()
	 *
	 * @param string $end_point
	 * @param string $domain
	 * @param bool $is_successful
	 * @param string $error_message
	 *
	 * @dataProvider get_create_subscription_data()
	 */
	public function test_create_subscription( $end_point, $domain, $is_successful, $error_message ) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config( $end_point ) );

		$this->assertEquals( $is_successful, $rest_client->create_subscription( $domain, 'test-key' ) );
		$this->assertEquals( $error_message, $rest_client->get_last_error() );
	}

	/**
	 * Test for Address_Book_Rest_Client::update_subscription()
	 *
	 * @param string $end_point
	 * @param string $current_domain
	 * @param string $new_domain
	 * @param bool $is_successful
	 * @param string $error_message
	 *
	 * @dataProvider get_update_subscription_data()
	 */
	public function test_update_subscription( $end_point, $current_domain, $new_domain, $is_successful, $error_message ) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config( $end_point ) );

		$this->assertEquals( $is_successful, $rest_client->update_subscription( $current_domain, $new_domain, 'test-key' ) );
		$this->assertEquals( $error_message, $rest_client->get_last_error() );
	}

	/**
	 * Test for Address_Book_Rest_Client::create_subscription_contacts()
	 *
	 * @param string $end_point
	 * @param string $request_body
	 * @param bool $is_successful
	 * @param string $error_message
	 *
	 * @dataProvider get_create_subscription_contact_data()
	 */
	public function test_create_subscription_contacts( $end_point, $request_body, $is_successful, $error_message ) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config( $end_point ) );

		$this->assertEquals( $is_successful, $rest_client->create_contacts( 'test-key', $request_body ) );
		$this->assertEquals( $error_message, $rest_client->get_last_error() );
	}

	/**
	 * Retrieves test data for testing Address_Book_Rest_Client::has_subscription()
	 *
	 * @see mocked middleware with different status code values
	 *
	 * @return array
	 */
	public function get_subscription_data() {
		return [
			[ 'http://cosy.wiremock.lan:8080', true, 'localhost:8080' ],
			[
				'http://cosy.wiremock.lan:8080/internal-error',
				false,
				json_encode(
					[
						'status_code'   => 500,
						'error_type'    => 'Internal Server Error',
						'error_message' => 'An error occurred while processing the request please try again later'
					]
				)
			],
			[ 'http://cosy.wiremock.lan:8080/to-be-updated', true, 'localhost:8081' ],
		];
	}

	/**
	 * Retrieves test data for testing Address_Book_Rest_Client::create_subscription()
	 *
	 * @see mocked middleware with different status code values
	 *
	 * @return array
	 */
	public function get_create_subscription_data() {
		return [
			[ 'http://cosy.wiremock.lan:8080', 'localhost:8080', true, '' ],
			[
				'http://cosy.wiremock.lan:8080/defect',
				'localhost:8080',
				false,
				json_encode(
					[
						'status_code'   => 409,
						'error_type'    => 'Conflict',
						'error_message' => 'API Key already assigned to another subscription'
					]
				)
			]
		];
	}

	/**
	 * Retrieves test data for testing Address_Book_Rest_Client::create_subscription()
	 *
	 * @see mocked middleware with different status code values
	 *
	 * @return array
	 */
	public function get_create_subscription_contact_data() {
		return [
			[
				'http://cosy.wiremock.lan:8080',
				file_get_contents( sprintf( '%s/config/mockfiles/valid_contacts.json', __DIR__ ) ),
				true,
				''
			],
			[
				'http://cosy.wiremock.lan:8080/defect',
				file_get_contents( sprintf( '%s/config/mockfiles/invalid_contacts.json', __DIR__ ) ),
				false,
				json_encode(
					[
						'status_code'   => 400,
						'error_type'    => 'Bad Request',
						'error_message' => 'Validation error on contact'
					]
				)
			]
		];
	}

	/**
	 * Retrieves test data for testing Address_Book_Rest_Client::update_subscription()
	 *
	 * @see mocked middleware with different status code values
	 *
	 * @return array
	 */
	public function get_update_subscription_data() {
		return [
			[ 'http://cosy.wiremock.lan:8080', 'localhost:8080', 'www.example.com', true, '' ],
			[
				'http://cosy.wiremock.lan:8080/defect',
				'localhost:8080',
				'www.example.com',
				false,
				json_encode(
					[
						'status_code'   => 404,
						'error_type'    => 'Not Found',
						'error_message' => 'Domain not found'
					]
				)
			]
		];
	}

	/**
	 * Retrieves an instance Address_Book_Config
	 *
	 * @param string $end_point to be use for requests
	 *
	 * @return CoSy_Address_Book_Config
	 */
	private function get_config( $end_point = 'http://cosy.wiremock.lan:8080' ) {
		$address_book_config = $this->getMockBuilder( 'CoSy_Address_Book_Config' )
		                            ->disableOriginalConstructor()
		                            ->getMock();

		$address_book_config->expects( $this->any() )
		                    ->method( 'get_api_endpoint' )
		                    ->will( $this->returnValue( $end_point ) );

		return $address_book_config;
	}
}