<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}
/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

function sidebarToggle() {
	?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				
				const isFrontPage = () => {
					wordpressURL = 'http://localhost/jewelry-project/index.php/shop/'
					wordpressFilterUrl = wordpressURL + '?filter'
					wordpressPriceUrl = wordpressURL + '?min_price'
					currUrl = window.location.href
					if (currUrl == wordpressURL) return true
					if (currUrl.includes(wordpressFilterUrl)) return true
					if (currUrl.includes(wordpressPriceUrl)) return true
					return false
				}

				const moveCartAfterSearch = () => {
					cart = jQuery('#site-header-cart')
					searchBar = jQuery('.site-search')
					searchBar.after(cart)
				}

				const allPagesFloatRight = () => {				
					if (!isFrontPage()) {
						jQuery('#primary').css('float', 'left')
						jQuery('#primary').css('width', '100%')
					} else {
						renderSidebarToggle()
						addActiveToSidebar()
						toggleSidebar()
					}
				}

				const removeOldArrowsFromQuantity = () => {
					quantityElements =  jQuery("[id^=quantity]")
					quantityElements.each(function(_, elem) {
						jQuery(elem).attr("type", "text")
					})
				}

				const addPlusMinusToQuantity = () => {
					q = jQuery('.quantity')
					html_minus = '<a class="quantity-change minus-quantity" data-quantity=-1> - </a>'
					html_plus = '<a class="quantity-change plus-quantity" data-quantity=1> + </a>'
					q.prepend(html_minus)
					q.append(html_plus)
				}

				const changeQuantity = () => {
					jQuery('.quantity-change').on('click', function() {
						// parentElement = jQuery(this).parent().parent().parent().find("input");
						parentElement = jQuery(this).parent().find('input')
						currQuantity = parentElement.attr('value')
						addedQuntity = jQuery(this).attr('data-quantity');
						total = parseInt(currQuantity) + parseInt(addedQuntity)
						console.log(total)
						parentElement.attr('value', String(total))
						jQuery('button[name=update_cart]').removeAttr('disabled')
					});
				}
				
				const updateCartArrows = () => {
					removeOldArrowsFromQuantity()
					addPlusMinusToQuantity()
					changeQuantity()
				}

				const addActiveToSidebar = () => {
					jQuery('#primary').addClass('sidebar-active')
				}

				const renderSidebarToggle = () => {
					sidebar = jQuery('#content')
					sidebar.before('<div class="col-full toggle-sidebar"> <i class="fa fa-bars"></i></div>')
				}

				const sidebarOff = () => {
					jQuery('#primary').toggleClass('sidebar-active')
					jQuery('#secondary').css('display', 'none')
					jQuery('#primary').animate({
						width: '100%',
					}, 500)
				}

				const sidebarOn = () => {
					jQuery('#primary').toggleClass('sidebar-active')
					jQuery('#secondary').css('display', '')
					jQuery('#primary').animate({
						width: '73.9130434783%',
					}, 500)
				}

				const toggleSidebar = () => {
					jQuery('.toggle-sidebar').on( 'click', function(){
						if (jQuery('#primary').hasClass('sidebar-active')) {
							sidebarOff()
						}
						else if (!jQuery('#primary').hasClass('sidebar-active')) {
							sidebarOn()
						}
					})
				}

				// Function calls
				moveCartAfterSearch()
				allPagesFloatRight()
				updateCartArrows()

			
				jQuery(document.body).on('updated_cart_totals', function() {
					updateCartArrows()
				})
			})
		</script>
	<?php
}
	
add_action( 'wp_head', 'sidebarToggle' );