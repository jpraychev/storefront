<?php
/**
 * Speedy Shipping From Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr class="speedy_row">
<td colspan="2" style="padding: 6px">

<form method="post" enctype="multipart/form-data" id="speedy_form">
	<table id="speedy_client_table">
	<?php if (!$abroad) { ?>
			<tr>
				<td>
					<label for="speedy_city"><?php _e( 'Населено място:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_city" name="city" value="<?php echo $city; ?>" />
					<input type="hidden" id="speedy_city_id" name="city_id" value="<?php echo $city_id; ?>" />
					<input type="hidden" id="speedy_city_nomenclature" name="city_nomenclature" value="<?php echo $city_nomenclature; ?>" />
					<label for="speedy_postcode"><?php _e( 'ПК:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_postcode" name="postcode" value="<?php echo $postcode; ?>" disabled="disabled" />
				</td>
			</tr>
			<tr id="to_office" <?php if (empty($offices)) { ?>style="display:none;" <?php } ?>>
				<td><?php _e( 'Доставка:', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td>
					<?php if ($option_before_payment == 'no_option' || $ignore_obp) { ?>
						<input type="radio" id="speedy_shipping_to_apt" data-is-apt="1" name="to_office" value="1" <?php if (!empty($to_office) && !empty($is_apt)) { ?> checked="checked"<?php } ?> />
						<label for="speedy_shipping_to_apt">
						<?php
							if ($option_before_payment == 'test') {
								_e( 'до автомат (без опция Тествай)', SPEEDY_TEXT_DOMAIN );
							} else if ($option_before_payment == 'open') {
								_e( 'до автомат (без опция Отвори)', SPEEDY_TEXT_DOMAIN );
							} else {
								_e( 'до автомат', SPEEDY_TEXT_DOMAIN );
							}
						?>
						</label>
					<?php } ?>
					<input type="radio" id="speedy_shipping_to_office" data-is-apt="0" name="to_office" value="1" <?php if (!empty($to_office) && empty($is_apt)) { ?> checked="checked"<?php } ?> />
					<label for="speedy_shipping_to_office"><?php _e( 'до офис', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="radio" id="speedy_shipping_to_door" data-is-apt="0" name="to_office" value="0" <?php if (empty($to_office) && empty($is_apt)) { ?> checked="checked"<?php } ?> />
					<label for="speedy_shipping_to_door"><?php _e( 'до врата', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
			</tr>
			<tr id="speedy_quarter_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_quarter"><?php _e( 'Квартал:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_quarter" name="quarter" value="<?php echo $quarter; ?>" />
					<input type="hidden" id="speedy_quarter_id" name="quarter_id" value="<?php echo $quarter_id; ?>" />
				</td>
			</tr>
			<tr id="speedy_street_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_street"><?php _e( 'Улица:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_street" name="street" value="<?php echo $street; ?>" />
					<input type="hidden" id="speedy_street_id" name="street_id" value="<?php echo $street_id; ?>" />
					<label for="speedy_street_no"><?php _e( '№:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_street_no" name="street_no" value="<?php echo $street_no; ?>" />
				</td>
			</tr>
			<tr id="speedy_block_no_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_block_no"><?php _e( 'Бл.:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_block_no" name="block_no" value="<?php echo $block_no; ?>" />
					<label for="speedy_entrance_no"><?php _e( 'Вх.:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_entrance_no" name="entrance_no" value="<?php echo $entrance_no; ?>" />
					<label for="speedy_floor_no"><?php _e( 'Ет.:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_floor_no" name="floor_no" value="<?php echo $floor_no; ?>" />
					<label for="speedy_apartment_no"><?php _e( 'Ап.:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_apartment_no" name="apartment_no" value="<?php echo $apartment_no; ?>" />
				</td>
			</tr>
			<tr id="speedy_note_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_note"><?php _e( 'Забележка към адреса:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_note" name="note" value="<?php echo $note; ?>" />
				</td>
			</tr>
			<tr id="speedy_office_container" <?php if (empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_office_id"><?php _e( 'Офис:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<select id="speedy_office_id" name="office_id" style="padding: 5px 0; border-color: #CCCCCC; color: #666666; width: 100%; border-radius: 3px;">
						<?php if (!empty($to_office) && !empty($is_apt)) { ?>
							<option value="0" selected="selected"><?php _e( ' --- Моля, изберете автомат --- ', SPEEDY_TEXT_DOMAIN ); ?></option>
						<?php } else { ?>
							<option value="0" selected="selected"><?php _e( ' --- Моля, изберете офис --- ', SPEEDY_TEXT_DOMAIN ); ?></option>
						<?php } ?>
						<?php foreach ($offices as $office) { ?>
							<?php if ($office['id'] == $office_id) { ?>
								<?php if (!($office['is_apt'] xor $is_apt)) { ?>
									<option value="<?php echo $office['id']; ?>" data-is-apt="<?php echo $office['is_apt']; ?>" selected="selected"><?php echo $office['label']; ?></option>
								<?php } ?>
							<?php } else { ?>
								<option value="<?php echo $office['id']; ?>" data-is-apt="<?php echo $office['is_apt']; ?>"><?php echo $office['label']; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<input type="hidden" id="is_apt" name="is_apt" value="<?php echo !empty($is_apt) ? 1 : 0; ?>" />
				</td>
			</tr>
			<tr id="speedy_fixed_time" <?php if (!$fixed_time) { ?> style="display: none;"<?php } ?>>
				<td>
					<input class="fixed_time" id="speedy_fixed_time_cb" type="checkbox" <?php if ($fixed_time_cb) { ?>checked="checked"<?php } ?> name="fixed_time_cb" value="1" onclick="speedyCheckFixedTime();" />
					<label id="speedy_fixed_time_cb_label" class="fixed_time"> <?php _e( 'Фиксиран час:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<select class="fixed_time" id="speedy_fixed_time_hour" name="fixed_time_hour" <?php if (!$fixed_time_cb) { ?>disabled="disabled"<?php } ?> onchange="speedySetFixedTime();">
						<?php for ($i = 10; $i <= 17; $i++) { ?>
							<?php $hour = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
							<?php if ($hour == $fixed_time_hour) { ?>
								<?php $fixed_time_hour = $hour; ?>
								<option value="<?php echo $hour; ?>" selected="selected"><?php echo $hour; ?></option>
							<?php } else { ?>
								<option value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<select class="fixed_time" id="speedy_fixed_time_min" name="fixed_time_min" <?php if (!$fixed_time_cb) { ?>disabled="disabled"<?php } ?>>
						<?php $min_fixed_time_mins = ($fixed_time_hour == 10 || empty($fixed_time_hour)) ? 30 : 0; ?>
						<?php $max_fixed_time_mins = ($fixed_time_hour == 17) ? 30 : 59; ?>
						<?php for ($i = $min_fixed_time_mins; $i <= $max_fixed_time_mins; $i++) { ?>
							<?php $hour = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
							<?php if ($hour == $fixed_time_min) { ?>
								<option value="<?php echo $hour; ?>" selected="selected"><?php echo $hour; ?></option>
							<?php } else { ?>
								<option value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
	<?php } else { ?>
			<tr>
				<td>
					<label for="speedy_country" class="speedy_required"><?php _e( 'Държава:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_country" name="country" value="<?php echo $country; ?>" disabled="disabled" />
					<input type="hidden" id="speedy_country_id" name="country_id" value="<?php echo $country_id; ?>" />
					<input type="hidden" id="speedy_country_nomenclature" name="country_nomenclature" value="<?php echo $country_nomenclature; ?>" />
					<label for="speedy_state" id="speedy_state_label" class="<?php if ($required_state) { ?>speedy_required<?php } ?>"><?php _e( 'Щат:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_state" name="state" value="<?php echo $state; ?>" <?php if ($state_disabled) { ?>disabled="disabled"<?php } ?> />
					<input type="hidden" id="speedy_state_id" name="state_id" value="<?php echo $state_id; ?>" />
					<input type="hidden" id="speedy_required_state" name="required_state" value="<?php echo $required_state; ?>" />
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="speedy_city"><?php _e( 'Населено място:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_city" name="city" value="<?php echo $city; ?>" />
					<input type="hidden" id="speedy_city_id" name="city_id" value="<?php echo $city_id; ?>" />
					<input type="hidden" id="speedy_city_nomenclature" name="city_nomenclature" value="<?php echo $city_nomenclature; ?>" />
					<label for="speedy_postcode"><?php _e( 'ПК:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_postcode" name="postcode" value="<?php echo $postcode; ?>"/>
				</td>
			</tr>
			<?php if (!empty($country_address_nomenclature)) { ?>
			<tr id="to_office" <?php if (empty($offices)) { ?>style="display:none;" <?php } ?>>
				<td><?php _e( 'Доставка:', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td>
					<?php if ($option_before_payment == 'no_option' || $ignore_obp) { ?>
						<input type="radio" id="speedy_shipping_to_apt" data-is-apt="1" name="to_office" value="1" <?php if (!empty($to_office) && !empty($is_apt)) { ?> checked="checked"<?php } ?> />
						<label for="speedy_shipping_to_apt">
						<?php
							if ($option_before_payment == 'test') {
								_e( 'до автомат (без опция Тествай)', SPEEDY_TEXT_DOMAIN );
							} else if ($option_before_payment == 'open') {
								_e( 'до автомат (без опция Отвори)', SPEEDY_TEXT_DOMAIN );
							} else {
								_e( 'до автомат', SPEEDY_TEXT_DOMAIN );
							}
						?>
						</label>
					<?php } ?>
					<input type="radio" id="speedy_shipping_to_office" data-is-apt="0" name="to_office" value="1" <?php if (!empty($to_office) && empty($is_apt)) { ?> checked="checked"<?php } ?> />
					<label for="speedy_shipping_to_office"><?php _e( 'до офис', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="radio" id="speedy_shipping_to_door" data-is-apt="0" name="to_office" value="0" <?php if (empty($to_office) && empty($is_apt)) { ?> checked="checked"<?php } ?> />
					<label for="speedy_shipping_to_door"><?php _e( 'до врата', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
			</tr>
			<tr id="speedy_quarter_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_quarter"><?php _e( 'Квартал:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_quarter" name="quarter" value="<?php echo $quarter; ?>" />
					<input type="hidden" id="speedy_quarter_id" name="quarter_id" value="<?php echo $quarter_id; ?>" />
				</td>
			</tr>
			<tr id="speedy_street_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_street"><?php _e( 'Улица:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_street" name="street" value="<?php echo $street; ?>" />
					<input type="hidden" id="speedy_street_id" name="street_id" value="<?php echo $street_id; ?>" />
					<label for="speedy_street_no"><?php _e( '№:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_street_no" name="street_no" value="<?php echo $street_no; ?>" />
				</td>
			</tr>
			<tr id="speedy_block_no_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_block_no"><?php _e( 'Бл.:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_block_no" name="block_no" value="<?php echo $block_no; ?>" />
					<label for="speedy_entrance_no"><?php _e( 'Вх.:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_entrance_no" name="entrance_no" value="<?php echo $entrance_no; ?>" />
					<label for="speedy_floor_no"><?php _e( 'Ет.:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_floor_no" name="floor_no" value="<?php echo $floor_no; ?>" />
					<label for="speedy_apartment_no"><?php _e( 'Ап.:', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="text" id="speedy_apartment_no" name="apartment_no" value="<?php echo $apartment_no; ?>" />
				</td>
			</tr>
			<tr id="speedy_note_container" <?php if (!empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_note"><?php _e( 'Забележка към адреса:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_note" name="note" value="<?php echo $note; ?>" />
				</td>
			</tr>
			<tr id="speedy_office_container" <?php if (empty($to_office)) { ?> style="display: none;"<?php } ?>>
				<td>
					<label for="speedy_office_id"><?php _e( 'Офис:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<select id="speedy_office_id" name="office_id" style="padding: 5px 0; border-color: #CCCCCC; color: #666666; width: 100%; border-radius: 3px;">
						<?php if (!empty($to_office) && !empty($is_apt)) { ?>
							<option value="0" selected="selected"><?php _e( ' --- Моля, изберете автомат --- ', SPEEDY_TEXT_DOMAIN ); ?></option>
						<?php } else { ?>
							<option value="0" selected="selected"><?php _e( ' --- Моля, изберете офис --- ', SPEEDY_TEXT_DOMAIN ); ?></option>
						<?php } ?>
						<?php foreach ($offices as $office) { ?>
							<?php if ($office['id'] == $office_id) { ?>
								<?php if (!($office['is_apt'] xor $is_apt)) { ?>
									<option value="<?php echo $office['id']; ?>" data-is-apt="<?php echo $office['is_apt']; ?>" selected="selected"><?php echo $office['label']; ?></option>
								<?php } ?>
							<?php } else { ?>
								<option value="<?php echo $office['id']; ?>" data-is-apt="<?php echo $office['is_apt']; ?>"><?php echo $office['label']; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<input type="hidden" id="is_apt" name="is_apt" value="<?php echo !empty($is_apt) ? 1 : 0; ?>" />
				</td>
			</tr>
			<tr id="speedy_fixed_time" <?php if (!$fixed_time) { ?> style="display: none;"<?php } ?>>
				<td>
					<input class="fixed_time" id="speedy_fixed_time_cb" type="checkbox" <?php if ($fixed_time_cb) { ?>checked="checked"<?php } ?> name="fixed_time_cb" value="1" onclick="speedyCheckFixedTime();" />
					<label id="speedy_fixed_time_cb_label" class="fixed_time"> <?php _e( 'Фиксиран час:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<select class="fixed_time" id="speedy_fixed_time_hour" name="fixed_time_hour" <?php if (!$fixed_time_cb) { ?>disabled="disabled"<?php } ?> onchange="speedySetFixedTime();">
						<?php for ($i = 10; $i <= 17; $i++) { ?>
							<?php $hour = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
							<?php if ($hour == $fixed_time_hour) { ?>
								<?php $fixed_time_hour = $hour; ?>
								<option value="<?php echo $hour; ?>" selected="selected"><?php echo $hour; ?></option>
							<?php } else { ?>
								<option value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<select class="fixed_time" id="speedy_fixed_time_min" name="fixed_time_min" <?php if (!$fixed_time_cb) { ?>disabled="disabled"<?php } ?>>
						<?php $min_fixed_time_mins = ($fixed_time_hour == 10 || empty($fixed_time_hour)) ? 30 : 0; ?>
						<?php $max_fixed_time_mins = ($fixed_time_hour == 17) ? 30 : 59; ?>
						<?php for ($i = $min_fixed_time_mins; $i <= $max_fixed_time_mins; $i++) { ?>
							<?php $hour = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
							<?php if ($hour == $fixed_time_min) { ?>
								<option value="<?php echo $hour; ?>" selected="selected"><?php echo $hour; ?></option>
							<?php } else { ?>
								<option value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<?php } else { ?>
			<tr id="speedy_address_1_container">
				<td>
					<label for="speedy_address_1" class="speedy_required"><?php _e( 'Адрес 1:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_address_1" name="address_1" value="<?php echo $address_1; ?>"/>
				</td>
			</tr>
			<tr id="speedy_address_2_container">
				<td>
					<label for="speedy_address_2"><?php _e( 'Адрес 2:', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
				<td>
					<input type="text" id="speedy_address_2" name="address_2" value="<?php echo $address_2; ?>"/>
				</td>
			</tr>
			<?php } ?>
	<?php } ?>
			<tr id="speedy_cod_table" style="display: none;">
				<td><label><?php _e( 'Спиди наложен платеж:', SPEEDY_TEXT_DOMAIN ); ?></label></td>
				<td>
					<input type="radio" id="speedy_cod_yes" name="cod" value="1" <?php if (!empty($cod)) { ?> checked="checked"<?php } ?> />
					<label for="speedy_cod_yes"><?php _e( 'Да', SPEEDY_TEXT_DOMAIN ); ?></label>
					<input type="radio" id="speedy_cod_no" name="cod" value="0" <?php if ((isset($cod) && !$cod)) { ?> checked="checked"<?php } ?> />
					<label for="speedy_cod_no"><?php _e( 'Не', SPEEDY_TEXT_DOMAIN ); ?></label>
				</td>
			</tr>
		</table>
	<?php if (!$abroad) { ?>
		<input type="hidden" id="speedy_country_id" name="country_id" value="<?php echo $country_id; ?>" />
	<?php } ?>
	<input type="hidden" id="abroad" name="abroad" value="<?php echo $abroad; ?>" />
	<input type="hidden" name="country_address_nomenclature" value="<?php echo $country_address_nomenclature; ?>" />
	<input type="hidden" id="speedy_cod_status" name="cod_status" value="<?php echo $cod_status; ?>" />
	<input type="hidden" id="speedy_active_currency_code" name="active_currency_code" value="<?php echo $active_currency_code; ?>" />
	<div id="speedy_methods" <?php if (empty($speedy_methods)) { ?> style="display: none;"<?php } ?>>
		<?php if (!empty($speedy_methods)) { ?>
			<table>
				<tr>
					<td colspan="3"><?php _e( 'Изберете услуга', SPEEDY_TEXT_DOMAIN ); ?></td>
				</tr>
				<?php foreach ($speedy_methods as $speedy_method) { ?>
					<tr>
						<td>
							<input type="radio" name="speedy_shipping_method_id" id="speedy.<?php echo $speedy_method['code']; ?>" value="<?php echo $speedy_method['code']; ?>" <?php if ($speedy_method['code'] == $speedy_shipping_method_id) { ?>checked="checked"<?php } ?> />
							<label for="speedy.<?php echo $speedy_method['code']; ?>"><?php echo $speedy_method['title']; ?></label>
							<input type="hidden" name="shipping_method_price" id="speedy_price_<?php echo $speedy_method['code']; ?>" value="<?php echo $speedy_method['cost']; ?>"  disabled="disabled" />
							<br/>
							<?php if ($speedy_method['total_form']) { ?>
								<table <?php if ( $speedy_shipping_method_id != $speedy_method['code'] ) { ?>style="display:none;"<?php } ?> class="speedy_<?php echo $speedy_method['code']; ?> speedy_table">
									<?php foreach ($speedy_method['total_form'] as $total_form) { ?>
										<tr>
											<td><?php echo $total_form['label']; ?></td>
											<td><?php echo $total_form['value']; ?></td>
										</tr>
									<?php } ?>
								</table>
							<?php } ?>
						</td>
						<td class="right speedy_table_right"><?php echo $speedy_method['text']; ?></td>
					</tr>
				<?php } ?>
		</table>
		<?php } ?>
	</div>
	<div id="speedy_compare_address_warning" class="woocommerce-info" style="display: none;"></div>
	<input type="hidden" name="speedy_payment_method" />
</form>
</td>
</tr>

<script type="text/javascript"><!--
jQuery('tr.speedy_row').insertBefore('tr.shipping');

var wc_speedy_shipping_method_id = '<?php echo $wc_speedy_shipping_method_id; ?>';
var error_cyrillic = '<?php _e( 'Моля, използвайте само латински символи!', SPEEDY_TEXT_DOMAIN ); ?>';
var gateway = '<?php echo WC()->session->chosen_payment_method; ?>';

if (jQuery('#payment ul li [name=payment_method]').length) {
	if (jQuery('#payment_method_cod').is(':checked')) {
		jQuery('#speedy_cod_yes').prop('checked', true);
		jQuery('[name="cod"]').val(1)
	} else {
		jQuery('#speedy_cod_no').prop('checked', true);
		jQuery('[name="cod"]').val(0)
	}

	jQuery(document).on('change', '#payment ul li [name=payment_method]', function(e) {
		if (jQuery('.shipping_method:checked').val() != 'speedy_shipping_method' && jQuery('.shipping_method').length > 1) {
			return;
		}

		if (jQuery('#payment_method_cod').is(':checked')) {
			jQuery('#speedy_cod_yes').prop('checked', true);
			jQuery('[name="cod"]').val(1)
		} else {
			jQuery('#speedy_cod_no').prop('checked', true);
			jQuery('[name="cod"]').val(0)
		}
		jQuery('[name="speedy_payment_method"]').val(jQuery('#payment ul li [name=payment_method]:checked').val());
		speedySubmit(false);
	})
}
jQuery('body').on('updated_checkout', function() {
	if (jQuery('.shipping_method:checked').val() != 'speedy_shipping_method' && jQuery('.shipping_method').length > 1) {
		return;
	}

	jQuery('#payment_method_' + gateway).prop('checked', true);

	if (jQuery('#payment_method_' + gateway).is(':checked') && !jQuery('.payment_box.payment_method_' + gateway).is(":visible")) {
		jQuery('.payment_box').hide();
		jQuery('.payment_box.payment_method_' + gateway).show();
	}
});

jQuery(document).ready(function() {
	jQuery('#speedy_form').on('change', function() {
		disableNotSelectedSpeedyMethods( false );

		speedy_disabled = jQuery('#speedy_form input:disabled, #speedy_form select:disabled');
		speedy_disabled.removeAttr('disabled');

		disableNotSelectedSpeedyMethods();

		jQuery.ajax({
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			type: 'POST',
			data: {
				action: 'speedy_save_data_form',
				data: jQuery('#speedy_form').serialize()
			},
			beforeSend: function() {
				jQuery('#place_order').prop('disabled', true);
			},
			complete: function() {
				jQuery('#place_order').prop('disabled', false);
			}
		});

		speedy_disabled.attr('disabled', true);
	});

	jQuery('#speedy_form input').keypress(function(event){
		if ((jQuery('#abroad').val() == 1) && event.key.match(/[а-яА-я]/)) {
			event.preventDefault();
			alert(error_cyrillic);
		}
	});

	jQuery('#speedy_form input:text').focusout(function(event){
		speedy_clear_input(jQuery(this));
	});

	jQuery('#speedy_form input:text').each(function(index) {
		speedy_clear_input(jQuery(this));
	});

	setRecalculatedPriceToShippingAmount();
});

function disableNotSelectedSpeedyMethods( disable = true ) {
	jQuery('[name="speedy_shipping_method_id"]:not(:checked)').each(function() {
		jQuery('#speedy_price_' + jQuery(this).val()).prop('disabled', disable);
	});
}

function setRecalculatedPriceToShippingAmount() {
	var checked_speedy_method = jQuery('[name="speedy_shipping_method_id"]:checked');
	var selected_price = jQuery('#speedy_price_' + checked_speedy_method.val()).val();
	var amount = jQuery('.shipping_method:checked').parent().find('.amount');

	if (selected_price != amount.text().replace(amount.find('.woocommerce-Price-currencySymbol').text(), '')) {
		setSpeedyMethod(checked_speedy_method.val(), selected_price, false);
	}
}

function speedySubmit(next) {
	speedy_disabled = jQuery('#speedy_form input:disabled, #speedy_form select:disabled');
	jQuery('#speedy_form :input').removeAttr('disabled');

	jQuery.ajax({
		url: '<?php echo admin_url('admin-ajax.php'); ?>',
		type: 'POST',
		data: {
			action: 'speedy_submit_form',
			data: jQuery('#speedy_form').serialize()
		},
		dataType: 'json',
		beforeSend: function() {
			jQuery(".speedy_error").remove();

			jQuery( '.woocommerce-checkout-review-order-table' ).block({
				message: '<?php _e( 'Моля, изчакайте. Цената за доставка се калкулира ...', SPEEDY_TEXT_DOMAIN ); ?>',
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			jQuery( '.woocommerce-checkout-payment' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},
		complete: function( data ) {
			speedy_disabled.attr('disabled', true);
		},
		success: function(data) {
			gateway = data.gateway;
			if (data.status == false) {
				if (jQuery('.woocommerce-error.speedy_error').length == 0) {
					jQuery.each(data.error, function(i) {
						jQuery('#speedy_form').prepend( '<ul class="woocommerce-error speedy_error">' + data.error[i] + '</ul>' );
					});
				}

				add_offset = 0;
				if (jQuery('#wpadminbar').length != 0) {
					add_offset = jQuery('#wpadminbar').height();
				}

				setTimeout(() => {
					if (jQuery('#speedy_form').length) {
						jQuery('html, body').animate({
							scrollTop: jQuery('#speedy_form').offset().top - add_offset
						}, 500);
					}
				}, 500);

				jQuery('#speedy_form :input :disabled').attr('disabled', true);
				jQuery( '.woocommerce-checkout-review-order-table' ).unblock();
				jQuery( '.woocommerce-checkout-payment' ).unblock();
			} else {
				jQuery('#speedy_methods').html('');
				jQuery('#speedy_compare_address_warning').hide();
				<?php if (!$abroad) { ?>
					jQuery('#speedy_postcode').attr('disabled', 'disabled');
				<?php } ?>

				if (data.methods) {
					html = "<table><tr><td colspan=\"3\"><?php _e( 'Изберете услуга', SPEEDY_TEXT_DOMAIN ); ?></td></tr>";

					if (data.methods.length) {
						for (i = 0; i < data.methods.length; i++) {
							html += '<tr>';
							html += '  <td>';
							html += '    <input type="radio" name="speedy_shipping_method_id" id="speedy.'+data.methods[i]['code']+'" value="'+data.methods[i]['code']+'" ';
							if (data.shipping_method_id == data.methods[i]['code']) {
								html += 'checked="checked"';
							}

							html += ' /> <label for="speedy.'+data.methods[i]['code']+'">'+data.methods[i]['title']+'</label><br /> <input type="hidden" name="shipping_method_price" id="speedy_price_'+data.methods[i]['code']+'" value="'+data.methods[i]['cost']+'" disabled="disabled" /> ';

							if (data.methods[i]['total_form'].length) {
								html += '  <table ';
								if (data.shipping_method_id != data.methods[i]['code']) {
									html += 'style="display: none;"';
								}
								html += ' class="speedy_'+data.methods[i]['code']+' speedy_table">';
								for (j = 0; j< data.methods[i]['total_form'].length; j++) {
								html += '    <tr>';
								html += '      <td>'+data.methods[i]['total_form'][j]['label']+'</td>';
								html += '      <td>'+data.methods[i]['total_form'][j]['value']+'</td>';
								html += '    </tr>';
								}
								html += '  </table>';
							}

							html += '</td>';
							html += '<td class="right speedy_table_right">'+data.methods[i]['text']+'</td></tr>';
						}
					} else {
						html += "<tr><td colspan=\"3\"><?php _e( 'Няма намерени услуги!', SPEEDY_TEXT_DOMAIN ); ?></td></tr>";
					}

					html += '</table>';

					jQuery('#speedy_methods').html(html);
					jQuery('#speedy_methods').show();

					if (data.methods.length == 1) {
						jQuery('input[name=\'speedy_shipping_method_id\']:first').prop('checked', 1).trigger('change');
					}

					jQuery('input[name=\'speedy_shipping_method_id\']:checked').trigger('change');

					if (jQuery('input#changed_data').val() == 1) {
						setSpeedyMethod(jQuery('input[name=\'speedy_shipping_method_id\']:checked').val(), jQuery('#speedy_price_' + jQuery('input[name=\'speedy_shipping_method_id\']:checked').val()).val(), false);
					} else {
						jQuery('#speedy_form :input :disabled').attr('disabled', true);
						jQuery( '.woocommerce-checkout-review-order-table' ).unblock();
						jQuery( '.woocommerce-checkout-payment' ).unblock();
					}
				}

				jQuery('input[name=\'speedy_shipping_method_id\']').change( function () {
					if (jQuery(this).is(':checked')) {
						setSpeedyMethod(jQuery(this).val(), jQuery('#speedy_price_' + jQuery(this).val()).val(), true);
					}
				});

				setRecalculatedPriceToShippingAmount();
				jQuery('#price-not-calculated').remove();

				speedy_disabled.attr('disabled', true);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
		}
	});
}

jQuery('input[name=\'speedy_shipping_method_id\']').change( function () {
	if (jQuery(this).is(':checked')) {
		setSpeedyMethod(jQuery(this).val(), jQuery('#speedy_price_' + jQuery(this).val()).val(), true);
	}
});

function speedyCheckFixedTime() {
	if (jQuery('#speedy_fixed_time_cb:checked').length) {
		jQuery('#speedy_fixed_time_hour').removeAttr('disabled');
		jQuery('#speedy_fixed_time_min').removeAttr('disabled');
	} else {
		jQuery('#speedy_fixed_time_hour').attr('disabled', 'disabled');
		jQuery('#speedy_fixed_time_min').attr('disabled', 'disabled');
	}
}

function setSpeedyMethod(method_id, price, block) {
	if (method_id) {
		jQuery.ajax({
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			type: 'POST',
			data: {
				action: 'set_speedy_method',
				method_id: encodeURIComponent(method_id),
				method_price: encodeURIComponent(price)
			},
			dataType: 'json',
			beforeSend: function() {
				jQuery('#place_order').prop('disabled', true);
				if (block) {
					jQuery( '.woocommerce-checkout-review-order-table' ).block({
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					});
					jQuery( '.woocommerce-checkout-payment' ).block({
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					});
				}
			},
			complete: function( data ) {
				jQuery( '.woocommerce-checkout-review-order-table' ).unblock();
				jQuery( '.woocommerce-checkout-payment' ).unblock();
				jQuery('#place_order').prop('disabled', false);
			},
			success: function(json) {
				jQuery('table.speedy_table').hide();
				jQuery('table.speedy_' + method_id).show();

				jQuery( '.order-total' ).find( '.amount' ).html(json.new_total);

				var speedy_price = jQuery('#shipping_method li').has('input[value=speedy_shipping_method]');

				if (speedy_price.find('.amount').length == 0) {
					speedy_price.find('label').after('<span class="woocommerce-Price-amount amount"></span>');
				}

				if ( json.woocommerce_shipping_method_format == 'select' ) {
					jQuery('select.shipping_method :selected').html(json.price_text);
				} else {
					speedy_price.find('label').html(json.shipping_title);

					if (jQuery('input[value=speedy_shipping_method][type=radio]:checked').length) {
						if (speedy_price.find('.amount').length == 0) {
							speedy_price.find('label').after('<span class="woocommerce-Price-amount amount"></span>');
						}

						speedy_price.find('.amount').html(json.price_text);
					} else if (jQuery('input[value=speedy_shipping_method][type=hidden]').length) {
						speedy_price.find('.amount').html(json.price_text);
					}
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
			}
		});
	} else {
		jQuery( '.woocommerce-checkout-review-order-table' ).unblock();
		jQuery( '.woocommerce-checkout-payment' ).unblock();
	}
}

function speedySetFixedTime() {
	if (jQuery('#speedy_fixed_time_hour').val() == 10) {
		min_fixed_time_mins = 30;
	} else {
		min_fixed_time_mins = 0;
	}

	if (jQuery('#speedy_fixed_time_hour').val() == 17) {
		max_fixed_time_mins = 30;
	} else {
		max_fixed_time_mins = 59;
	}

	html = '';

	for (i = min_fixed_time_mins; i <= max_fixed_time_mins; i++) {
		iStr = i.toString();

		if (iStr.length < 2) {
			fixed_time_min = '0' + i;
		} else {
			fixed_time_min = i;
		}

		html += '<option value="' + fixed_time_min + '">' + fixed_time_min + '</option>';
	}

	jQuery('#speedy_fixed_time_min').html(html);
}

// Autocomplete functions
var speedy_city = '<?php echo $city; ?>';
var speedy_quarter = '<?php echo $quarter; ?>';
var speedy_street = '<?php echo $street; ?>';
var speedy_country = '<?php echo $country; ?>';
var speedy_state = '<?php echo $state; ?>';

jQuery(document).ready(function() {
	jQuery( "#speedy_city" ).autocomplete({
		source: function(request, response) {
			var $this = jQuery(this);
			var $element = jQuery(this.element);
			var jqXHR = $element.data('jqXHR');
			if (jqXHR) {
				jqXHR.abort();
			}
			$element.data('jqXHR', jQuery.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					action: 'get_cities',
					term: request.term,
					country_id: jQuery('#speedy_country_id').val(),
					abroad: '<?php echo $abroad; ?>'
				},
				complete: function() {
					$this.removeData('jqXHR');
				},
				success: function(data) {
					if (jQuery('#speedy_country_nomenclature').val() == 'FULL') {
						if (data.length) {
							response(data);
						}
					} else {
						response(data);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			}));
		},
		delay: 500,
		minLength: 1,
		select: function(event, ui) {
			if (ui.item) {
				speedy_city = ui.item.value;
				jQuery('#speedy_postcode').val(ui.item.postcode);
				jQuery('#speedy_city_id').val(ui.item.id);
				jQuery('#speedy_city_nomenclature').val(ui.item.nomenclature);
				jQuery('#speedy_quarter').val('');
				jQuery('#speedy_quarter_id').val('');
				jQuery('#speedy_street').val('');
				jQuery('#speedy_street_id').val('');
				jQuery('#speedy_street_no').val('');
				jQuery('#speedy_block_no').val('');
				jQuery('#speedy_entrance_no').val('');
				jQuery('#speedy_floor_no').val('');
				jQuery('#speedy_apartment_no').val('');
				jQuery('#speedy_note').val('');
				jQuery('#speedy_office_id').html('<option value="0"><?php _e( ' --- Моля, изчакайте --- ', SPEEDY_TEXT_DOMAIN ); ?></option>');

				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					dataType: 'json',
					data: {
						action: 'get_offices',
						city_id: ui.item.id,
						abroad: '<?php echo $abroad; ?>',
						country_id: jQuery('#speedy_country_id').val(),
					},
					success: function(data) {
						if (data.error) {
							alert(data.error);
						} else {
							html = '';

							if (data.length) {
								var hasApt = false;
								var hasOffices = false;

								html += '<option value="0"><?php _e( ' --- Моля, изберете офис --- ', SPEEDY_TEXT_DOMAIN ); ?></option>';
								for (i = 0; i < data.length; i++) {
									html += '<option value="' + data[i]['id'] + '" data-is-apt="' + data[i]['is_apt'] + '">' + data[i]['label'] + '</option>';

									if (data[i]['is_apt'] == 1) {
										hasApt = true;
									}

									if (data[i]['is_apt'] == 0) {
										hasOffices = true;
									}
								}

								jQuery('#speedy_office_id').html(html);

								if (jQuery('#to_office:hidden').length == 1
									|| (hasApt && jQuery('#speedy_shipping_to_apt:checked'))
									|| (hasOffices && jQuery('#speedy_shipping_to_office:checked'))
								) {
									if (hasApt) {
										jQuery('#speedy_shipping_to_apt').trigger('click');
										speedy_change_to_office(jQuery('#speedy_shipping_to_apt'));
									} else if (hasOffices) {
										jQuery('#speedy_shipping_to_office').trigger('click');
										speedy_change_to_office(jQuery('#speedy_shipping_to_office'));
									}
								}

								jQuery('#speedy_shipping_to_apt').parent().toggle(hasApt);
								jQuery('#speedy_shipping_to_office').parent().toggle(hasOffices);

								jQuery('#to_office').show();
							} else {
								jQuery('#speedy_office_id').html(html);
								jQuery('#speedy_shipping_to_door').trigger('click');
								speedy_change_to_office(jQuery('#speedy_shipping_to_door'));
								hideOffices(jQuery('#to_office input[name=to_office]:checked'));
								jQuery('#to_office').hide();
							}
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
					}
				});
			}
		},
		change: function(event, ui) {
			if(!ui.item && jQuery('#speedy_country_nomenclature').val() == 'FULL') {
				jQuery('#speedy_city').val('');
				jQuery('#speedy_city_id').val('');
				jQuery('#speedy_city_nomenclature').val('');
				jQuery('#speedy_postcode').val('');
				jQuery('#speedy_office_id').html('<option value="0"><?php _e( '--- Моля, въведете населено място ---', SPEEDY_TEXT_DOMAIN ); ?></option>');
			}

			jQuery('#speedy_quarter').val('');
			jQuery('#speedy_quarter_id').val('');
			jQuery('#speedy_street').val('');
			jQuery('#speedy_street_id').val('');
			jQuery('#speedy_street_no').val('');
			jQuery('#speedy_block_no').val('');
			jQuery('#speedy_entrance_no').val('');
			jQuery('#speedy_floor_no').val('');
			jQuery('#speedy_apartment_no').val('');
			jQuery('#speedy_note').val('');
		}
	});

	jQuery('#speedy_city').blur(function() {
		var $this = jQuery(this);
		var jqXHR = jQuery(this).data('jqXHR');
		if (jqXHR) {
			jqXHR.abort();
		}
		$this.removeData('jqXHR');

		if ($this.val() != speedy_city) {
			if (!jQuery('#abroad').val() || (jQuery('#abroad').val() && (jQuery('#speedy_country_nomenclature').val() == 'FULL'))) {
				jQuery('#speedy_city').val('');
				jQuery('#speedy_postcode').val('');
			}
			jQuery('#speedy_shipping_to_door').click();
			jQuery('#to_office').hide();

			jQuery('#speedy_city_id').val('');
			jQuery('#speedy_city_nomenclature').val('');
			jQuery('#speedy_office_id').html('<option value="0"><?php _e( '--- Моля, въведете населено място ---', SPEEDY_TEXT_DOMAIN ); ?></option>');
			jQuery('#speedy_quarter').val('');
			jQuery('#speedy_quarter_id').val('');
			jQuery('#speedy_street').val('');
			jQuery('#speedy_street_id').val('');
			jQuery('#speedy_street_no').val('');
			jQuery('#speedy_block_no').val('');
			jQuery('#speedy_entrance_no').val('');
			jQuery('#speedy_floor_no').val('');
			jQuery('#speedy_apartment_no').val('');
			jQuery('#speedy_note').val('');
		}
	});

	jQuery('#speedy_quarter').autocomplete({
		source: function(request, response) {
			var $this = jQuery(this);
			var $element = jQuery(this.element);
			var jqXHR = $element.data('jqXHR');
			if (jqXHR) {
				jqXHR.abort();
			}
			$element.data('jqXHR', jQuery.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					action: 'get_quarters',
					term: request.term,
					city_id: function() { return jQuery('#speedy_city_id').val(); },
					abroad: '<?php echo $abroad; ?>'
				},
				complete: function() {
					$this.removeData('jqXHR');
				},
				success: function(data) {
					if (data.error) {
						jQuery('#speedy_quarter').val('');
						jQuery('#speedy_quarter_id').val('');
						alert(data.error);
					} else {
						if (jQuery('#speedy_city_nomenclature').val() == 'FULL') {
							if (data.length) {
								response(data);
							}
						} else {
							response(data);
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			}));
		},
		minLength: 1,
		select: function(event, ui) {
			if (ui.item) {
				speedy_quarter = ui.item.value;
				jQuery('#speedy_quarter_id').val(ui.item.id);
			}
		},
		change: function(event, ui) {
			if(!ui.item && jQuery('#speedy_city_nomenclature').val() == 'FULL') {
				jQuery('#speedy_quarter').val('');
				jQuery('#speedy_quarter_id').val('');
			}
		}
	});

	jQuery('#speedy_quarter').blur(function() {
		var $this = jQuery(this);
		var jqXHR = jQuery(this).data('jqXHR');
		if (jqXHR) {
			jqXHR.abort();
		}
		$this.removeData('jqXHR');

		if (($this.val() != speedy_quarter) && (jQuery('#speedy_city_nomenclature').val() == 'FULL')) {
			jQuery('#speedy_quarter').val('');
			jQuery('#speedy_quarter_id').val('');
		}
	});

	jQuery('#speedy_street').autocomplete({
		source: function(request, response) {
			var $this = jQuery(this);
			var $element = jQuery(this.element);
			var jqXHR = $element.data('jqXHR');
			if (jqXHR) {
				jqXHR.abort();
			}
			$element.data('jqXHR', jQuery.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					action: 'get_streets',
					term: request.term,
					city_id: function() { return jQuery('#speedy_city_id').val(); },
					abroad: '<?php echo $abroad; ?>'
				},
				complete: function() {
					$this.removeData('jqXHR');
				},
				success: function(data) {
					if (data.error) {
						jQuery('#speedy_street').val('');
						jQuery('#speedy_street_id').val('');
						alert(data.error);
					} else {
						if (jQuery('#speedy_city_nomenclature').val() == 'FULL') {
							if (data.length) {
								response(data);
							}
						} else {
							response(data);
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			}));
		},
		minLength: 1,
		select: function(event, ui) {
			if (ui.item) {
				speedy_street = ui.item.value;
				jQuery('#speedy_street_id').val(ui.item.id);
			}
		},
		change: function(event, ui) {
			if(!ui.item && jQuery('#speedy_city_nomenclature').val() == 'FULL') {
				jQuery('#speedy_street').val('');
				jQuery('#speedy_street_id').val('');
			}
		}
	});

	jQuery('#speedy_street').blur(function() {
		var $this = jQuery(this);
		var jqXHR = jQuery(this).data('jqXHR');
		if (jqXHR) {
			jqXHR.abort();
		}
		$this.removeData('jqXHR');

		if (($this.val() != speedy_street) && (jQuery('#speedy_city_nomenclature').val() == 'FULL')) {
			jQuery('#speedy_street').val('');
			jQuery('#speedy_street_id').val('');
		}
	});

	jQuery('#speedy_block_no').autocomplete({
		source: function(request, response) {
			var $this = jQuery(this);
			var $element = jQuery(this.element);
			var jqXHR = $element.data('jqXHR');
			if (jqXHR) {
				jqXHR.abort();
			}
			$element.data('jqXHR', jQuery.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					action: 'get_blocks',
					term: request.term,
					city_id: function() { return jQuery('#speedy_city_id').val(); },
					abroad: '<?php echo $abroad; ?>'
				},
				complete: function() {
					$this.removeData('jqXHR');
				},
				success: function(data) {
					if (data.error) {
						jQuery('#speedy_block_no').val('');
						alert(data.error);
					} else {
						response(data);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			}));
		},
		minLength: 1
	});

	jQuery('#speedy_block_no').blur(function() {
		var $this = jQuery(this);
		var jqXHR = jQuery(this).data('jqXHR');
		if (jqXHR) {
			jqXHR.abort();
		}
		$this.removeData('jqXHR');
	});

	jQuery('#speedy_country').autocomplete({
		source: function(request, response) {
			var $this = jQuery(this);
			var $element = jQuery(this.element);
			var jqXHR = $element.data('jqXHR');
			if (jqXHR) {
				jqXHR.abort();
			}
			$element.data('jqXHR', jQuery.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					action: 'get_countries',
					term: request.term,
					abroad: '<?php echo $abroad; ?>'
				},
				complete: function() {
					$this.removeData('jqXHR');
				},
				success: function(data) {
					if (data.error) {
						jQuery('#speedy_country').val('');
						jQuery('#speedy_country_id').val('');
						jQuery('#speedy_country_nomenclature').val('');
						jQuery('#speedy_state').val('');
						jQuery('#speedy_state_id').val('');
						alert(data.error);
					} else {
						response(data);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			}));
		},
		minLength: 1,
		select: function(event, ui) {
			if (ui.item) {
				speedy_country = ui.item.value;
				jQuery('#speedy_country').val(ui.item.value);
				jQuery('#speedy_country_id').val(ui.item.id);
				jQuery('#speedy_country_nomenclature').val(ui.item.nomenclature);
				jQuery('#speedy_required_state').val(ui.item.required_state);
				jQuery('#speedy_required_postcode').val(ui.item.required_postcode);
				jQuery('#speedy_active_currency_code').val(ui.item.active_currency_code);

				if (!ui.item.active_currency_code) {
					jQuery('#speedy_cod_table').hide();
					jQuery('#speedy_cod_no').click();
					jQuery('#speedy_cod_status').val(0);
				} else {
					jQuery('#speedy_cod_table').show();
					jQuery('#speedy_cod_status').val(1);
				}

				if (ui.item.required_state) {
					jQuery('#speedy_state_label').addClass('speedy_required');
				} else {
					jQuery('#speedy_state_label').removeClass('speedy_required');
				}

				if (ui.item.required_postcode) {
					jQuery('#speedy_postcode_label').addClass('speedy_required');
				} else {
					jQuery('#speedy_postcode_label').removeClass('speedy_required');
				}
			}
		},
		change: function(event, ui) {
			if (!ui.item) {
				jQuery('#speedy_country').val('');
				jQuery('#speedy_country_id').val('');
				jQuery('#speedy_country_nomenclature').val('');
			}
			jQuery('#speedy_state').val('');
			jQuery('#speedy_state_id').val('');
			jQuery('#speedy_city').val('');
			jQuery('#speedy_city_id').val('');
			jQuery('#speedy_city_nomenclature').val('');
			jQuery('#speedy_postcode').val('');
			}
		}
	);

	jQuery('#speedy_country').blur(function() {
		var $this = jQuery(this);
		var jqXHR = jQuery(this).data('jqXHR');
		if (jqXHR) {
			jqXHR.abort();
		}
		$this.removeData('jqXHR');

		if ($this.val() != speedy_country) {
			$this.val('');
			jQuery('#speedy_country_id').val('');
			jQuery('#speedy_country_nomenclature').val('');
			jQuery('#speedy_state').val('');
			jQuery('#speedy_state_id').val('');
			jQuery('#speedy_city').val('');
			jQuery('#speedy_city_id').val('');
			jQuery('#speedy_city_nomenclature').val('');
			jQuery('#speedy_postcode').val('');
		}

		if (jQuery('#speedy_country_container .wait').length != 0) {
			jQuery('#speedy_country_container .wait').remove();
		}
	});

	jQuery('#speedy_state').autocomplete({
		source: function(request, response) {
			var $this = jQuery(this);
			var $element = jQuery(this.element);
			var jqXHR = $element.data('jqXHR');
			if (jqXHR) {
				jqXHR.abort();
			}
			$element.data('jqXHR', jQuery.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					action: 'get_states',
					term: request.term,
					country_id: function() { return jQuery('#speedy_country_id').val(); },
					abroad: '<?php echo $abroad; ?>'
				},
				complete: function() {
					$this.removeData('jqXHR');
				},
				success: function(data) {
					if (data.error) {
						jQuery('#speedy_state').val('');
						jQuery('#speedy_state_id').val('');
						alert(data.error);
					} else {
						response(data);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			}));
		},
		minLength: 1,
		select: function(event, ui) {
			if (ui.item) {
				speedy_state = ui.item.value;
				jQuery('#speedy_state').val(ui.item.value);
				jQuery('#speedy_state_id').val(ui.item.id).change();
			}
		},
		change: function(event, ui) {
			if (!ui.item) {
				jQuery('#speedy_state').val('');
				jQuery('#speedy_state_id').val('');
			}
		}
	});

	jQuery('#speedy_state').blur(function() {
		var $this = jQuery(this);
		var jqXHR = jQuery(this).data('jqXHR');
		if (jqXHR) {
			jqXHR.abort();
		}
		$this.removeData('jqXHR');

		if ($this.val() != speedy_state) {
			jQuery(this).val('');
			jQuery('#speedy_state_id').val('');
		}
	});

	jQuery('#speedy_state_id, #speedy_postcode').on('change', function () {
		if (!jQuery('#country_address_nomenclature').val()
			&& ((jQuery('#speedy_state_id').val() && jQuery('#required_state').val()) || !jQuery('#required_state').val())
			&& ((jQuery('#speedy_postcode').val() && jQuery('#required_postcode').val()) || !jQuery('#required_postcode').val())
		) {
			speedySubmit(false);
		}
	});

	jQuery('#to_office input[name=to_office], #speedy_street_id, #speedy_quarter_id').on('change', function () {
		speedy_change_to_office(this);
	});

	hideOffices(jQuery('#to_office input[name=to_office]:checked'));

	<?php if ($speedy_precalculate) { ?>
		if (jQuery('#speedy_form').serialize()) {
			speedySubmit(false);
			jQuery('#speedy_form').change();
		}
	<?php } ?>
});
// End Autocomplete functions

function speedy_clear_input(element) {
	if ((jQuery('#abroad').val() == 1) && element.val().match(/[а-яА-я]/)) {
		element.val('');
	}
}

function speedy_change_to_office(element) {
	hideOffices(element);

	if (jQuery(element).attr('data-is-apt') == 1) {
		jQuery('#speedy_office_id option[value=0]').text('<?php _e( ' --- Моля, изберете автомат --- ', SPEEDY_TEXT_DOMAIN ); ?>');
	} else {
		jQuery('#speedy_office_id option[value=0]').text('<?php _e( ' --- Моля, изберете офис --- ', SPEEDY_TEXT_DOMAIN ); ?>');
	}

	jQuery('#speedy_office_id').val(0);

	speedySubmit(false);
}

function hideOffices(speedy_radio) {
	var hasApt = false;
	var hasOffices = false;

	if (jQuery(speedy_radio).val() == 1) {
		jQuery('#is_apt').val(jQuery(speedy_radio).attr('data-is-apt'));

		jQuery('#speedy_quarter_container, #speedy_street_container, #speedy_block_no_container, #speedy_note_container').hide();
		jQuery('#speedy_office_container').show();
	} else {
		jQuery('#is_apt').val(0);

		jQuery('#speedy_quarter_container, #speedy_street_container, #speedy_block_no_container, #speedy_note_container').show();
		jQuery('#speedy_office_container').hide();
	}

	jQuery('#speedy_office_id option').each(function() {
		if (jQuery(this).val() == 0) {
			return;
		}

		if ((jQuery(this).attr('data-is-apt') == 1 && jQuery(speedy_radio).attr('data-is-apt') != 1)
			|| (jQuery(this).attr('data-is-apt') != 1 && jQuery(speedy_radio).attr('data-is-apt') == 1)
		) {
			jQuery(this).hide();
		} else {
			jQuery(this).show();
		}

		if (jQuery(this).attr('data-is-apt') == 1) {
			hasApt = true;
		} else {
			hasOffices = true;
		}
	});

	jQuery('#speedy_shipping_to_apt, [for=speedy_shipping_to_apt]').toggle(hasApt);
	jQuery('#speedy_shipping_to_office, [for=speedy_shipping_to_office]').toggle(hasOffices);
}

function str_pad(input, pad_length, pad_string, pad_type) {
	  // From: http://phpjs.org/functions
	  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // + namespaced by: Michael White (http://getsprink.com)
	  // +      input by: Marco van Oort
	  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	  // *     example 1: str_pad('Kevin van Zonneveld', 30, '-=', 'STR_PAD_LEFT');
	  // *     returns 1: '-=-=-=-=-=-Kevin van Zonneveld'
	  // *     example 2: str_pad('Kevin van Zonneveld', 30, '-', 'STR_PAD_BOTH');
	  // *     returns 2: '------Kevin van Zonneveld-----'
	  var half = '',
		pad_to_go;

	  var str_pad_repeater = function (s, len) {
		var collect = '',
		  i;

		while (collect.length < len) {
		  collect += s;
		}
		collect = collect.substr(0, len);

		return collect;
	  };

	  input += '';
	  pad_string = pad_string !== undefined ? pad_string : ' ';

	  if (pad_type !== 'STR_PAD_LEFT' && pad_type !== 'STR_PAD_RIGHT' && pad_type !== 'STR_PAD_BOTH') {
		pad_type = 'STR_PAD_RIGHT';
	  }
	  if ((pad_to_go = pad_length - input.length) > 0) {
		if (pad_type === 'STR_PAD_LEFT') {
		  input = str_pad_repeater(pad_string, pad_to_go) + input;
		} else if (pad_type === 'STR_PAD_RIGHT') {
		  input = input + str_pad_repeater(pad_string, pad_to_go);
		} else if (pad_type === 'STR_PAD_BOTH') {
		  half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
		  input = half + input + half;
		  input = input.substr(0, pad_length);
		}
	  }

	  return input;
}
--></script>