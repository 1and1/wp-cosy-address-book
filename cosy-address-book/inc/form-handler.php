<?php

/**
 * This interface defines business logic and behaviour facade of form handler classes working in adapter approach
 *
 * Class implementing this interface might differ from each other depending on which WordPress Form Plugin they rely on
 */
interface CoSy_Form_Handler {
	/**
	 * Checks if at least one instance of current form type is embedded on a page
	 *
	 * @return bool
	 */
	public function has_form_embedded_on_page();

	/**
	 * Checks if given form data contain consent data
	 *
	 * @param array $form_data form data to be saved into CoSy Address Book
	 *
	 * @return bool
	 */
	public function has_consent( array $form_data );

	/**
	 * Checks if form handler requires a specific user field mapping for synchronisation to address book api fields
	 *
	 * @return bool
	 */
	public function requires_user_field_mapping();

	/**
	 * Retrieves user chosen fields of form to be embedded on page and synchronised to cosy address book
	 *
	 * @return array
	 */
	public function get_user_form_fields();

	/**
	 * Retrieves corresponding field name identified by given type
	 *
	 * @param string $type type of field to be retrieved (eg.: email, phone, etc)
	 *
	 * @return string
	 */
	public function get_field_by_type( $type );

	/**
	 * Retrieves identifier of current form type
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Creates a request body using given form data
	 *
	 * @param array $form_data form data to be used to create request body
	 * @param array $field_mapping field mapping to identify api fields of request body
	 *
	 * @return string
	 */
	public function create_request_body( array $form_data, array $field_mapping = [] );
}