<?php
include_once './cosy-address-book/cosy-address-book.php';

class CoSy_Address_Book_Config_Test extends PHPUnit_Framework_TestCase {
	/**
	 * Test for Address_Book_Config::get_api_endpoint()
	 *
	 * @param string $environment
	 * @param string $market
	 * @param string $market_group
	 * @param string $api_endpoint
	 *
	 * @dataProvider get_api_endpoint_data()
	 */
	public function test_get_api_endpoint( $environment, $market, $market_group, $api_endpoint ) {
		$config = new CoSy_Address_Book_Config(
			$this->get_market( $market, $market_group ),
			$this->get_folder(),
			$environment
		);

		$this->assertEquals( $api_endpoint, $config->get_api_endpoint() );
	}

	/**
	 * Test for Address_Book_Config::get_frontend_endpoint()
	 *
	 * @param string $environment
	 * @param string $market
	 * @param string $market_group
	 * @param string $frontend_endpoint
	 *
	 * @dataProvider get_frontend_endpoint_data()
	 */
	public function test_get_frontend_endpoint( $environment, $market, $market_group, $frontend_endpoint ) {
		$config = new CoSy_Address_Book_Config(
			$this->get_market( $market, $market_group ),
			$this->get_folder(),
			$environment
		);

		$this->assertEquals( $frontend_endpoint, $config->get_frontend_endpoint() );
	}

	/**
	 * Test for Address_Book_Config::get_address_book_url()
	 *
	 * @param string $environment
	 * @param string $market
	 * @param string $market_group
	 * @param string $address_book_url
	 *
	 * @dataProvider get_address_book_url_data()
	 */
	public function test_get_address_book_url( $environment, $market, $market_group, $address_book_url ) {
		$config = new CoSy_Address_Book_Config(
			$this->get_market( $market, $market_group ),
			$this->get_folder(),
			$environment
		);

		$this->assertEquals( $address_book_url, $config->get_address_book_url() );
	}

	/**
	 * Test for Address_Book_Config::get_settings_page_url()
	 *
	 * @param string $environment
	 * @param string $market
	 * @param string $market_group
	 * @param string $settings_page_url
	 *
	 * @dataProvider get_settings_page_url_data()
	 */
	public function test_get_settings_page_url( $environment, $market, $market_group, $settings_page_url ) {
		$config = new CoSy_Address_Book_Config(
			$this->get_market( $market, $market_group ),
			$this->get_folder(),
			$environment
		);

		$this->assertEquals( $settings_page_url, $config->get_settings_page_url() );
	}

	/**
	 * Test for Address_Book_Config::get_settings_page_url()
	 *
	 * @param string $environment
	 * @param array $api_fields
	 *
	 * @dataProvider get_api_fields_data()
	 */
	public function test_get_api_fields( $environment, array $api_fields ) {
		$config = new CoSy_Address_Book_Config( $this->get_market(), $this->get_folder(), $environment );
		$this->assertEquals( $api_fields, $config->get_api_fields() );
	}

	/**
	 * Test for Address_Book_Config::get_gdpr_policies_url()
	 *
	 * @param string $environment
	 * @param string $market
	 * @param string $market_group
	 * @param array $gdpr_policies_url
	 *
	 * @dataProvider get_gdpr_policies_url_data()
	 */
	public function test_get_gdpr_policies_url( $environment, $market, $market_group, $gdpr_policies_url ) {
		$config = new CoSy_Address_Book_Config(
			$this->get_market( $market, $market_group ),
			$this->get_folder(),
			$environment
		);

		$this->assertEquals( $gdpr_policies_url, $config->get_gdpr_policies_url() );
	}

	/**
	 * Test for Address_Book_Config::get_supported_plugin_types()
	 */
	public function test_get_supported_plugin_types() {
		$config = new CoSy_Address_Book_Config(
			$this->get_market(),
			$this->get_folder(),
			''
		);

		$this->assertEquals( [ "Form Type One", "Form Type Two" ], $config->get_supported_plugin_types() );
	}

	/**
	 * Test for Address_Book_Config::get_plugins_display_names()
	 */
	public function test_get_plugins_display_names() {
		$config = new CoSy_Address_Book_Config(
			$this->get_market(),
			$this->get_folder(),
			''
		);

		$this->assertEquals( [ "Display Name One", "Display Name Two" ], $config->get_plugins_display_names() );
	}

	/**
	 * Test for Address_Book_Config::get_plugin_display_name()
	 *
	 * @param string $form_type
	 * @param string $expected_display_name
	 *
	 * @dataProvider get_form_plugin_display_name_data()
	 */
	public function test_get_plugin_display_name( $form_type, $expected_display_name ) {
		$config = new CoSy_Address_Book_Config(
			$this->get_market(),
			$this->get_folder(),
			''
		);

		$this->assertEquals( $expected_display_name, $config->get_plugin_display_name( $form_type ) );
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_api_endpoint()
	 *
	 * @return array
	 */
	public function get_api_endpoint_data() {
		return [
			[ 'production', '', 'EU', 'http://eu.production.cosy.middleware' ],
			[ 'production', '', 'US', 'http://us.production.cosy.middleware' ],
			[ 'integration', '', '', 'http://integration.cosy.middleware' ],
			[ 'development', '', '', 'http://development.cosy.middleware' ]
		];
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_frontend_endpoint()
	 *
	 * @return array
	 */
	public function get_frontend_endpoint_data() {
		return [
			[ 'production', 'DE', '', 'http://de.production.cosy.frontend' ],
			[ 'production', 'GB', '', 'http://gb.production.cosy.frontend' ],
			[ 'production', 'ES', '', 'http://es.production.cosy.frontend' ],
			[ 'production', 'FR', '', 'http://fr.production.cosy.frontend' ],
			[ 'production', 'IT', '', 'http://it.production.cosy.frontend' ],
			[ 'production', 'US', '', 'http://us.production.cosy.frontend' ],
			[ 'production', 'CA', '', 'http://ca.production.cosy.frontend' ],
			[ 'production', 'MX', '', 'http://mx.production.cosy.frontend' ],
			[ 'integration', '', '', 'http://integration.cosy.frontend' ],
			[ 'development', '', '', 'http://development.cosy.frontend' ]
		];
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_address_book_url()
	 *
	 * @return array
	 */
	public function get_address_book_url_data() {
		return [
			[ 'production', 'DE', '', 'http://de.production.cosy.frontend' ],
			[ 'production', 'GB', '', 'http://gb.production.cosy.frontend' ],
			[ 'production', 'ES', '', 'http://es.production.cosy.frontend' ],
			[ 'production', 'FR', '', 'http://fr.production.cosy.frontend' ],
			[ 'production', 'IT', '', 'http://it.production.cosy.frontend' ],
			[ 'production', 'US', '', 'http://us.production.cosy.frontend' ],
			[ 'production', 'CA', '', 'http://ca.production.cosy.frontend' ],
			[ 'production', 'MX', '', 'http://mx.production.cosy.frontend' ],
			[ 'integration', '', '', 'http://integration.cosy.frontend' ],
			[ 'development', '', '', 'http://development.cosy.frontend' ]
		];
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_settings_page_url()
	 *
	 * @return array
	 */
	public function get_settings_page_url_data() {
		return [
			[ 'production', 'DE', '', 'http://de.production.cosy.frontend/settings' ],
			[ 'production', 'GB', '', 'http://gb.production.cosy.frontend/settings' ],
			[ 'production', 'ES', '', 'http://es.production.cosy.frontend/settings' ],
			[ 'production', 'FR', '', 'http://fr.production.cosy.frontend/settings' ],
			[ 'production', 'IT', '', 'http://it.production.cosy.frontend/settings' ],
			[ 'production', 'US', '', 'http://us.production.cosy.frontend/settings' ],
			[ 'production', 'CA', '', 'http://ca.production.cosy.frontend/settings' ],
			[ 'production', 'MX', '', 'http://mx.production.cosy.frontend/settings' ],
			[ 'integration', '', '', 'http://integration.cosy.frontend/settings' ],
			[ 'development', '', '', 'http://development.cosy.frontend/settings' ]
		];
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_api_fields()
	 *
	 * @return array
	 */
	public function get_api_fields_data() {
		return [
			[ 'production', [ "field_1", "field_2", "field_3", "field_4", "field_5", "field_6" ] ],
			[ 'integration', [ "field_1", "field_2", "field_3", "field_4", "field_5", "field_6" ] ],
			[ 'development', [ "field_1", "field_2", "field_3", "field_4", "field_5", "field_6" ] ]
		];
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_gdpr_policies_url()
	 *
	 * @return array
	 */
	public function get_gdpr_policies_url_data() {
		return [
			[ 'production', 'DE', '', 'http://de.production.ionos.gdpr.policies' ],
			[ 'production', 'GB', '', 'http://gb.production.ionos.gdpr.policies' ],
			[ 'production', 'ES', '', 'http://es.production.ionos.gdpr.policies' ],
			[ 'production', 'FR', '', 'http://fr.production.ionos.gdpr.policies' ],
			[ 'production', 'IT', '', 'http://it.production.ionos.gdpr.policies' ],
			[ 'production', 'US', '', 'http://us.production.ionos.gdpr.policies' ],
			[ 'production', 'CA', '', 'http://ca.production.ionos.gdpr.policies' ],
			[ 'production', 'MX', '', 'http://mx.production.ionos.gdpr.policies' ],
			[ 'integration', '', '', 'http://integration.ionos.gdpr.policies' ],
			[ 'development', '', '', 'http://development.ionos.gdpr.policies' ]
		];
	}

	/**
	 * Retrieves data to test Address_Book_Config::get_form_plugin_display_name()
	 *
	 * @return array
	 */
	public function get_form_plugin_display_name_data() {
		return [
			[ 'formTypeOne', 'Display Name One' ],
			[ 'formTypeTwo', 'Display Name Two' ]
		];
	}

	/**
	 * Retrieves folder containing config files
	 *
	 * @return string
	 */
	private function get_folder() {
		return sprintf( '%s/config/mockfiles', __DIR__ );
	}

	/**
	 * Retrieves an instance of Address_Book_Market depending on injected parameter values
	 *
	 * @param string $market
	 * @param string $market_group
	 *
	 * @return CoSy_Address_Book_Market
	 */
	private function get_market( $market = 'US', $market_group = 'US' ) {
		$address_book_market = $this->getMockBuilder( 'CoSy_Address_Book_Market' )
		                            ->disableOriginalConstructor()
		                            ->getMock();

		$address_book_market->expects( $this->any() )
		                    ->method( 'get_market' )
		                    ->will( $this->returnValue( $market ) );

		$address_book_market->expects( $this->any() )
		                    ->method( 'get_market_group' )
		                    ->will( $this->returnValue( $market_group ) );

		return $address_book_market;
	}
}
