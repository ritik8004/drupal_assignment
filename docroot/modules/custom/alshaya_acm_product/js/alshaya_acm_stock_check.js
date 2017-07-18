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
      // Stock check on PLP & search pages.
      $('.views-element-container', context).find('.c-products__item article').once('js-event').each(function(){
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
      $('article[data-vmode="full"]', context).find('.basic-details-wrapper article').once('js-event').each(function(){
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

      $('article[data-vmode="modal"]').find('.basic-details-wrapper article').once('js-event').each(function(){
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

      $('.horizontal-crossell.mobile-only-block article[data-vmode="teaser"], .horizontal-upell.mobile-only-block article[data-vmode="teaser"]').find('article').once('js-event').each(function() {
        var skuId = $(this).attr('data-skuid');
        var editCartElementSettings, editConfigSizeElementSettings;
        if (skuId !== undefined) {
          var $wrapper = $(this);
          editCartElementSettings = {
            'callback': "alshaya_acm_cart_notification_form_submit",
            'dialogType': "ajax",
            'event': "mousedown",
            'keypress': true,
            'prevent': "click",
            'selector': ".edit-add-to-cart",
            'submit': {
              _triggering_element_name: "op",
              _triggering_element_value: "Add to cart"
            },
            'url': document.location.pathname + '?ajax_form=1',
            'wrapper': "cart_notification"
          };

          editConfigSizeElementSettings = {
            'callback': "alshaya_acm_product_configurable_form_ajax_callback",
            'dialogType': "ajax",
            'event': "change",
            'selector': "[data-drupal-selector='edit-configurables-size']",
            'progress': {
              'message': null,
              'type': 'throbber'
            },
            'submit': {
              _triggering_element_name: "configurables[size]"
            },
            "url": document.location.pathname + '?ajax_form=1'
          };

          $.ajax({
            url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
            type:"POST",
            contentType:"application/json;",
            dataType:"json",
            success: function (result) {
              $wrapper.html(result.html);
              Drupal.attachBehaviors($wrapper[0]);

              // Re-attach Ajax to add-to-cart buttons, since there are duplicate ids on the page, Drupal will attach
              // AJAX only with the first button it finds.
              if (editCartElementSettings) {
                var sku_id = $(result.html).find('input[name="sku_id"]').val();

                $('.edit-add-to-cart').each(function() {
                  var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
                  var sku_id = $(this).siblings('input[name="sku_id"]').val();
                  if ((is_mobile_only_sell.length > 0) && (!$(this).hasClass('reattached-ajax'))) {
                    $(this).addClass('reattached-ajax');
                    var addCartBase = 'edit-add-to-cart_mobile--only--sell--' + sku_id;
                    Drupal.ajax[addCartBase] = new Drupal.Ajax(addCartBase, this, editCartElementSettings);
                  }
                });

                $("select[data-drupal-selector='edit-configurables-size']").each(function() {
                  var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
                  var sku_id = $(this).siblings('input[name="sku_id"]').val();
                  if ((is_mobile_only_sell.length > 0) && (!$(this).hasClass('reattached-size-ajax'))) {
                    $(this).addClass('reattached-size-ajax');
                    var configSizeBase = 'edit-configurables-size_mobile--only--sell--' + sku_id;
                    Drupal.ajax[configSizeBase] = new Drupal.Ajax(configSizeBase, this, editConfigSizeElementSettings);
                  }
                });
              }
            }
          });
        }
      });
      $('.content__sidebar article[data-vmode="teaser"]').find('table.mobile--only--sell article').once('js-event').each(function(){
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
    }
  };
})(jQuery, Drupal);
