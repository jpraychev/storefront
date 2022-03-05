<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package storefront
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<div id="secondary" class="widget-area" role="complementary">
	<!-- Sidebar removed from all products and single products pages jpraychev -->
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</div><!-- #secondary -->
