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

      // Stock check on PDP main product / product in modal view.
      $('.add-to-cart-form-placeholder').once('js-event').each(function () {
        var $wrapper = $(this).closest('article');
        var skuId = $wrapper.attr('data-skuid');
        if (typeof skuId !== 'undefined') {
          $(this).trigger('click');
        }
      });

      // Check stock for mobile & load add cart form if stock-check successful.
      if ($(window).width() < 768) {
        $(window).on('load', function () {
          // Load cart form only for active carousel items on page load on PDP & Basket.
          $('.owl-item.active').each(function () {
            var activeItem = $(this);
            Drupal.loadTeaserCartForm(activeItem);
          });

          // Load cart form for active item post swipe on PDP & Basket.
          $('.owl-item').each(function () {
            var currItem = $(this);
            var observerConfig = {
              attributes: true
            };

            var owlItemActiveObserver = new MutationObserver(function (mutations) {
              mutations.forEach(function (mutation) {
                var newVal = $(mutation.target).prop(mutation.attributeName);
                if (mutation.attributeName === "class") {
                  if (newVal.indexOf('active') !== -1) {
                    Drupal.loadTeaserCartForm($(mutation.target));
                  }
                }
              });
            });

            owlItemActiveObserver.observe(currItem[0], observerConfig);
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

        // Load cart form for items which are not in the carousel on PDP & Basket on AJAX update.
        // We need to check classes on context here since the response from configurable_ajax_callback
        // returns the horizontal cross/up-sell wrapper.
        if ($(context).is('.horizontal-crossell.mobile-only-block') || $(context).is('.horizontal-upell.mobile-only-block')) {
          var viewRowCount = $(context).find('.views-row').length;
          if ((viewRowCount > 0) && (viewRowCount <= 3)) {
            $(context).find('.views-row').each(function () {
              var mobileItem = $(context).find('.mobile--only--sell');
              if (mobileItem.length !== 0) {
                Drupal.loadTeaserCartForm($(context));
              }
            });
          }
        }
      }
    }
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
        $wrapper.find('.add-to-cart-form-placeholder-teaser').trigger('click');
      }
    }
  };

  /**
   * Mark product out of stock.
   *
   * @param data
   *   SKU selector.
   */
  $.fn.markProductStockStatusAction = function (data, status) {
    var article = $(data).parents('article:first');
    if (status <= 0) {
      article.addClass('product-out-of-stock');
      article.find('.sharethis-wrapper').addClass('out-of-stock');
      article
        .find('#pdp-home-delivery.home-delivery')
        .accordion('option', 'disabled', true);
    }
  };

})(jQuery, Drupal);
