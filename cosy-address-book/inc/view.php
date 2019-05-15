<?php

/**
 * This class is responsible of rendering html value of view identified by specific file name
 */
class CoSy_Address_Book_View {
	public static function load_page() {
		self::load_view( 'main' );
	}

	public static function load_view( $template_name ) {
		load_template(
			sprintf( '%s/views/%s.php', __DIR__, $template_name )
		);
	}
}