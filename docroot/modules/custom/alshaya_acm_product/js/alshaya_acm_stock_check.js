(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for stock check on PLP, search & PDP pages.
   */
  Drupal.behaviors.alshayaStockCheck = {
    attach: function (context, settings) {
      if (context === document) {
        // Stock check on PLP & search pages.
        $('.views-element-container').find('.c-products__item article').once('js-event').each(function(){
          var productId = $(this).attr('data-nid');
          var productStock = $(this).find('.out-of-stock');

          $.ajax({
            url: Drupal.url('stock-check-ajax/node/' + productId),
            type:"POST",
            contentType:"application/json;",
            dataType:"json",
            success: function (result) {
              productStock.html(result.html);
            }
          });
        });

        // Stock check on PDP full view mode.
        $('article[data-vmode="full"]').find('.basic-details-wrapper article').once('js-event').each(function(){
          var skuId = $(this).attr('data-skuid');
          if (skuId !== undefined) {
            var $wrapper = $(this);
            $.ajax({
              url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
              type:"POST",
              contentType:"application/json;",
              dataType:"json",
              success: function (result) {
                $wrapper.html(result.html);
                // Add class to share this wrapper if product out of stock.
                if (result.max_quantity <= 0) {
                  $wrapper.closest('article[data-vmode="full"]').find('sharethis-wrapper').addClass('out-of-stock');
                }
                Drupal.attachBehaviors($wrapper[0]);
              }
            });
          }
        });

        // $('.horizontal-crossell.mobile-only-block article[data-vmode="teaser"], .horizontal-upell.mobile-only-block article[data-vmode="teaser"]').find('article').once('js-event').each(function() {
        //   var skuId = $(this).attr('data-skuid');
        //   if (skuId !== undefined) {
        //     var $wrapper = $(this);
        //     $.ajax({
        //       url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
        //       type:"POST",
        //       contentType:"application/json;",
        //       dataType:"json",
        //       success: function (result) {
        //         $wrapper.html(result.html);
        //         Drupal.attachBehaviors($wrapper[0]);
        //       }
        //     });
        //   }
        // });
        // $('.content__sidebar article[data-vmode="teaser"]').find('table.mobile--only--sell article').once('js-event').each(function(){
        //   var skuId = $(this).attr('data-skuid');
        //   if (skuId !== undefined) {
        //     var $wrapper = $(this);
        //     $.ajax({
        //       url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
        //       type:"POST",
        //       contentType:"application/json;",
        //       dataType:"json",
        //       success: function (result) {
        //         $wrapper.html(result.html);
        //         // Add class to share this wrapper if product out of stock.
        //         if (result.max_quantity <= 0) {
        //           $wrapper.closest('article[data-vmode="full"]').find('sharethis-wrapper').addClass('out-of-stock');
        //         }
        //         Drupal.attachBehaviors($wrapper[0]);
        //       }
        //     });
        //   }
        // });
      }
    }
  };
})(jQuery, Drupal);
