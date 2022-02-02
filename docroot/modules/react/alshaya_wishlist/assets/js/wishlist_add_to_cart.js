/**
 * @file
 * Contains utility function for wishlist add to cart.
 */

(function ($, Drupal) {

  Drupal.behaviors.alshayaWishlistAddToCart = {
    attach: function (context) {
      // When product is added to cart, it should be removed from wishlist.
      // For this, we are dispatching an event when product added to cart.
      $('.sku-base-form', context).once('wishlist-button-wrapper').on('product-add-to-cart-success', function (event) {
        var sku = event.detail.productData.sku;
        if (sku && sku !== null) {
          var wishlistAddToCartEvent = new CustomEvent('onProductAddToCart', {
            bubbles: true,
            detail: { sku }
          });
          document.dispatchEvent(wishlistAddToCartEvent);
        }
      });
    }
  };

})(jQuery, Drupal);
