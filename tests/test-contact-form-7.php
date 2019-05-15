<?php
include_once './cosy-address-book/cosy-address-book.php';
include_once './cosy-address-book/inc/handlers/contact-form-7.php';
include_once 'helper/page_creator.php';

class CoSy_Contact_Form_7_Test extends PHPUnit_Framework_TestCase {
	use Page_Creator;

	/**
	 * Test for Contact_Form_7::has_form_embedded_on_page()
	 *
	 * @param $has_page
	 * @param $page_content
	 * @param $has_form_embedded_on_page
	 *
	 * @dataProvider get_has_form_embedded_on_page_data()
	 */
	public function test_has_form_embedded_on_page( $has_page, $page_content, $has_form_embedded_on_page ) {

		$post_id = 0;

		if ( $has_page ) {
			$post_id = $this->create_page( $page_content );
			add_shortcode( 'contact-form-7', function () {
				return true;
			} );
		}

		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$this->assertEquals( $has_form_embedded_on_page, $contact_form_7->has_form_embedded_on_page() );

		if ( ! empty( $post_id ) ) {
			wp_delete_post( $post_id );
		}
	}

	/**
	 * Test for Contact_Form_7::has_consent()
	 *
	 * @param boolean $has_consent_in_form_config
	 * @param array $form_data
	 * @param boolean $has_consent
	 *
	 * @see /config/mockfiles/contact_form_7
	 *
	 * @dataProvider get_has_consent_data()
	 */
	public function test_has_consent( $has_consent_in_form_config, array $form_data, $has_consent ) {
		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$contact_form_7->set_wpfc7_instance( $this->get_wpfc7_instance( $has_consent_in_form_config ) );
		$this->assertEquals( $has_consent, $contact_form_7->has_consent( $form_data ) );
	}

	/**
	 * Test for Contact_Form_7::requires_user_field_mapping()
	 */
	public function test_requires_user_field_mapping() {
		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$this->assertTrue( $contact_form_7->requires_user_field_mapping() );
	}

	/**
	 * Test for Contact_Form_7::get_user_form_fields()
	 *
	 * @param boolean $has_consent_in_form_config
	 * @param array $user_form_fields
	 *
	 * @see /config/mockfiles/contact_form_7
	 *
	 * @dataProvider get_user_form_fields_data()
	 */
	public function test_get_user_form_fields( $has_consent_in_form_config, array $user_form_fields ) {
		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$contact_form_7->set_wpfc7_instance( $this->get_wpfc7_instance( $has_consent_in_form_config ) );
		$this->assertEquals( $user_form_fields, $contact_form_7->get_user_form_fields() );
	}

	/**
	 * Test for Contact_Form_7::get_type()
	 */
	public function test_get_type() {
		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$this->assertEquals( CoSy_Contact_Form_7_Handler::FORM_TYPE, $contact_form_7->get_type() );
	}

	/**
	 * Test for Contact_Form_7::get_field_by_type()
	 *
	 * @param string $expected_field
	 * @param string $field_type
	 *
	 * @see /config/mockfiles/contact_form_7
	 *
	 * @dataProvider get_field_by_type_data()
	 */
	public function test_get_field_by_type( $expected_field, $field_type ) {
		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$contact_form_7->set_wpfc7_instance( $this->get_wpfc7_instance() );
		$this->assertEquals( $expected_field, $contact_form_7->get_field_by_type( $field_type ) );
	}

	/**
	 * Test for Contact_Form_7::create_request_body()
	 *
	 * @param array $form_data
	 * @param array $field_mapping
	 * @param array $expected_value
	 *
	 * @see /config/mockfiles/contact_form_7
	 *
	 * @dataProvider get_create_request_body_data()
	 */
	public function test_create_request_body( array $form_data, array $field_mapping, array $expected_value ) {
		$contact_form_7 = new CoSy_Contact_Form_7_Handler();
		$contact_form_7->set_wpfc7_instance( $this->get_wpfc7_instance() );

		$request_body = $contact_form_7->create_request_body( $form_data, $field_mapping );
		$request_data = json_decode( $request_body, true );

		//remove consent issuedAt to avoid time race conditions in comparison
		unset( $request_data['consent']['issuedAt'] );

		$this->assertEquals( $expected_value, $request_data );
	}

	/**
	 * Retrieves data for testing Contact_Form_7::has_form_embedded_on_page()
	 *
	 * @return array
	 */
	public function get_has_form_embedded_on_page_data() {
		return [
			[ true, '[contact-form-7 id="1" title="Contact form 1"]', true ],
			[ true, 'this just any content', false ],
			[ true, '[any-short-code id="1" title="anything else"]', false ],
			[ true, '', false ],
			[ false, '[contact-form-7 id="1" title="Contact form 1"]', false ]
		];
	}

	/**
	 * Retrieves data for testing Contact_Form_7::has_consent()
	 *
	 * @return array
	 */
	public function get_has_consent_data() {
		return [
			[ true, [ 'your-email' => 'test@cosy.ionos.com', 'acceptance-179' => true ], true ],
			[ true, [ 'your-email' => 'test@cosy.ionos.com', 'acceptance-179' => false ], false ],
			[ true, [ 'your-email' => 'test@cosy.ionos.com' ], false ],
			[ false, [ 'your-email' => 'test@cosy.ionos.com' ], false ]
		];
	}

	/**
	 * Retrieves data for testing Contact_Form_7::get_user_form_fields()
	 *
	 * @return array
	 */
	public function get_user_form_fields_data() {
		return [
			[
				true,
				[ 'your-name', 'your-email', 'your-phone', 'your-subject', 'your-message', 'acceptance-179 optional' ]
			],
			[ false, [ 'your-name', 'your-email', 'your-phone', 'your-subject', 'your-message' ] ]
		];
	}

	/**
	 * Retrieves data for testing Contact_Form_7::get_field_by_type()
	 *
	 * @return array
	 */
	public function get_field_by_type_data() {
		return [
			[ 'your-email', 'email' ],
			[ 'your-phone', 'phone' ],
			[ 'your-message', 'notes' ],
			[ 'acceptance-179 optional', 'consent' ]
		];
	}

	/**
	 * Retrieves data for testing Contact_Form_7::create_request_body()
	 *
	 * @return array
	 */
	public function get_create_request_body_data() {
		$form_data_1 = [
			'your-name'      => 'CoSy Tester',
			'your-email'     => 'tester@cosy.ionos.com',
			'your-phone'     => '000114558963',
			'your-message'   => 'testing cosy contact form 7 handler',
			'acceptance-179' => true
		];

		$form_data_2 = [
			'your-name'      => '',
			'your-email'     => 'tester@cosy.ionos.com',
			'your-phone'     => '',
			'your-message'   => 'testing cosy contact form 7 handler',
			'acceptance-179' => true
		];

		$field_mapping = [
			'xxxxyour-namexxxxx'     => 'firstName',
			'xxxxyour-emailxxxx'     => 'email',
			'xxxxyour-phonexxxx'     => 'phone',
			'xxxxyour-messagexxx'    => 'notes',
			'xxxxacceptance-179xxxx' => 'consent'
		];

		$expected_value_1 = [
			'source'    => [
				'type'        => 'CONTACTFORM',
				'description' => 'example.org'
			],
			'email'     => 'tester@cosy.ionos.com',
			'phone'     => '000114558963',
			'firstName' => 'CoSy Tester',
			'notes'     => 'testing cosy contact form 7 handler',
			'consent'   => [
				'origin' => 'example.org',
				'text'   => 'I agree, that my data will be stored into IONOS Address Book'
			]
		];

		$expected_value_2 = [
			'source'  => [
				'type'        => 'CONTACTFORM',
				'description' => 'example.org'
			],
			'email'   => 'tester@cosy.ionos.com',
			'notes'   => 'testing cosy contact form 7 handler',
			'consent' => [
				'origin' => 'example.org',
				'text'   => 'I agree, that my data will be stored into IONOS Address Book'
			]
		];

		return [
			[ $form_data_1, $field_mapping, $expected_value_1 ],
			[ $form_data_2, $field_mapping, $expected_value_2 ]
		];
	}

	/**
	 * Retrieves an instance of WPCF7_ContactForm
	 *
	 * @param boolean $has_consent
	 *
	 * @return WPCF7_ContactForm
	 */
	private function get_wpfc7_instance( $has_consent = true ) {
		$wpfc7_instance = $this->getMockBuilder( 'WPCF7_ContactForm' )
		                       ->disableOriginalConstructor()
		                       ->setMethods( [ 'prop' ] )
		                       ->getMock();

		$wpfc7_instance->expects( $this->once() )
		               ->method( 'prop' )
		               ->will(
			               $this->returnCallback( function ( $prop_key ) use ( $has_consent ) {
				               if ( $prop_key === 'form' ) {
					               $file_name_suffix = ( $has_consent ) ? 'with_consent' : 'without_consent';

					               $file_name = sprintf(
						               '%s/config/mockfiles/contact_form_7/html_form_config_%s.txt',
						               __DIR__,
						               $file_name_suffix
					               );

					               return file_get_contents( $file_name );
				               }

				               return '';
			               } )
		               );

		return $wpfc7_instance;
	}
}
