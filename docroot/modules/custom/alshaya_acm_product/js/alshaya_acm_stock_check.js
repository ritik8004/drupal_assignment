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
      // Stock check on PLP,search & Promo pages.
      $('article[data-vmode="search_result"]', context).each(function(){
        var productId = $(this).attr('data-nid');
        var productStock = $(this).find('.out-of-stock');

        $.ajax({
          url: Drupal.url('stock-check-ajax/node/' + productId),
          type: "GET",
          contentType: "application/json;",
          dataType: "json",
          success: function (result) {
            productStock.html(result.html);
          }
        });
      });

      // Stock check on PDP main Product.
      $('article[data-vmode="full"]', context).find('.basic-details-wrapper article').once('js-event').each(function(){
        var skuId = $(this).attr('data-skuid');
        if (skuId !== undefined) {
          var $wrapper = $(this);
          $.ajax({
            url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
            type: "GET",
            contentType: "application/json;",
            dataType: "json",
            success: function (result) {
              $wrapper.html(result.html);
              // Add class to share this wrapper if product out of stock.
              if (result.max_quantity <= 0) {
                $wrapper.closest('article[data-vmode="full"]').find('sharethis-wrapper').addClass('out-of-stock');
              }
              Drupal.attachBehaviors($wrapper[0]);
              Drupal.reAttachAddCartAndConfigSizeAjax(result.html);
            }
          });
        }
      });

      // Check stock for modal & load add cart form if stock-check successful.
      $('article[data-vmode="modal"]').find('.basic-details-wrapper article').once('js-event').each(function(){
        var skuId = $(this).attr('data-skuid');
        var stockCheckProcessed = 'stock-check-processed';
        if ((skuId !== undefined) && (!$(this).closest('article[data-vmode="modal"]').hasClass(stockCheckProcessed))) {
          var $wrapper = $(this);
          $.ajax({
            url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
            type: "GET",
            contentType: "application/json;",
            dataType: "json",
            success: function (result) {
              $wrapper.html(result.html);
              // Add class to share this wrapper if product out of stock.
              if (result.max_quantity <= 0) {
                $wrapper.closest('article[data-vmode="modal"]').find('sharethis-wrapper').addClass('out-of-stock');
              }
              $wrapper.closest('article[data-vmode="modal"]').addClass(stockCheckProcessed);
              Drupal.attachBehaviors($wrapper[0]);
              Drupal.reAttachAddCartAndConfigSizeAjax(result.html);
            }
          });
        }
      });

      // Check stock for mobile & load add cart form if stock-check successful.
      $('.horizontal-crossell.mobile-only-block article[data-vmode="teaser"], .horizontal-upell.mobile-only-block article[data-vmode="teaser"], .horizontal-crossell article[data-vmode="teaser"], .horizontal-upell article[data-vmode="teaser"]').find('article').once('js-event').each(function() {
        var skuId = $(this).attr('data-skuid');
        if (skuId !== undefined) {
          var $wrapper = $(this);

          $.ajax({
            url: Drupal.url('stock-check-ajax/acq_sku/' + skuId),
            type: "GET",
            contentType: "application/json;",
            dataType: "json",
            success: function (result) {
              $wrapper.html(result.html);
              Drupal.attachBehaviors($wrapper[0]);
              Drupal.reAttachAddCartAndConfigSizeAjax(result.html);
            }
          });
        }
      });

      // Remove checking stock message from the response for configurable size AJAX.
      $(document).ajaxComplete(function(xhr, event, settings) {
        if (settings.hasOwnProperty('extraData') && (settings.extraData._triggering_element_name === "configurables[size]")) {
          $('.stock-checker').remove();
        }
      });
    }
  };

  Drupal.reAttachAddCartAndConfigSizeAjax = function(element) {
    var editCartElementSettings = {
      'callback': "alshaya_acm_cart_notification_form_submit",
      'dialogType': "ajax",
      'event': "mousedown",
      'keypress': true,
      'prevent': "click",
      'selector': ".edit-add-to-cart",
      'submit': {
        _triggering_element_name: "op",
        _triggering_element_value: Drupal.t("add to cart")
      },
      'url': document.location.pathname + '?ajax_form=1',
      'wrapper': "cart_notification"
    };

    var editConfigSizeElementSettings = {
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

    // Re-attach Ajax to add-to-cart buttons, since there are duplicate ids on the page, Drupal will attach
    // AJAX only with the first button it finds.
    var sku_id = $(element).find('input[name="sku_id"]').val();

    $('.edit-add-to-cart').each(function() {
      var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
      var is_modal_product = $(this).closest('#drupal-modal');
      var sku_id = $(this).siblings('input[name="sku_id"]').val();
      if (((is_mobile_only_sell.length > 0) || (is_modal_product.length > 0)) && (!$(this).hasClass('reattached-ajax'))) {
        $(this).addClass('reattached-ajax');
        var addCartBase = 'edit-add-to-cart_mobile--only--sell--' + sku_id;
        Drupal.ajax[addCartBase] = new Drupal.Ajax(addCartBase, this, editCartElementSettings);
      }
    });

    $("select[data-drupal-selector='edit-configurables-size']").each(function() {
      var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
      var is_modal_product = $(this).closest('#drupal-modal');
      var sku_id = $(this).siblings('input[name="sku_id"]').val();
      if (((is_mobile_only_sell.length > 0) || (is_modal_product.length > 0)) && (!$(this).hasClass('reattached-size-ajax'))) {
        $(this).addClass('reattached-size-ajax');
        var configSizeBase = 'edit-configurables-size_mobile--only--sell--' + sku_id;
        Drupal.ajax[configSizeBase] = new Drupal.Ajax(configSizeBase, this, editConfigSizeElementSettings);
      }
    });
  };

})(jQuery, Drupal);
