<p>
	<?php _e( 'cosy_address_book_invitation', 'cosy-address-book' ); ?>
	<a href='<?php echo CoSy_Address_Book_Config::instance()->get_address_book_url(); ?>' target='_blank'>
		<?php _e( 'cosy_address_book_url_text', 'cosy-address-book' ); ?>
	</a>
</p>
<h2><?php _e( 'cosy_address_book_connection_header', 'cosy-address-book' ); ?></h2>
<div id="feedback-message" default-error="<?php _e( 'cosy_address_book_generic_error', 'cosy-address-book' ); ?>"></div>
<div>
	<p>
		<?php _e( 'cosy_address_book_guidance_summary', 'cosy-address-book' ); ?>
	</p>
	<div class="flex-layout">
		<div class="flex-item flex-shrink no-wrap mr8">
			<?php _e( 'cosy_address_book_guidance_step_one_label', 'cosy-address-book' ); ?>:
		</div>
		<div class="flex-item flex-grow mb16">
                        <span>
                            <?php _e( 'cosy_address_book_guidance_step_one_content', 'cosy-address-book' ); ?>
	                        <a href='<?php echo CoSy_Address_Book_Config::instance()->get_settings_page_url(); ?>'
	                           target="_blank">
                                <?php _e( 'cosy_address_book_url_text', 'cosy-address-book' ); ?>
                            </a>
                        </span>
		</div>
	</div>
	<div class="flex-layout">
		<div class="flex-item flex-shrink no-wrap mr8">
			<?php _e( 'cosy_address_book_guidance_step_two_label', 'cosy-address-book' ); ?>:
		</div>
		<div class="flex-item flex-grow mb16">
			<div class="mb4">
				<?php _e( 'cosy_address_book_guidance_step_two_content', 'cosy-address-book' ); ?>
			</div>
			<div>
				<div class="mb4"><label
						for="apiKey"><?php _e( 'cosy_address_book_activation_form_label', 'cosy-address-book' ); ?></label>
				</div>
				<input name='api_key' id="apiKey" type="text">
			</div>
		</div>
	</div>
</div>
<div>
	<button id="connect-api-key"><?php _e( 'cosy_address_book_form_button_text', 'cosy-address-book' ); ?></button>
</div>