<?php
/**
 * Shows a speedy section when there isn`t generated loading
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="form-field" style="margin: 0 10px 10px 162px ;"><label><?php echo _e( 'СПИДИ ПОЩА - Максимален брой единици от продукта запълващ опаковката', SPEEDY_TEXT_DOMAIN ); ?></label>
	<table class="wp-list-table widefat fixed striped posts" style="clear: initial;">
		<thead>
			<tr>
				<td align="center"><?php echo _e( 'Размер на опаковката в см.', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td align="center"><?php echo _e( 'XS <br> (50 x 35 x 4,5)', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td align="center"><?php echo _e( 'S <br> (60 x 35 x 11)', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td align="center"><?php echo _e( 'M <br> (60 x 35 x 19)', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td align="center"><?php echo _e( 'L <br> (60 x 35 x 37)', SPEEDY_TEXT_DOMAIN ); ?></td>
				<td align="center"><?php echo _e( 'XL <br> (60 x 60 x 60)', SPEEDY_TEXT_DOMAIN ); ?></td>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td align="center">
					<?php echo _e( 'Максимален брои продукти за пакет', SPEEDY_TEXT_DOMAIN ); ?>
				</td>
				<td align="center">
					<input type="text" name="speedy[quantity_dimentions][XS]" value="<?php echo !empty($XS) ? $XS : ''; ?>" style="width: 100%;" >
				</td>
				<td align="center">
					<input type="text" name="speedy[quantity_dimentions][S]" value="<?php echo !empty($S) ? $S : ''; ?>" style="width: 100%;">
				</td>
				<td align="center">
					<input type="text" name="speedy[quantity_dimentions][M]" value="<?php echo !empty($M) ? $M : ''; ?>" style="width: 100%;">
				</td>
				<td align="center">
					<input type="text" name="speedy[quantity_dimentions][L]" value="<?php echo !empty($L) ? $L : ''; ?>" style="width: 100%;">
				</td>
				<td align="center">
					<input type="text" name="speedy[quantity_dimentions][XL]" value="<?php echo !empty($XL) ? $XL : ''; ?>" style="width: 100%;">
				</td>
			</tr>
		</tbody>
	</table>
</div>