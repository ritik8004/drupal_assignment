(function ($) {
  'use strict';

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for stock check on PLP & search pages.
   */
  Drupal.behaviors.alshaya_acm_product_stock_check = {
    attach: function (context, settings) {
      $('.views-element-container').find('.c-products__item article').once('js-event').each(function(){
        var productQuickeditLink = $(this).attr('data-quickedit-entity-id');
        var productId = productQuickeditLink.replace('node/', '');
        var productStock = $(this).find('.out-of-stock');

        $.ajax({
          url: Drupal.url('stock-check-ajax/' + productId),
          success: function (result) {
            productStock.html(result);
          }
        });
      });
    }
  };
})(jQuery);
