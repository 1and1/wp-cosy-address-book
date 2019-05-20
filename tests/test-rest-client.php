<?php
include_once './cosy-address-book/cosy-address-book.php';

class Address_Book_Rest_Client_Test extends PHPUnit_Framework_TestCase {

	/**
	 * Mock http response type to be consumed by 'pre_http_request' wordpress filter
	 *
	 * @var string
	 */
	protected $response_type;

	/**
	 * Triggers parent setup mechanism and initializes local instance variables
	 */
	public function setUp() {
		parent::setUp();
		$this->response_type = '';
	}

	/**
	 * Triggers parent tear down mechanism and unset local instance variables
	 */
	public function tearDown() {
		parent::tearDown();
		unset( $this->response_type );
	}

	/**
	 * Test for Address_Book_Rest_Client::has_subscription()
	 *
	 * @param string $response_type
	 * @param bool $is_successful
	 * @param string $message
	 *
	 * @dataProvider get_subscription_data()
	 */
	public function test_get_subscription( $response_type, $is_successful, $message ) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config() );

		$this->set_response_type( $response_type );
		add_filter( 'pre_http_request', array( $this, 'send_http_response' ) );

		$subscription_response = $rest_client->get_subscription( 'test-key' );

		$this->assertEquals( $is_successful, $subscription_response->is_successful() );
		$this->assertEquals( $message, $subscription_response->get_message() );
	}

	/**
	 * Test for Address_Book_Rest_Client::create_subscription()
	 *
	 * @param string $response_type
	 * @param string $domain
	 * @param bool $is_successful
	 * @param string $error_message
	 *
	 * @dataProvider get_create_subscription_data()
	 */
	public function test_create_subscription( $response_type, $domain, $is_successful, $error_message ) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config() );

		$this->set_response_type( $response_type );
		add_filter( 'pre_http_request', array( $this, 'send_http_response' ) );

		$this->assertEquals( $is_successful, $rest_client->create_subscription( $domain, 'test-key' ) );
		$this->assertEquals( $error_message, $rest_client->get_last_error() );
	}

	/**
	 * Test for Address_Book_Rest_Client::update_subscription()
	 *
	 * @param string $response_type
	 * @param string $current_domain
	 * @param string $new_domain
	 * @param bool $is_successful
	 * @param string $error_message
	 *
	 * @dataProvider get_update_subscription_data()
	 */
	public function test_update_subscription(
		$response_type,
		$current_domain,
		$new_domain,
		$is_successful,
		$error_message
	) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config() );

		$this->set_response_type( $response_type );
		add_filter( 'pre_http_request', array( $this, 'send_http_response' ) );

		$this->assertEquals( $is_successful,
			$rest_client->update_subscription( $current_domain, $new_domain, 'test-key' ) );
		$this->assertEquals( $error_message, $rest_client->get_last_error() );
	}

	/**
	 * Test for Address_Book_Rest_Client::create_subscription_contacts()
	 *
	 * @param string $response_type
	 * @param string $request_body
	 * @param bool $is_successful
	 * @param string $error_message
	 *
	 * @dataProvider get_create_subscription_contact_data()
	 */
	public function test_create_subscription_contacts( $response_type, $request_body, $is_successful, $error_message ) {
		$rest_client = new Address_Book_Rest_Client( $this->get_config() );

		$this->set_response_type( $response_type );
		add_filter( 'pre_http_request', array( $this, 'send_http_response' ) );

		$this->assertEquals( $is_successful, $rest_client->create_contacts( 'test-key', $request_body ) );
		$this->assertEquals( $error_message, $rest_client->get_last_error() );
		$this->assertEquals( $is_successful, $this->is_valid_contact_creation_request( $request_body ) );
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
			[ 'successful_get_subscription', true, 'localhost:8080' ],
			[
				'internal_error_get_subscription',
				false,
				json_encode(
					[
						'status_code'   => 500,
						'error_type'    => 'Internal Server Error',
						'error_message' => 'An error occurred while processing the request please try again later'
					]
				)
			],
			[ 'successful_get_subscription_to_update', true, 'localhost:8081' ]
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
			[ 'successful_create_subscription', 'localhost:8080', true, '' ],
			[
				'defect_create_subscription',
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
				'successful_create_subscription_contacts',
				file_get_contents( sprintf( '%s/config/mockfiles/valid_contacts.json', __DIR__ ) ),
				true,
				''
			],
			[
				'defect_create_subscription_contacts',
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
			[ 'successful_update_subscription', 'localhost:8080', 'www.example.com', true, '' ],
			[
				'defect_update_subscription',
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
	 * Retrieves a mocked data set of http response to be consumed by 'pre_http_request' filter
	 *
	 * Note that response body will depend on value of response_type injected via data provider values
	 *
	 * @return array
	 */
	public function send_http_response() {
		$response = $this->get_http_response();

		return [
			'headers'  => $this->get_http_response_headers(),
			'body'     => $this->get_http_response_body(),
			'response' => $response
		];
	}

	/**
	 * Sets mock http response type to a new value
	 *
	 * @param string $response_type
	 */
	protected function set_response_type( $response_type ) {
		$this->response_type = $response_type;
	}

	/**
	 * Retrieves current value of mock http response type
	 *
	 * @return string
	 */
	protected function get_response_type() {
		return $this->response_type;
	}

	/**
	 * Retrieves mocked http response based on given response type
	 *
	 * @param string $response_type
	 *
	 * @return mixed
	 */
	private function get_http_response() {
		return json_decode(
			file_get_contents(
				sprintf( '%s/config/mockfiles/http/%s.json', __DIR__, $this->get_response_type() )
			),
			true
		);
	}

	/**
	 * Retrieves http response body extracted out of mocked http response
	 *
	 * @return mixed
	 */
	private function get_http_response_body() {
		$response = $this->get_http_response();

		if ( isset( $response['body'] ) && is_array( $response['body'] ) ) {
			return json_encode( $response['body'] );
		}

		return $response['body'];
	}

	/**
	 * Retrieves mock http response headers
	 *
	 * @return array
	 */
	private function get_http_response_headers() {
		return [
			'server'       => 'cosy-mock-http-server/1.0.0',
			'date'         => 'Fri, 17 May 2019 14:30:08 GMT',
			'content-type' => 'application/vnd.ionos.product.integration.cosy.subscription-v1+json',
			'link'         => 'http://cosy-mock-http-server/response'
		];
	}

	/**
	 * Checks if currently given request body is the expected valid one
	 *
	 * @param string $request_body
	 *
	 * @return boolean
	 */
	private function is_valid_contact_creation_request( $request_body ) {
		return empty(
		strcasecmp(
			$request_body,
			file_get_contents( sprintf( '%s/config/mockfiles/valid_contacts.json', __DIR__ ) )
		)
		);
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