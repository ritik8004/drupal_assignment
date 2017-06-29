(function ($) {
  'use strict';

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for product page.
   */
  Drupal.behaviors.alshaya_acm_product_stock_check = {
    attach: function (context, settings) {
      $('.views-element-container').find('.c-products__item article').once('js-event').each(function(){
        var product_quickedit_link = $(this).attr('data-quickedit-entity-id');
        var product_id = product_quickedit_link.replace('node/', '');
        var product_stock = $(this).find('.out-of-stock');

        $.ajax({
          url: Drupal.url('stock-check-ajax/' + product_id),
          success: function (result) {
            product_stock.html(result);
          }
        });
      });
    }
  };
})(jQuery);
