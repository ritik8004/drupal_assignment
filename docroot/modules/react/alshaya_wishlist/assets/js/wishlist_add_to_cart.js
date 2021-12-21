/**
 * @file
 * Contains utility function for wishlist add to cart.
 */

(function ($, Drupal, drupalSettings) {
	'use strict';

	Drupal.behaviors.alshayaWishlistAddToCart = {
		attach: function (context) {
		// Trigger event for wishlist removal on product-add-to-cart-success.
		$('.sku-base-form', context).once('wishlist-button-wrapper').on('product-add-to-cart-success', function (event) {
					var productData = event.detail.productData;
					if (productData && productData !== null) {
						var wishlistAddToCartEvent = new CustomEvent('onProductAddToCart', {
							bubbles: true,
							detail: {
								productData,
							}
						});
						document.dispatchEvent(wishlistAddToCartEvent);
					}
		});
		}
	};

})(jQuery, Drupal, drupalSettings);
