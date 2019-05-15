<?php

/**
 * This class is responsible of fetching all relevant configuration data such as: endpoint, url and supported api fields
 *
 * Furthermore it used utility class Address_Book_Market to identify the right market to match market based configuration
 */
class CoSy_Address_Book_Config {
	/**
	 * Instance of api client
	 *
	 * @param CoSy_Address_Book_Config
	 */
	private static $instance;

	/**
	 * instance of Address_Book_Market to fetch relevant market data
	 *
	 * @var CoSy_Address_Book_Market
	 */
	private $market;

	/**
	 * current stage environment extracted out of STAGE_ENV env parameter
	 *
	 * @var string
	 */
	private $environment;

	/**
	 * current folder containing all config files
	 *
	 * @var string
	 */
	private $folder;

	/**
	 * retrieves an instance of CoSy_Address_Book_Config
	 *
	 * @return CoSy_Address_Book_Config
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			$market         = new CoSy_Address_Book_Market();
			$folder         = self::get_folder();
			$environment    = self::get_environment();
			self::$instance = new self( $market, $folder, $environment );
		}

		return self::$instance;
	}

	/**
	 * Address_Book_Config constructor.
	 *
	 * @param CoSy_Address_Book_Market $market instance of Address_Book_Market to fetch relevant market data
	 * @param string $folder folder containing all config files
	 * @param string $environment stage environment
	 */
	public function __construct( CoSy_Address_Book_Market $market, $folder, $environment ) {
		$this->market      = $market;
		$this->folder      = $folder;
		$this->environment = $environment;
	}

	/**
	 * Retrieves api endpoint
	 *
	 * @return string
	 */
	public function get_api_endpoint() {
		return $this->get_endpoint( 'cosy_middleware', $this->market->get_market_group() );
	}

	/**
	 * Retrieves CoSy Address Book Frontend endpoint
	 *
	 * @return string
	 */
	public function get_frontend_endpoint() {
		return $this->get_endpoint( 'cosy_frontend', $this->market->get_market() );
	}

	/**
	 * Retrieves url of CoSy Address Book Frontend landing page
	 *
	 * @return string
	 */
	public function get_address_book_url() {
		return $this->get_frontend_endpoint();
	}


	/**
	 * Retrieves url of CoSy Address Book Frontend setting page
	 *
	 * @return string
	 */
	public function get_settings_page_url() {
		return sprintf( '%s/settings', $this->get_frontend_endpoint() );
	}

	/**
	 * Retrieves url describing gdpr policies handling
	 *
	 * @return string
	 */
	public function get_gdpr_policies_url() {
		return $this->get_endpoint( 'gdpr_policies', $this->market->get_market() );
	}

	/**
	 * Retrieves list of currently supported api fields
	 *
	 * @return array
	 */
	public function get_api_fields() {
		$config = $this->get_config_data( 'api_fields' );

		return $config['fields'];
	}

	/**
	 * Retrieves list of supported plugin types
	 *
	 * @return array
	 */
	public function get_supported_plugin_types() {
		$config = $this->get_config_data( 'plugins' );

		return $config['plugins']['supported_types'];
	}

	/**
	 * Retrieves list of plugins display names
	 *
	 * @return array
	 */
	public function get_plugins_display_names() {
		$config = $this->get_config_data( 'plugins' );

		return $config['plugins']['display_names'];
	}

	/**
	 * Retrieves corresponding form plugin display name identified by given form type
	 *
	 * @param string $plugin_type
	 *
	 * @return string
	 */
	public function get_plugin_display_name( $plugin_type ) {
		$config = $this->get_config_data( 'plugins' );

		return $config['plugins']['plugin_types'][ $plugin_type ]['display_name'];
	}

	/**
	 * Retrieves endpoint value depending on injected relevant parameters
	 *
	 * @param string $section config section to extract endpoint value data from
	 * @param string $market_data market data to be used to extract end point value
	 *
	 * @return string
	 */
	private function get_endpoint( $section, $market_data ) {
		$end_point = null;

		$config = $this->get_config_data( 'api_endpoints' );

		switch ( $this->environment ) {
			case 'production':
				$end_point = $config[ $section ][ $this->environment ][ $market_data ];
				break;

			default:
				$end_point = $config[ $section ][ $this->environment ];

		}

		return $end_point;
	}

	/**
	 * Retrieves config data found in file identified by given name
	 *
	 * @param string $file_name
	 *
	 * @return array
	 */
	private function get_config_data( $file_name ) {
		return json_decode( file_get_contents( $this->get_file_path( $file_name ) ), true );
	}

	/**
	 * Retrieves corresponding file path identified by given config type
	 *
	 * @param string $config_type
	 *
	 * @return string
	 */
	private function get_file_path( $config_type ) {
		return sprintf( '%s/%s.json', $this->folder, $config_type );
	}

	/**
	 * Retrieves current environment stage extracted out of STAGE_ENV env parameter
	 *
	 * Note that if no STAGE_ENV is set or found, 'production' will be retrieved a default value
	 *
	 * @return string
	 */
	private static function get_environment() {
		$stage_env = getenv( 'STAGE_ENV' );

		if ( empty( $stage_env ) ) {
			return 'production';
		}

		return $stage_env;
	}

	/**
	 * Retrieves current folder containing config files
	 *
	 * @return string
	 */
	private static function get_folder() {
		return sprintf( '%s/../config', __DIR__ );
	}
}