<?php if($bol_id) { ?>
	<tr>
		<th><?php _e( 'Номер на товарителница:', SPEEDY_TEXT_DOMAIN ); ?></th>
		<td><a href="https://www.speedy.bg/bg/track-shipment/?shipmentNumber=<?php echo $bol_id; ?>" target="_blank"><?php echo $bol_id; ?></a></td>
	</tr>
<?php } ?>