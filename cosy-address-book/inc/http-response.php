<?php

/**
 * Class responsible of wrapping Wordpress http response data
 */
class CoSy_Address_Book_Http_Response {
	/**
	 * @var $data
	 */
	private $data;

	/**
	 * Retrieves an instance of Address_Book_Http_Response
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * Checks if $response is an http error
	 *
	 * @return bool
	 */
	public function is_error() {
		return $this->get_status_code() != WP_Http::OK;
	}

	/**
	 * Retrieves http error built out of response message and body
	 *
	 * @return string
	 */
	public function get_error() {
		return json_encode(
			[
				'status_code'   => $this->get_status_code(),
				'error_type'    => $this->get_message(),
				'error_message' => $this->get_body()
			]
		);
	}

	/**
	 * Retrieves http status code of found current response
	 *
	 * @return int
	 */
	public function get_status_code() {
		if ( isset( $this->data['response']['code'] ) ) {
			return $this->data['response']['code'];
		}

		return 0;
	}

	/**
	 * Retrieves http response message of found current response
	 *
	 * @return string
	 */
	public function get_message() {
		if ( isset( $this->data['response']['message'] ) ) {
			return $this->data['response']['message'];
		}

		return '';
	}

	/**
	 * Retrieves http response body of found current response
	 *
	 * @return string
	 */
	public function get_body() {
		if ( isset( $this->data['body'] ) ) {
			return $this->data['body'];
		}

		return '';
	}
}