/**
 * @file
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAcmProductLazyLoad = {
    attach: function (context, settings) {
      $('.lazyload-product').once('alshayaAcmProductLazyLoad').each(function () {
        var that = $(this);
        var product_nid = that.find('.placeholder-lazyload-product').attr('data-id');

        var loadProduct = new Drupal.ajax({
          url: Drupal.url('product-list-view/' + product_nid),
          element: false,
          base: false,
          progress: {type: 'none'},
          submit: {js: true}
        });

        loadProduct.options.type = 'GET';
        loadProduct.options.async = true;
        loadProduct.execute();
      });
    }
  };

})(jQuery, Drupal);
