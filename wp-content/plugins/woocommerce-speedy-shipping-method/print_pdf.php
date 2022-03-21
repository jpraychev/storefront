<?php
/**
 * Shows a speedy loading pdf
 */
	require_once( '../../../wp-load.php' );

	$speedy_shipping_method = new WC_Speedy_Shipping_Method();
	$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();
	$order = $speedy_shipping_method->_getOrderByOrderIdByBolId($_GET['bol_id']);

	$additional_copy_for_sender_value = maybe_unserialize($order['data'])['additional_copy_for_sender'];

	if ( isset( $_GET['bol_id'] ) && preg_match('/^[0-9]{11}$/',$_GET['bol_id']) ) {
		$pdf = $speedy_shipping_method->speedy->createPDF(trim($_GET['bol_id']), $additional_copy_for_sender_value);

		if (!$speedy_shipping_method->speedy->getError() && $pdf) {

			header('Content-Type: application/pdf');

			echo $pdf;
		}
	} else {
		wp_die(__( 'Товарителницата не съществува!', SPEEDY_TEXT_DOMAIN ));
	}