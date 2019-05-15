<?php

/**
 * Class responsible of handling configuration of contact form 7 plugin field mapping and generated
 * visitor form data responding to be sent to CoSy Address Book api
 */
class CoSy_Contact_Form_7_Handler implements CoSy_Form_Handler {
	const FORM_TYPE = 'contactForm7';

	/**
	 * Instance of WPCF7_ContactForm
	 *
	 * @var WPCF7_ContactForm
	 */
	private $wpcf7_instance;

	/**
	 * List of field types supported by current handler to be used to identify fields by name
	 *
	 * @var array
	 */
	private $field_types = array(
		'email'   => 'email',
		'phone'   => 'tel',
		'notes'   => 'textarea',
		'consent' => 'acceptance'
	);

	/**
	 * Checks if at least one instance of current form type is embedded on a page
	 *
	 * @return bool
	 */
	public function has_form_embedded_on_page() {
		$pages = get_pages();
		foreach ( $pages as $page ) {
			if ( ! empty( $page->post_content ) && has_shortcode( $page->post_content, 'contact-form-7' ) ) {
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
		$has_consent       = false;
		$wpcf7_instance_id = $this->fetch_wpcf7_instance_id( $form_data );
		$consent_field     = $this->get_field_by_type( 'consent', $wpcf7_instance_id );

		if ( ! empty( $consent_field ) ) {
			foreach ( $form_data as $field_name => $field_value ) {
				if ( ! empty( strstr( $consent_field, $field_name ) && $field_value == true ) ) {
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
		return true;
	}

	/**
	 * Retrieves user chosen fields of form to be embedded on page and synchronised to CoSy Address Book
	 *
	 * Note that a deep regular expression parsing of Contact_Form_7 form html config value stored as wp_post entry
	 * has to be done in order to extract single form fields due to lack of a generic function fulfilling this concern
	 *
	 * WPCF7_ContactForm::prop( 'form' ); currently retrieves entire string value
	 *
	 * @example form html config value used in mock file used in unit tests at /test/mockfiles/contact_form_7
	 *
	 * @return array
	 */
	public function get_user_form_fields() {
		$form_html_config = $this->get_form_html_config();

		if ( empty( $form_html_config ) ) {
			return [];
		}

		preg_match_all(
			'/\s(\w+).*\]/',
			$form_html_config,
			$field_templates
		);

		$user_form_fields = explode(
			']',
			implode( '', $field_templates[0] )
		);

		$raw_form_field_values = preg_replace( '/.*\[.*acceptance.*/', '', $user_form_fields );

		return $this->get_sanitized_value_list( $raw_form_field_values );
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
	 * Retrieves corresponding field name identified by given type in form config html value
	 *
	 * Note that a deep regular expression parsing of Contact_Form_7 form html config value stored as wp_post entry
	 * has to be done in order to extract single form field due to lack of a generic function fulfilling this concern
	 *
	 * WPCF7_ContactForm::prop( 'form' ); currently retrieves entire string value
	 *
	 * @example form html config value used in mock file used in unit tests at /test/mockfiles/contact_form_7
	 *
	 * @param string $type type of field to be retrieved (eg.: email, phone, etc)
	 * @param mixed $wpcf7_instance_id instance id of a specific form instance to fetch if explicitly required
	 *
	 * @return string
	 */
	public function get_field_by_type( $type, $wpcf7_instance_id = null ) {
		$field = '';

		if ( array_key_exists( $type, $this->field_types ) ) {
			$form_html_config = $this->get_form_html_config( $wpcf7_instance_id );
			if ( ! empty( $form_html_config ) ) {
				if ( $type == 'consent' ) {
					$field = $this->fetch_consent_field_name( $form_html_config );
				} else {
					$field = $this->fetch_field_name_by_type( $type, $form_html_config );
				}
			}
		}

		return $field;
	}

	/**
	 * Injects a new instance of WPCF7_ContactForm
	 *
	 * @param WPCF7_ContactForm $wpcf7_instance
	 */
	public function set_wpfc7_instance( WPCF7_ContactForm $wpcf7_instance ) {
		$this->wpcf7_instance = $wpcf7_instance;
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
		$wpcf7_instance_id = $this->fetch_wpcf7_instance_id( $form_data );

		$request_data = array(
			'source' => array(
				'type'        => 'CONTACTFORM',
				'description' => parse_url( get_site_url(), PHP_URL_HOST )
			)
		);

		if ( is_array( $form_data ) ) {
			foreach ( $form_data as $name => $value ) {
				$api_field_name = $this->fetch_api_field_name( $field_mapping, $name );
				if ( ! empty( $api_field_name ) && ! empty( $value ) ) {
					switch ( $api_field_name ) {
						case 'consent':
							$form_html_config        = $this->get_form_html_config( $wpcf7_instance_id );
							$request_data['consent'] = array(
								'origin'   => parse_url( get_site_url(), PHP_URL_HOST ),
								'text'     => $this->fetch_consent_field_text( $form_html_config ),
								'issuedAt' => date( DATE_ISO8601, time() )
							);
							break;

						default:
							$request_data[ $api_field_name ] = $value;
					}
				}
			}
		}

		return json_encode( $request_data );
	}

	/**
	 * Retrieves instance of WPCF7_ContactForm if found
	 *
	 * @param mixed $wpcf7_instance_id instance id of a specific form instance to fetch if explicitly required
	 *
	 * @return bool|WPCF7_ContactForm
	 */
	private function get_wpcf7_instance( $wpcf7_instance_id = null ) {
		if ( $this->wpcf7_instance instanceof WPCF7_ContactForm ) {
			return $this->wpcf7_instance;
		}

		if ( ! empty( $wpcf7_instance_id ) ) {
			return WPCF7_ContactForm::get_instance( $wpcf7_instance_id );
		}

		$args = array(
			'post_type'   => 'wpcf7_contact_form',
			'numberposts' => '1',
			'order'       => 'ASC',
		);

		$wpcf7_posts      = get_posts( $args );
		$wpcf7_first_post = array_pop( $wpcf7_posts );

		return WPCF7_ContactForm::get_instance( $wpcf7_first_post->ID );
	}

	/**
	 * Retrieves persisted form html config
	 *
	 * @param mixed $wpcf7_instance_id instance id of a specific form instance to fetch if explicitly required
	 *
	 * @return string
	 */
	private function get_form_html_config( $wpcf7_instance_id = null ) {
		$wpcf7_form_instance = $this->get_wpcf7_instance( $wpcf7_instance_id );
		if ( $wpcf7_form_instance instanceof WPCF7_ContactForm ) {
			$form_html_config = $wpcf7_form_instance->prop( 'form' );

			return ( empty( $form_html_config ) ) ? '' : $form_html_config;
		}

		return '';
	}

	/**
	 * Fetch api field name out of field mapping mathcing to given form field
	 *
	 * @param array $field_mapping
	 * @param string $form_field_name
	 *
	 * @example form html config value used in mock file used in unit tests at /test/mockfiles/contact_form_7
	 *
	 * @return string
	 */
	private function fetch_api_field_name( array $field_mapping, $form_field_name ) {
		$api_field_name = '';

		foreach ( $field_mapping as $form_field => $api_field ) {
			if ( ! empty( strstr( $form_field, $form_field_name ) ) ) {
				$api_field_name = $api_field;
				break;
			}
		}

		return $api_field_name;
	}

	/**
	 * Fetches consent field name from given form html config
	 *
	 * @param string $form_html_config
	 *
	 * @see self::get_field_by_type()
	 *
	 * @return string
	 */
	private function fetch_consent_field_name( $form_html_config ) {
		$consent_field_template_fragments = $this->fetch_consent_field_template_fragments( $form_html_config );

		if ( empty( $consent_field_template_fragments ) ) {
			return '';
		}

		return trim( array_shift( $consent_field_template_fragments ) );
	}

	/**
	 * Fetches consent field text from given form html config
	 *
	 * @param string $form_html_config
	 *
	 * @return string
	 */
	private function fetch_consent_field_text( $form_html_config ) {
		$consent_field_template_fragments = $this->fetch_consent_field_template_fragments( $form_html_config );

		if ( empty( $consent_field_template_fragments ) ) {
			return '';
		}

		return trim( str_replace( '[/acceptance', '', $consent_field_template_fragments[1] ) );
	}

	/**
	 * Retrieves consent field template fragments out of given form html config
	 *
	 * @param string $form_html_config
	 *
	 * @return array
	 */
	private function fetch_consent_field_template_fragments( $form_html_config ) {
		$consent_field_template_fragments = [];

		preg_match_all(
			'/\s(\w+).*\]/',
			$form_html_config,
			$field_templates
		);

		foreach ( $field_templates[0] as $field_template ) {
			if ( preg_match( '/\[.*acceptance.*\]/', $field_template ) ) {
				$consent_field_template_fragments = explode( ']', $field_template );
				break;
			}
		}

		return $consent_field_template_fragments;
	}

	/**
	 * Retrieves sanitized value list
	 *
	 * @param array $raw_value_list
	 *
	 * @return array
	 */
	private function get_sanitized_value_list( array $raw_value_list ) {
		$sanitized_value_list = [];

		foreach ( $raw_value_list as $raw_value ) {
			if ( ! empty( $raw_value ) ) {
				array_push( $sanitized_value_list, trim( $raw_value ) );
			}
		}

		return $sanitized_value_list;
	}

	/**
	 * Fetches a specific field name matching to given field type from given form html config
	 *
	 * @param string $type
	 * @param string $form_html_config
	 *
	 * @example form html config value used in mock file used in unit tests at /test/mockfiles/contact_form_7
	 * @see self::get_field_by_type()
	 *
	 * @return string
	 */
	private function fetch_field_name_by_type( $type, $form_html_config ) {
		$field = null;

		preg_match_all(
			'/\[.*(\w+).*\]/',
			$form_html_config,
			$field_templates
		);

		foreach ( $field_templates[0] as $field_template ) {
			$pattern = sprintf( '/^\[%s.*\]$/', $this->field_types[ $type ] );

			if ( preg_match( $pattern, $field_template ) ) {
				preg_match_all(
					'/\s(\w+).*\]/',
					$field_template,
					$field_name
				);

				$field = str_replace( [ ' ', '[', ']' ], '', $field_name[0][0] );
			}
		}

		return $field;
	}

	/**
	 * Fetches wpcf7 instance id from given data lit if found
	 *
	 * @param array $data
	 *
	 * @return mixed|null
	 */
	private function fetch_wpcf7_instance_id( array $data ) {
		return ( isset( $data['_wpcf7'] ) ) ? $data['_wpcf7'] : null;
	}
}
