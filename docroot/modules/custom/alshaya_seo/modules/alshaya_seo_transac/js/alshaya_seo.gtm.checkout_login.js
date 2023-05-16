/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {

  $('body[gtm-container="checkout login page"]').once('gtm_checkout_login').each(function () {
    // @todo: Remove when we merge login section with checkout.
    var cart_data = Drupal.alshayaSpc.getCartData();
    if (cart_data) {
      Drupal.alshayaSeoSpc.loginData(cart_data);
    }
    // Tracking New customers.
    $('a[gtm-type="checkout-as-guest"]', $(this)).on('click', function () {
      Drupal.alshaya_seo_gtm_push_checkout_option('Guest Login', 1);
    });

    // Tracking Returning customers.
    $('[gtm-type="checkout-signin"]', $(this)).on('mousedown', function () {
      Drupal.alshaya_seo_gtm_push_checkout_option('New Login', 1);
    });
  });

})(jQuery, Drupal, dataLayer);
