<div class="ui-cosy-address-book">
	<h1>
		<?php _e( 'cosy_address_book_menu_page_headline', 'cosy-address-book' ); ?>
	</h1>
	<div>
		<?php
		CoSy_Address_Book_Api_Client::instance()->check_subscription_update();
		CoSy_Address_Book_View::load_view(
			( CoSy_Address_Book_Api_Client::instance()->is_connected() ) ? 'settings' : 'description'
		);
		?>
	</div>
</div>