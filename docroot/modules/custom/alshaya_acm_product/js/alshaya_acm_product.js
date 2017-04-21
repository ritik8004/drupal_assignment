(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for product page.
   */
  Drupal.behaviors.alshaya_acm_product = {
    attach: function (context, settings) {
      // If we find the gallery in add cart form ajax response, we update the main gallery.
      if ($('.field--name-field-skus .cloud-zoom-container').size() > 0) {
        $('.gallery-wrapper .cloud-zoom-container').replaceWith($('.field--name-field-skus .cloud-zoom-container'));
        $('.gallery-wrapper .image-gallery-mobile').replaceWith($('.field--name-field-skus .image-gallery-mobile'));
        $('.gallery-wrapper .product-image-gallery-container').replaceWith($('.field--name-field-skus .product-image-gallery-container'));

        // Execute the attach function of alshaya_product_zoom again.
        Drupal.behaviors.alshaya_product_zoom.attach($('.gallery-wrapper'), settings);
      }
    }
  };

})(jQuery, Drupal);
