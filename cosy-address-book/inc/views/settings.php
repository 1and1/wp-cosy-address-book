<div>
	<h2><?php _e( 'cosy_address_book_connection_header', 'cosy-address-book' ); ?></h2>
	<?php _e( 'cosy_address_book_connection_confirmation', 'cosy-address-book' ); ?>
	<div class="deconnection-link">
		<a href='<?php echo CoSy_Address_Book_Config::instance()->get_settings_page_url(); ?>' target='_blank'>
			<?php _e( 'cosy_address_book_deconnection_url_text', 'cosy-address-book' ); ?>
		</a>
	</div>
</div>
<div>
	<?php
	$template_name = sprintf(
		'plugins/%s',
		( CoSy_Address_Book_Api_Client::instance()->has_displayable_form_settings() ) ? 'settings' : 'description'
	);

	CoSy_Address_Book_View::load_view( $template_name );
	?>
</div>