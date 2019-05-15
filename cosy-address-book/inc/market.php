<?php

/**
 * This class is responsible of fetching market value configured for current WordPress installation
 */
class CoSy_Address_Book_Market {
	/**
	 * @var string
	 */
	const WP_OPTION_MARKET = 'assistant_market';

	/**
	 * @var string
	 */
	const MARKET_GROUP_EU = 'EU';

	/**
	 * @var string
	 */
	const MARKET_GROUP_US = 'US';

	/**
	 * Default market
	 *
	 * @var string
	 */
	private $default_market = 'US';

	/**
	 * List of supported markets
	 *
	 * @var array
	 */
	private $supported_markets = [ 'DE', 'CA', 'GB', 'US', 'ES', 'MX', 'FR', 'IT' ];

	/**
	 * List of markets assigned to EU markets
	 *
	 * @var array
	 */
	private $eu_markets = [ 'DE', 'ES', 'GB', 'FR', 'IT' ];

	/**
	 * List of markets assigned to EU markets
	 *
	 * @var array
	 */
	private $us_markets = [ 'US', 'CA', 'MX' ];

	/**
	 * Retrieves depending on market value configured for current installation in wp_option
	 *
	 * @return string
	 */
	public function get_market() {
		$market = $this->get_wp_option_market();

		if ( ! $market || ! in_array( $market, $this->supported_markets ) ) {
			$market = $this->default_market;
		}

		return $market;
	}

	/**
	 * Retrieves market group EU or US depending on market value configured for current installation
	 *
	 * @return string
	 */
	public function get_market_group() {
		$market = $this->get_wp_option_market();

		if ( in_array( $market, $this->eu_markets ) ) {
			return self::MARKET_GROUP_EU;
		}

		if ( in_array( $market, $this->us_markets ) ) {
			return self::MARKET_GROUP_US;
		}

		return self::MARKET_GROUP_US;
	}

	/**
	 * Retrieves the contract's market value provided by the installation
	 *
	 * @return string
	 */
	private function get_wp_option_market() {
		return ( string ) strtoupper( get_option( self::WP_OPTION_MARKET, $this->default_market ) );
	}
}