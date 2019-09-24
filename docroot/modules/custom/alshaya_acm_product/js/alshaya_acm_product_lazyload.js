/**
 * @file
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAcmProductLazyLoad = {
    attach: function (context, settings) {
      $('.lazyload-product').once('alshayaAcmProductLazyLoad').each(products, function (key, product) {
        var product_nid = $(product).find('.placeholder-lazyload-product').attr('data-id');
        $.ajax({
          url: Drupal.url('/product-list-view/' + product_nid),
          method: 'GET',
          dataType: 'HTML',
          cache: true,
          async: true,
          success: function (data) {
            $(product).html(data);

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.revalidate();
            }
          }
        });
      });
    }
  };

})(jQuery, Drupal);
