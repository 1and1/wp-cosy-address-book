<?php
$api_fields    = CoSy_Address_Book_Config::instance()->get_api_fields();
$form_handlers = CoSy_Address_Book_Api_Client::instance()->get_form_handlers();

/* @var CoSy_Form_Handler $form_handler */
foreach ( $form_handlers as $form_handler ) { ?>
	<?php if ( $form_handler->has_form_embedded_on_page() ) { ?>
		<h2 class='ui-form-type' id="<?php echo $form_handler->get_type(); ?>">
			<?php echo CoSy_Address_Book_Config::instance()->get_plugin_display_name(
				$form_handler->get_type()
			); ?>
		</h2>
		<?php if ( $form_handler->requires_user_field_mapping() ) { ?>
			<p>
				<?php _e( 'cosy_address_book_mapping_description', 'cosy-address-book' ); ?>
			</p>
			<div id="feedback-message"
			     default-error="<?php _e( 'cosy_address_book_generic_error', 'cosy-address-book' ); ?>"></div>
			<div>
				<div class="flex-layout flex-wrap v-center">
					<div class="flex-item size-6">
						<b><?php _e( 'cosy_address_book_mapping_header_api_fields', 'cosy-address-book' ); ?></b>
					</div>
					<div class="flex-item size-6">
						<b><?php _e( 'cosy_address_book_mapping_header_form_fields', 'cosy-address-book' ); ?></b>
					</div>
				</div>
				<?php foreach ( $api_fields as $api_field ) { ?>
					<div class='flex-layout flex-wrap v-center ui-mapping-configuration'>
						<div class="flex-item size-6 ui-api-field" id=<?php echo $api_field; ?>>
							<label>
								<?php _e(
									sprintf(
										'cosy_address_book_api_field_display_name_%s',
										strtolower( $api_field )
									),
									'cosy-address-book'
								); ?>
							</label>
						</div>
						<div class="flex-item size-6 ui-form-field">
							<?php echo get_form_fields_choice_element(
								$form_handler->get_user_form_fields()
							); ?>
						</div>
					</div>
				<?php } ?>
				<div>
					<p>
						<?php _e( 'cosy_address_book_consent_field_constraint_hint', 'cosy-address-book' ); ?>
					</p>
				</div>
			</div>
			<button type="button" id="save-field-mappings">
				<?php _e( 'cosy_address_book_form_button_text', 'cosy-address-book' ); ?>
			</button>
		<?php } else { ?>
			<div>
				<p>
					<?php _e( 'cosy_address_book_synchronisation_ready_hint', 'cosy-address-book' ); ?>
				</p>
			</div>
			<div class='ui-gdpr-policy-hint'>
				<p>
					<?php _e( 'cosy_address_book_gdpr_hint_content', 'cosy-address-book' ); ?>
				</p>
			</div>
		<?php } ?>
	<?php } ?>
<?php } ?>
<?php
function get_form_fields_choice_element( array $form_fields ) {
	$option_elements_pattern = '#OPTIONS#';

	$form_fields_choice_element = sprintf( "
                <select>
                    <option value='none'>-</option>
                    %s
                <select>",
		$option_elements_pattern
	);

	$option_elements = '';

	foreach ( $form_fields as $form_field ) {
		$option_elements .= sprintf( "<option value='%s'>%s</option>", $form_field, $form_field );
	}

	return str_replace( $option_elements_pattern, $option_elements, $form_fields_choice_element );
}

?>