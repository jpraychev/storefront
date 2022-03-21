jQuery(document).ready(function() {
	var speedy_grouped_fields = [
		'pricing',
		'fixed_price',
		'free_shipping_total',
		'free_method_city',
		'free_method_intercity',
		'free_method_international',
		'table_rate_file',
		'option_before_payment',
		'return_payer_type',
		'return_package_city_service_id',
		'return_package_intercity_service_id',
		'ignore_obp',
		'return_voucher',
		'return_voucher_city_service_id',
		'return_voucher_intercity_service_id',
		'return_voucher_payer_type',
		'insurance',
		'fragile',
		'from_office',
		'order_status_update',
		'final_statuses',
		'office_id'
	];

	for (key in speedy_grouped_fields) {
		jQuery('#' + php_vars.prefix + '_' + speedy_grouped_fields[key]).parent().parent('tr').addClass('speedy-group');
		jQuery('#' + php_vars.prefix + '_' + speedy_grouped_fields[key]).parent().parent().parent('tr').addClass('speedy-group');
	}

	var speedy_grouped_fields_first = [
		'pricing',
		'option_before_payment',
		'return_voucher',
		'insurance',
		'from_office',
		'order_status_update',
	];

	for (key in speedy_grouped_fields_first) {
		jQuery('#' + php_vars.prefix + '_' + speedy_grouped_fields_first[key]).parent().parent().parent('tr').addClass('speedy-first-in-group');
	}

	// Speedy Pricing
	jQuery('select#' + php_vars.prefix + '_pricing').change(function(){
		if (jQuery(this).val()=="calculator") {
			jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').hide();
		} else if (jQuery(this).val()=="fixed" || jQuery(this).val()=="calculator_fixed") {
			jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').hide();
		} else if (jQuery(this).val()=="free") {
			jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').hide();
		} else if (jQuery(this).val()=="table_rate") {
			jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').show();
		}
	}).change();

	// Hide row
	jQuery('select[name*="' + php_vars.prefix + '"]').each(function(){
		if (jQuery(this).hasClass('hidden-row')) {
			jQuery(this).parent().parent().closest('tr').hide();
		}
	});

	// Hide fragile
	jQuery('select#' + php_vars.prefix + '_insurance').change(function(){
		if (jQuery(this).val()==0) {
			jQuery('#' + php_vars.prefix + '_fragile').parent().parent().closest('tr').hide();
		} else {
			jQuery('#' + php_vars.prefix + '_fragile').parent().parent().closest('tr').show();
		}
	});

	// Hide office
	jQuery('select#' + php_vars.prefix + '_from_office').change(function(){
		if (jQuery(this).val()==0) {
			jQuery('#' + php_vars.prefix + '_office_id').parent().parent().closest('tr').hide();
		} else {
			jQuery('#' + php_vars.prefix + '_office_id').parent().parent().closest('tr').show();
		}
	});

	// Hide office
	jQuery('select#' + php_vars.prefix + '_order_status_update').change(function(){
		if (jQuery(this).val()==0) {
			jQuery('#final_statuses').parent().parent().closest('tr').hide();
		} else {
			jQuery('#final_statuses').parent().parent().closest('tr').show();
		}
	});
	if (jQuery('select#' + php_vars.prefix + '_order_status_update').val()==0) {
		jQuery('#final_statuses').parent().parent().closest('tr').hide();
	} else {
		jQuery('#final_statuses').parent().parent().closest('tr').show();
	}

	// Hide return voucher options
	jQuery('select#' + php_vars.prefix + '_return_voucher').change(function(){
		if (jQuery(this).val()==0) {
			jQuery('#' + php_vars.prefix + '_return_voucher_city_service_id').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_return_voucher_intercity_service_id').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_return_voucher_payer_type').parent().parent().closest('tr').hide();
		} else {
			jQuery('#' + php_vars.prefix + '_return_voucher_city_service_id').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_return_voucher_intercity_service_id').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_return_voucher_payer_type').parent().parent().closest('tr').show();
		}
	});

	// Hide option before payment
	jQuery('select#' + php_vars.prefix + '_option_before_payment').change(function(){
		if (jQuery(this).val() == 'no_option') {
			jQuery('#' + php_vars.prefix + '_return_payer_type').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_return_package_city_service_id').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_return_package_intercity_service_id').parent().parent().closest('tr').hide();
			jQuery('#' + php_vars.prefix + '_ignore_obp').parent().parent().closest('tr').hide();
		} else {
			jQuery('#' + php_vars.prefix + '_return_payer_type').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_return_package_city_service_id').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_return_package_intercity_service_id').parent().parent().closest('tr').show();
			jQuery('#' + php_vars.prefix + '_ignore_obp').parent().parent().closest('tr').show();
		}
	});

	jQuery('select#' + php_vars.prefix + '_free_method_city').change(function(){
		changePricing();
	});
	jQuery('select#' + php_vars.prefix + '_free_method_intercity').change(function(){
		changePricing();
	});
});

 // Generate allowed methods if they are not set
function getAllowedMethods() {
	if (jQuery('input[name="' + php_vars.prefix + '_server_address"]').val() == '' || jQuery('input[name="' + php_vars.prefix + '_username"]').val() == '' || jQuery('input[name="' + php_vars.prefix + '_password"]').val() == '') {
		alert(php_vars.error_get_allowed_methods);
	} else {
		jQuery.post(
			ajaxurl, 
				{
					action: "get_allowed_methods",
					speedy_server_address: jQuery('input[name="' + php_vars.prefix + '_server_address"]').val(),
					speedy_username: jQuery('input[name="' + php_vars.prefix + '_username"]').val(),
					speedy_password: jQuery('input[name="' + php_vars.prefix + '_password"]').val()
				}, 
			function(data) {
				if (data.status) {
					var services = data.services;
					html = '';

					for (i = 0; i < services.length; i++) {
						html += '<option value="' + services[i]['service_id'] + '">' + services[i]['name'] + '</option>';
					}
					jQuery('#services_buttons').show();
					jQuery('#generate_services_button').hide();
		
					jQuery('#' + php_vars.prefix + '_allowed_methods').html(html);
				} else {
					alert(data.error);
				}
			},
			'json'
		);
	}
}

function changePricing() {
	pricing = jQuery('#' + php_vars.prefix + '_pricing').val();
	if (pricing == 'free') {
		jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').show();
		jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').show();
		jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').show();
		jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').show();
		jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').hide();
	} else if (pricing == 'fixed' || pricing == 'calculator_fixed') {
		jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().closest('tr').show();
		jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().closest('tr').parent().hide();
		jQuery('#' + php_vars.prefix + '_free_method_international').parent().closest('tr').parent().hide();
		jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').hide();
	} else if (jQuery(this).val()=="table_rate") {
		jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').show();
	} else {
		jQuery('#' + php_vars.prefix + '_fixed_price').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_shipping_total').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_city').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_intercity').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_free_method_international').parent().parent().closest('tr').hide();
		jQuery('#' + php_vars.prefix + '_table_rate_file').parent().parent().closest('tr').hide();
	}
}