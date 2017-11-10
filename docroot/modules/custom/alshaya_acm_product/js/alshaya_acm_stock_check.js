(function ($, Drupal) {
  'use strict';

  var query_params = window.location.search;

  // Make sure query params coming from parent url are passed as is.
  if (query_params) {
    query_params = query_params.replace('?', '');
  }

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
      // Stock check on PLP, Search and Promo pages and Related products block.
      $('.stock-placeholder', context).once('check-stock').each(function () {
        var placeHolder = $(this);

        if (placeHolder.parents('.mobile-related-wrapper').length > 0) {
          return;
        }

        var articleNode = placeHolder.closest('article');
        var productId = articleNode.attr('data-nid');
        var productStock = articleNode.find('.out-of-stock');

        $.ajax({
          url: Drupal.url('stock-check-ajax/node/' + productId) + '?cacheable' + '&' + query_params,
          type: 'GET',
          dataType: 'json',
          async: true,
          success: function (result) {
            productStock.html(result.html);
          }
        });
      });

      // Stock check on PDP main Product.
      $('article[data-vmode="full"]', context).find('.basic-details-wrapper article .stock-checker').once('js-event').each(function () {
        var $wrapper = $(this).closest('article');
        var skuId = $wrapper.attr('data-skuid');
        if (typeof skuId !== 'undefined') {
          $.ajax({
            url: Drupal.url('get-cart-form/acq_sku/' + skuId) + '?' + query_params,
            type: 'GET',
            dataType: 'json',
            async: true,
            success: function (result) {
              $wrapper.html(result.html);

              // Add class to share this wrapper if product out of stock.
              if (result.max_quantity <= 0) {
                var $article = $wrapper.closest('article[data-vmode="full"]');
                $article.find('.sharethis-wrapper').addClass('out-of-stock');

                // Add out of stock class to article to allow styles to be added everywhere.
                $article.addClass('product-out-of-stock');
              }

              Drupal.attachBehaviors($wrapper[0]);
              Drupal.reAttachAddCartAndConfigSizeAjax($wrapper[0]);
            }
          });
        }
      });

      // Check stock for mobile & load add cart form if stock-check successful.
      if ($(window).width() < 768) {
        $(window).load(function () {
          // Load cart form only for active carousel items on page load on PDP & Basket.
          $('.owl-item.active').each(function () {
            var activeItem = $(this);
            Drupal.loadTeaserCartForm(activeItem);
          });

          // Load cart form for active item post swipe on PDP & Basket.
          $('.owl-item').each(function () {
            var currItem = $(this);
            var currParent = currItem.closest('.mobile-only-block');
            // Handle owl carousel on Basket page.
            if (currParent.length === 0) {
              currParent = currItem.closest('#block-baskethorizontalproductrecommendation.horizontal-crossell');
            }

            currItem.swipe({
              swipeStatus: function (event, phase, direction, distance, fingerCount) {
                switch (phase) {
                  case 'end':
                    setTimeout(function () {
                      var activeItem = currParent.find('.owl-item.active');
                      Drupal.loadTeaserCartForm(activeItem);
                    }, '1000');
                    break;
                }
              }
            });
          });

          // Load cart form for items which are not in the carousel on PDP & Basket.
          $('.horizontal-crossell.mobile-only-block, .horizontal-upell.mobile-only-block, #block-baskethorizontalproductrecommendation.horizontal-crossell, #block-baskethorizontalproductrecommendation.horizontal-upell', context).each(function () {
            var viewRowCount = $(this).find('.views-row').length;
            if ((viewRowCount > 0) && (viewRowCount <= 3)) {
              $(this).find('.views-row').each(function () {
                var mobileItem = $(this).find('.mobile--only--sell');
                if (mobileItem.length !== 0) {
                  Drupal.loadTeaserCartForm($(this));
                }
              });
            }
          });
        });
      }

      // Check stock for modal & load add cart form if stock-check successful.
      $('article[data-vmode="modal"]').find('.basic-details-wrapper article .stock-checker').once('js-event').each(function () {
        var $wrapper = $(this).closest('article');
        var skuId = $wrapper.attr('data-skuid');
        var stockCheckProcessed = 'stock-check-processed';
        if ((skuId !== undefined) && (!$(this).closest('article[data-vmode="modal"]').hasClass(stockCheckProcessed))) {
          $.ajax({
            url: Drupal.url('get-cart-form/acq_sku/' + skuId) + '?' + query_params,
            type: 'GET',
            dataType: 'json',
            async: true,
            success: function (result) {
              $wrapper.html(result.html);
              // Add class to share this wrapper if product out of stock.
              if (result.max_quantity <= 0) {
                $wrapper.closest('article[data-vmode="modal"]').find('sharethis-wrapper').addClass('out-of-stock');
              }
              $wrapper.closest('article[data-vmode="modal"]').addClass(stockCheckProcessed);
              Drupal.attachBehaviors($wrapper[0]);
              Drupal.reAttachAddCartAndConfigSizeAjax($wrapper[0]);
            }
          });
        }
      });

      // Remove checking stock message from the response for configurable size AJAX.
      $(document).ajaxComplete(function (xhr, event, settings) {
        if (settings.hasOwnProperty('extraData') && (settings.extraData._triggering_element_name === 'configurables[size]')) {
          $('.stock-checker').remove();
        }
      });
    }
  };

  /**
   * Helper function to re-attach AJAX settings to add-cart button & config sizes.
   *
	 * @param element
	 */
  Drupal.reAttachAddCartAndConfigSizeAjax = function (element) {
    var postUrl = $(element).find('form').attr('action') + '?ajax_form=1';
    var editCartElementSettings = {
      callback: 'alshaya_acm_cart_notification_form_submit',
      dialogType: 'ajax',
      event: 'mousedown',
      keypress: true,
      prevent: 'click',
      selector: '.edit-add-to-cart',
      submit: {
        _triggering_element_name: 'op',
        _triggering_element_value: Drupal.t('add to cart')
      },
      url: postUrl,
      wrapper: 'cart_notification'
    };

    var editConfigSizeElementSettings = {
      callback: 'alshaya_acm_product_configurable_form_ajax_callback',
      dialogType: 'ajax',
      event: 'change',
      selector: "[data-drupal-selector='edit-configurables-size']",
      progress: {
        message: null,
        type: 'throbber'
      },
      submit: {
        _triggering_element_name: 'configurables[size]'
      },
      url: postUrl
    };

    var editConfigCastorIdElementSettings = {
      callback: 'alshaya_acm_product_configurable_form_ajax_callback',
      dialogType: 'ajax',
      event: 'change',
      selector: "[data-drupal-selector='edit-configurables-article-castor-id']",
      progress: {
        message: null,
        type: 'throbber'
      },
      submit: {
        _triggering_element_name: 'configurables[article_castor_id]'
      },
      url: postUrl
    };

    // Re-attach Ajax to add-to-cart buttons, since there are duplicate ids on the page, Drupal will attach
    // AJAX only with the first button it finds.
    $(element).find('.edit-add-to-cart').each(function () {
      var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
      var is_modal_product = $(this).closest('#drupal-modal');
      var sku_id = $(this).siblings('input[name="sku_id"]').val();
      if (((is_mobile_only_sell.length > 0) || (is_modal_product.length > 0)) && (!$(this).hasClass('reattached-ajax'))) {
        $(this).off();
        $(this).addClass('reattached-ajax');
        var addCartBase = 'edit-add-to-cart_mobile--only--sell--' + sku_id;
        Drupal.ajax[addCartBase] = new Drupal.Ajax(addCartBase, this, editCartElementSettings);
      }
    });

    $("select[data-drupal-selector='edit-configurables-size']").each(function () {
      var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
      var is_modal_product = $(this).closest('#drupal-modal');
      var sku_id = $(this).siblings('input[name="sku_id"]').val();
      if (((is_mobile_only_sell.length > 0) || (is_modal_product.length > 0)) && (!$(this).hasClass('reattached-size-ajax'))) {
        $(this).addClass('reattached-size-ajax');
        var configSizeBase = 'edit-configurables-size_mobile--only--sell--' + sku_id;
        Drupal.ajax[configSizeBase] = new Drupal.Ajax(configSizeBase, this, editConfigSizeElementSettings);
      }
    });

    $("select[data-drupal-selector='edit-configurables-article-castor-id']").each(function () {
      var is_mobile_only_sell = $(this).closest('.mobile--only--sell');
      var is_modal_product = $(this).closest('#drupal-modal');
      var sku_id = $(this).siblings('input[name="sku_id"]').val();
      if (((is_mobile_only_sell.length > 0) || (is_modal_product.length > 0)) && (!$(this).hasClass('reattached-castorid-ajax'))) {
        $(this).addClass('reattached-castorid-ajax');
        var configCastorIdBase = 'edit-configurables-castorid_mobile--only--sell--' + sku_id;
        Drupal.ajax[configCastorIdBase] = new Drupal.Ajax(configCastorIdBase, this, editConfigCastorIdElementSettings);
      }
    });
  };

  /**
   * Helper function to load cart form for the carousel active item.
   *
	 * @param activeitem
	 */
  Drupal.loadTeaserCartForm = function (activeitem) {
    if (activeitem.find('.mobile--only--sell')) {
      var activeMobileItem = activeitem.find('.mobile--only--sell');
      var skuArticle = activeMobileItem.find('article');
      var skuId = skuArticle.attr('data-skuid');
      if (!(skuArticle.hasClass('stock-check-processed')) && (typeof skuId !== 'undefined')) {
        var $wrapper = skuArticle;
        $.ajax({
          url: Drupal.url('get-cart-form/acq_sku/' + skuId) + '?' + query_params,
          type: 'GET',
          dataType: 'json',
          async: true,
          success: function (result) {
            $wrapper.html(result.html);
            skuArticle.addClass('stock-check-processed');
            Drupal.attachBehaviors($wrapper[0]);
            Drupal.reAttachAddCartAndConfigSizeAjax($wrapper[0]);
          }
        });
      }
    }
  };

})(jQuery, Drupal);
