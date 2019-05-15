<div>
	<h2><?php _e( 'cosy_address_book_plugin_description_header', 'cosy-address-book' ); ?></h2>
	<p><?php _e( 'cosy_address_book_plugin_activation_hint', 'cosy-address-book' ); ?></p>
	<ul>
		<?php
		foreach ( CoSy_Address_Book_Config::instance()->get_plugins_display_names() as $display_name ) { ?>
			<li>&#8210;&nbsp;&nbsp; <?php echo $display_name; ?>
			</li>
		<?php } ?>
	</ul>
	<p><?php _e( 'cosy_address_book_web_form_insertion_hint', 'cosy-address-book' ); ?></p>
</div>
