<?php
/**
 * Shows a speedy section when there is generated loading
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="speedy_loading">
	<table id="loading_info">
		<tr>
			<td><?php _e( 'Номер на товарителница:', SPEEDY_TEXT_DOMAIN ); ?> <a href="<?php echo $print_url; ?>" target="_blank" title="<?php echo $loading_num; ?>"><?php echo $loading_num; ?></a></td>
			<td><a href="javascript: void(0);" onclick="cancelLoadingConfirm(<?php echo $loading_num; ?>);" class="button" title="<?php _e( 'Откажи', SPEEDY_TEXT_DOMAIN ); ?>"><?php _e( 'Откажи', SPEEDY_TEXT_DOMAIN ); ?></a>
			</td>
			<td><a target="_blank" href="<?php echo $track_loading; ?>" class="button" title="<?php _e( 'Проследи', SPEEDY_TEXT_DOMAIN ); ?>"><?php _e( 'Проследи', SPEEDY_TEXT_DOMAIN ); ?></a></td>
			<td>
				<?php if ($print_return_voucher_requested_url) { ?>
					<a target="_blank" href="<?php echo $print_return_voucher_requested_url; ?>" class="button" title="<?php _e( 'Принтиране на ваучер за връщане', SPEEDY_TEXT_DOMAIN ); ?>"><?php _e( 'Принтиране на ваучер за връщане', SPEEDY_TEXT_DOMAIN ); ?></a>
				<?php } ?>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript"><!--
function cancelLoadingConfirm(bol_id) {
	if (confirm("<?php _e( 'Сигурни ли сте, че желаете да откажете тази товарителница?', SPEEDY_TEXT_DOMAIN ); ?>")) {
		jQuery.ajax({
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			type: 'POST',
			data: {
				action: 'speedy_cancel_loading',
				bol_id: encodeURIComponent(bol_id)
			},
			beforeSend: function() {
				jQuery( '#woocommerce-speedy-data' ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
			},
			complete: function() {
				jQuery( '#woocommerce-speedy-data' ).unblock();
			},
			dataType: 'json',
			success: function( response ) {
				if (response.status == true) {
					location.reload();
				} else {
					alert(response.warning);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
			}
		});
	}
	return false;
}
//--></script>