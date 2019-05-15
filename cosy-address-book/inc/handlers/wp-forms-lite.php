<?php

/**
 * Class responsible of handling configuration of WPforms lite plugin field mapping and generated
 * visitor form data responding to be sent to CoSy Address Book api
 */
class CoSy_WP_Forms_Lite_Handler implements CoSy_Form_Handler {
	const FORM_TYPE = 'WpFormsLite';

	/**
	 * Checks if at least one instance of current form type is embedded on a page
	 *
	 * @return bool
	 */
	public function has_form_embedded_on_page() {
		$pages = get_pages();
		foreach ( $pages as $page ) {
			if ( ! empty( $page->post_content )
			     &&
			     ( has_shortcode( $page->post_content, 'wpforms' )
			       ||
			       $this->has_wpforms_widget( $page->post_content )
			     )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if given form data contain consent data
	 *
	 * @param array $form_data form data to be saved into CoSy Address Book
	 *
	 * @return bool
	 */
	public function has_consent( array $form_data ) {
		$has_consent = false;
		foreach ( $form_data as $form_field ) {
			if ( is_array( $form_field ) && array_key_exists( 'type', $form_field ) ) {
				if ( ( $form_field['type'] == 'gdpr-checkbox' ) && ! empty( $form_field['value'] ) ) {
					$has_consent = true;
					break;
				}
			}

		}

		return $has_consent;
	}

	/**
	 * Checks if form handler requires a specific user field mapping for synchronisation to address book api fields
	 *
	 * @return bool
	 */
	public function requires_user_field_mapping() {
		return false;
	}

	/**
	 * Retrieves user chosen fields of form to be embedded on page and synchronised to CoSy address book
	 *
	 * @return array
	 */
	public function get_user_form_fields() {
		return [];
	}

	/**
	 * Retrieves identifier of current form type
	 *
	 * @return string
	 */
	public function get_type() {
		return self::FORM_TYPE;
	}

	/**
	 * Retrieves corresponding field name identified by given type
	 *
	 * @param string $type type of field to be retrieved (eg.: email, phone, etc)
	 *
	 * @return string
	 */
	public function get_field_by_type( $type ) {
		return '';
	}

	/**
	 * Creates a request body using given form data
	 *
	 * @param array $form_data form data to be used to create request body
	 * @param array $field_mapping field mapping to identify api fields of request body
	 *
	 * @return string
	 */
	public function create_request_body( array $form_data, array $field_mapping = [] ) {
		$request_data = array(
			'source' => array(
				'type'        => 'CONTACTFORM',
				'description' => parse_url( get_site_url(), PHP_URL_HOST )
			)
		);

		if ( is_array( $form_data ) ) {
			foreach ( $form_data as $field ) {
				if ( is_array( $field ) && isset( $field['type'] ) && ! empty( $field['value'] ) ) {
					switch ( $field['type'] ) {
						case 'name':
							$request_data['firstName'] = $field['first'];
							$request_data['lastName']  = $field['last'];
							break;

						case 'email':
							$request_data['email'] = $field['value'];
							break;

						case 'phone':
							$request_data['phone'] = $field['value'];
							break;

						case 'textarea':
							$request_data['notes'] = $field['value'];
							break;

						case 'gdpr-checkbox':
							$request_data['consent'] = array(
								'origin'   => parse_url( get_site_url(), PHP_URL_HOST ),
								'text'     => $field['value'],
								'issuedAt' => date( DATE_ISO8601, time() )
							);
							break;
					}
				}
			}
		}

		return json_encode( $request_data );
	}

	/**
	 * Checks if given post content has a wpforms widget
	 *
	 * This function has been explicitly implemented due to lack of WordPress Core generic function
	 * retrieving flag confirming presence of a wpforms-widget on a page or not like done in WordPress core function.
	 *
	 * 'has_short_code($content)'
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	private function has_wpforms_widget( $content ) {
		return boolval( preg_match( '/.*wpforms\/form-selector.*formId.*:.*\d+.*/', $content ) );
	}
}