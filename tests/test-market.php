<?php
include_once './cosy-address-book/cosy-address-book.php';

class CoSy_Address_Book_Market_Test extends PHPUnit_Framework_TestCase {
	/**
	 * Test for Address_Book_Market::get_market()
	 *
	 * @param string $wp_option_market
	 * @param string $expected_market
	 *
	 * @dataProvider get_market_data()
	 */
	public function test_get_market( $wp_option_market, $expected_market ) {
		update_option( CoSy_Address_Book_Market::WP_OPTION_MARKET, $wp_option_market );
		$market = new CoSy_Address_Book_Market();
		$this->assertEquals( $expected_market, $market->get_market() );
	}

	/**
	 * Test for Address_Book_Market::get_market_group()
	 *
	 * @param string $wp_option_market
	 * @param string $expected_market_group
	 *
	 * @dataProvider get_market_group_data()
	 */
	public function test_get_market_group( $wp_option_market, $expected_market_group ) {
		update_option( CoSy_Address_Book_Market::WP_OPTION_MARKET, $wp_option_market );
		$market = new CoSy_Address_Book_Market();
		$this->assertEquals( $expected_market_group, $market->get_market_group() );
	}

	/**
	 * Retrieves data for testing Address_Book_Market::get_market()
	 *
	 * @return array
	 */
	public function get_market_data() {
		return [
			[ 'DE', 'DE' ],
			[ 'ES', 'ES' ],
			[ 'FR', 'FR' ],
			[ 'GB', 'GB' ],
			[ 'IT', 'IT' ],
			[ 'CA', 'CA' ],
			[ 'US', 'US' ],
			[ 'MX', 'MX' ],
			[ 'AT', 'US' ],
			[ 'NL', 'US' ],
			[ 'BL', 'US' ],
			[ 'PL', 'US' ],
			[ null, 'US' ],
			[ '', 'US' ]
		];
	}

	/**
	 * Retrieves data for testing Address_Book_Market::get_market_group()
	 *
	 * @return array
	 */
	public function get_market_group_data() {
		return [
			[ 'DE', 'EU' ],
			[ 'ES', 'EU' ],
			[ 'FR', 'EU' ],
			[ 'GB', 'EU' ],
			[ 'IT', 'EU' ],
			[ 'CA', 'US' ],
			[ 'US', 'US' ],
			[ 'MX', 'US' ],
			[ 'AT', 'US' ],
			[ 'NL', 'US' ],
			[ 'BL', 'US' ],
			[ 'PL', 'US' ],
			[ null, 'US' ],
			[ '', 'US' ]
		];
	}
}
