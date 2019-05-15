<?php
include_once './cosy-address-book/cosy-address-book.php';
include_once './cosy-address-book/inc/handlers/wp-forms-lite.php';
include_once 'helper/page_creator.php';

class WP_Forms_Lite_Test extends PHPUnit_Framework_TestCase {
	use Page_Creator;

	/**
	 * Test for WP_Forms_Lite::has_form_embedded_on_page()
	 *
	 * @param $has_page
	 * @param $has_short_code
	 * @param $page_content
	 * @param $has_form_embedded_on_page
	 *
	 * @dataProvider get_has_form_embedded_on_page_data()
	 */
	public function test_has_form_embedded_on_page( $has_page, $has_short_code, $page_content, $has_form_embedded_on_page ) {
		$post_id = 0;

		if ( $has_page ) {
			$post_id = $this->create_page( $page_content );
			if ( $has_short_code ) {
				add_shortcode( 'wpforms', function () {
					return true;
				} );
			}
		}

		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();
		$this->assertEquals( $has_form_embedded_on_page, $wp_forms_lite->has_form_embedded_on_page() );

		if ( ! empty( $post_id ) ) {
			wp_delete_post( $post_id );
		}
	}

	/**
	 * Test for WP_Forms_Lite::has_consent()
	 *
	 * @param array $form_data
	 * @param boolean $has_consent
	 *
	 * @dataProvider get_has_consent_data()
	 */
	public function test_has_consent( array $form_data, $has_consent ) {
		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();
		$this->assertEquals( $has_consent, $wp_forms_lite->has_consent( $form_data ) );
	}

	/**
	 * Test for WP_Forms_Lite::requires_user_field_mapping()
	 */
	public function test_requires_user_field_mapping() {
		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();
		$this->assertFalse( $wp_forms_lite->requires_user_field_mapping() );
	}

	/**
	 * Test for WP_Forms_Lite::get_user_form_fields()
	 */
	public function test_get_user_form_fields() {
		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();
		$this->assertEmpty( $wp_forms_lite->get_user_form_fields() );
	}

	/**
	 * Test for WP_Forms_Lite::get_type()
	 */
	public function test_get_type() {
		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();
		$this->assertEquals( CoSy_WP_Forms_Lite_Handler::FORM_TYPE, $wp_forms_lite->get_type() );
	}

	/**
	 * Test for WP_Forms_Lite::get_field_by_type()
	 */
	public function test_get_field_by_type() {
		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();
		$this->assertEmpty( $wp_forms_lite->get_field_by_type( 'consent' ) );
	}

	/**
	 * Test for WP_Forms_Lite::create_request_body()
	 *
	 * @param array $form_data
	 * @param array $expected_value
	 *
	 * @dataProvider get_create_request_body_data()
	 */
	public function test_create_request_body( array $form_data, array $expected_value ) {
		$wp_forms_lite = new CoSy_WP_Forms_Lite_Handler();

		$request_body = $wp_forms_lite->create_request_body( $form_data );
		$request_data = json_decode( $request_body, true );

		//remove consent issuedAt to avoid time race conditions in comparison
		unset( $request_data['consent']['issuedAt'] );

		$this->assertEquals( $expected_value, $request_data );
	}

	/**
	 * Retrieves data for testing WP_Forms_Lite::has_form_embedded_on_page()
	 *
	 * @return array
	 */
	public function get_has_form_embedded_on_page_data() {

		$pageContent = "
			<!-- wp:table -->
				<table class=\"wp-block-table\">
					<tbody>
						<tr>
							<td>
								<a href=\"http://hiphoptimemachine.me/wp-admin/admin.php?page=wpforms-builder&amp;view=fields&amp;form_id=14\">B</a>
							</td>
							<td>Kontaktiere uns mit dem WP-Form:<br></td>
						</tr>
					</tbody>
				</table>
			<!-- /wp:table -->			
			<!-- wp:wpforms/form-selector {\"formId\":\"14\"} /-->			
			<!-- wp:shortcode -->			
			<!-- /wp:shortcode -->			
			<!-- wp:paragraph -->
				<p></p>
			<!-- /wp:paragraph -->
		";

		return [
			[ true, true, '[wpforms id="12"]', true ],
			[ true, true, '[any-short-code id="1" title="anything else"]', false ],
			[ true, false, 'this just any content', false ],
			[ true, false, '', false ],
			[ true, false, '<!-- wp:wpforms/form-selector {"formId":"garbage"} /-->', false ],
			[ true, false, $pageContent, true ],
			[ false, true, '[wpforms id="12"]', false ],
			[ false, false, '<!-- wp:wpforms/form-selector {"formId":"garbage"} /-->', false ],
			[ false, false, '<!-- wp:wpforms/form-selector {"formId":"11"} /-->', false ],
			[ false, false, '', false ]
		];
	}

	/**
	 * Retrieves data for testing WP_Forms_Lite::has_consent()
	 *
	 * @return array
	 */
	public function get_has_consent_data() {
		return [
			[
				[
					[
						'type'  => 'gdpr-checkbox',
						'value' => 'I agree, that my data will be stored into CoSy Address Book'
					]
				],
				true
			],
			[ [ [ 'type' => 'gdpr-checkbox', 'value' => '' ] ], false ],
			[ [ [ 'type' => 'gdpr-checkbox', 'value' => null ] ], false ],
			[ [ [ 'type' => 'garbage', 'value' => 'garbage' ] ], false ],
			[ [ 'garbage_one', 'garbage_two', 'garbage_three' ], false ],
			[ [ [ 'key_one' => 'value_one' ], [ 'key_two' => 'value_two' ] ], false ]
		];
	}

	/**
	 * Retrieves data for testing WP_Forms_Lite::create_request_body()
	 *
	 * @return array
	 */
	public function get_create_request_body_data() {
		$form_data_1 = [
			[ 'type' => 'name', 'value' => 'CoSy Tester', 'first' => 'CoSy', 'last' => 'Tester' ],
			[ 'type' => 'email', 'value' => 'tester@cosy.ionos.com' ],
			[ 'type' => 'phone', 'value' => '000114558963' ],
			[ 'type' => 'textarea', 'value' => 'testing cosy wp form lite handler' ],
			[ 'type' => 'gdpr-checkbox', 'value' => 'I agree, that my data will be stored into CoSy Address Book' ]
		];

		$form_data_2 = [
			[ 'type' => 'name', 'value' => '', 'first' => '', 'last' => '' ],
			[ 'type' => 'email', 'value' => 'tester@cosy.ionos.com' ],
			[ 'type' => 'phone', 'value' => '' ],
			[ 'type' => 'textarea', 'value' => 'testing cosy wp form lite handler' ],
			[ 'type' => 'gdpr-checkbox', 'value' => 'I agree, that my data will be stored into CoSy Address Book' ]
		];

		$expected_value_1 = [
			'source'    => [
				'type'        => 'CONTACTFORM',
				'description' => 'example.org'
			],
			'email'     => 'tester@cosy.ionos.com',
			'phone'     => '000114558963',
			'firstName' => 'CoSy',
			'lastName'  => 'Tester',
			'notes'     => 'testing cosy wp form lite handler',
			'consent'   => [
				'origin' => 'example.org',
				'text'   => 'I agree, that my data will be stored into CoSy Address Book'
			]
		];

		$expected_value_2 = [
			'source'  => [
				'type'        => 'CONTACTFORM',
				'description' => 'example.org'
			],
			'email'   => 'tester@cosy.ionos.com',
			'notes'   => 'testing cosy wp form lite handler',
			'consent' => [
				'origin' => 'example.org',
				'text'   => 'I agree, that my data will be stored into CoSy Address Book'
			]
		];

		return [
			[ $form_data_1, $expected_value_1 ],
			[ $form_data_2, $expected_value_2 ],
		];
	}
}
