<?php

/**
 * This class is responsible of encapsulating result of api requests status and potential error messages
 */
class CoSy_Address_Book_Api_Response {
	/**
	 * @var bool
	 */
	private $is_successful;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * Creates an instance of Address_Book_Api_Response
	 *
	 * @param bool $is_successful
	 * @param string $message
	 */
	public function __construct( $is_successful = false, $message = '' ) {
		$this->is_successful = $is_successful;
		$this->message       = $message;
	}

	/**
	 * @return bool
	 */
	public function is_successful() {
		return $this->is_successful;
	}

	/**
	 * @param bool $is_successful
	 */
	public function set_is_successful( $is_successful ) {
		$this->is_successful = $is_successful;
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Retrieves a json string representation of current api response instance
	 *
	 * @return string
	 */
	public function to_json_string() {
		return json_encode( $this->to_array() );
	}

	/**
	 * Retrieves a array representation of current api response instance
	 *
	 * @return array
	 */
	public function to_array() {
		return [ 'is_successful' => $this->is_successful, 'message' => $this->message ];
	}
}