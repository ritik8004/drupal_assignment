/**
 * @file
 * Contains utility function for wishlist add to cart.
 */

(function ($, Drupal) {
	'use strict';

	Drupal.behaviors.alshayaWishlistAddToCart = {
		attach: function (context) {
		// When product is added to cart, it should be removed from wishlist.
		// For this, we are dispatching an event when product added to cart.
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

})(jQuery, Drupal);
