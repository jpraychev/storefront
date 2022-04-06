<?php

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array( 'parenthandle' ), 
        wp_get_theme()->get('Version') // this only works if you have Version in the style header
    );
}


function sidebarToggle() {
	?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				
				const isFrontPage = () => {
					wordpressURL = 'http://localhost/glasses/shop/'
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