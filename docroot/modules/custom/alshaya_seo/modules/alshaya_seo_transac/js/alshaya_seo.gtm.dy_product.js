/**
 * @file
 * JS code to integrate DY products recommendation with GTM.
 */

 (function ($, Drupal) {
  'use strict';

  Drupal.alshayaSeoGtmProductRecomDy = Drupal.alshayaSeoGtmProductRecomDy || {};

  // Trigger product impressions on scroll.
  $(document).once('gtm-dy').on('scroll', debounce(function (event) {
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoGtmProductRecomDy.prepareProductImpressions, $('.dy_unit'), drupalSettings, event);
  }, 500));

  // Trigger product impressions on click of slider next/prev buttons.
  // Debouncing here adds some delay which helps to cover up for the time
  // which is taken be the slider movement animation.
  $(document).once('dy-product-slider').on('click', '.dy_unit .dy-recommendations-slider-arrows .dy-recommendations-slider-button--prev, .dy_unit .dy-recommendations-slider-arrows .dy-recommendations-slider-button--next', debounce(function (event) {
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoGtmProductRecomDy.prepareProductImpressions, $('.dy_unit'), drupalSettings, event);
  }, 500));

  /**
   * Prepares the impression list to send to Product Impressions event.
   *
   * @param {object} context
   *   The jQuery HTML wrapper object for which contains the list of items.
   * @param {string} eventType
   *   The type of event, eg. click, scroll etc.
   *
   * @return array
   *   Array of impressions.
   */
  Drupal.alshayaSeoGtmProductRecomDy.prepareProductImpressions = function (context, eventType) {
    var listName = Drupal.alshayaSeoGtmProductRecomDy.getRecommendationListName(context);
    var impressions = [];
    var dyPosition = context.find('.dy-recommendation-product.impression-processed:last').attr('dy-position');
    var i = (dyPosition) ? parseInt(dyPosition) + 1 : 1;
    context.find('.dy-recommendation-product:not(".impression-processed"):visible').each(function () {
      var condition = true;
       // Only on scroll we check if product is in view or not.
      if (eventType == 'scroll') {
        condition = $(this).isElementInViewPort(0, 4);
      }
      if (condition) {
        var impression = Drupal.alshayaSeoGtmProductRecomDy.getProductInfo($(this));
        impression.list = listName;
        impression.position = i;
        impressions.push(impression);
        $(this).addClass('impression-processed');
        $(this).attr('dy-position', i);
        i++;
      }
      if ((eventType === 'scroll' || eventType === 'click') && (impressions.length == drupalSettings.gtm.productImpressionDefaultItemsInQueue)) {
        // This is to break out from the .each() function.
        return false;
      }
    });

    return impressions;
  }

  /**
   * Get the list name for the recommended product.
   *
   * @param object element
   *   The jquery object of the recommended product.
   *
   * @return string
   *    The list name.
   */
  Drupal.alshayaSeoGtmProductRecomDy.getRecommendationListName = function (element) {
    var strategyId = element.closest('.dyMonitor').attr('data-dy-var-id');
    if ($('.dyMonitor').find('div.dy-404').length > 0) {
      return (productRecommendationsSuffix + 'page-404-' + strategyId).toLowerCase();
    }
    var gtmListName = $('body').attr('gtm-list-name');
    var label = $('.dy-recommendations__title-container').find('.dy-recommendations__title.title--eng').text();

    return (productRecommendationsSuffix + gtmListName.replace('placeholder', label) + '-' + strategyId).toLowerCase();
  }

   /**
   * Function to provide product data object.
   *
   * @param product
   *   jQuery object which contains all gtm attributes.
   */
  Drupal.alshayaSeoGtmProductRecomDy.getProductInfo = function (product) {
    // Convert the product to a jQuery object, if not already.
    if (!(product instanceof jQuery) && typeof product !== 'undefined') {
      product = $(product);
    }
    // Prepare default product data object.
    var productData = {
      name: '',
      id: product.attr('data-dy-sku'),
      price: 0,
      category: '',
      variant: '',
      dimension2: '',
      dimension3: '',
      dimension4: 'mediaCount',
    };

    try {
      // Remove comma from price before passing through parseFloat.
      var amount = product.find('.dy-recommendation-product__details').attr('data-price').replace(/\,/g,'');
      productData = {
        name: product.find('.dy-recommendation-product__detail--name').text(),
        id: product.attr('data-dy-sku'),
        price: parseFloat(amount),
        // @Todo Dy recommendation products
        // do not have below details.
        category: '',
        variant: '',
        dimension2: '',
        dimension3: '',
        dimension4: ''
      };

    }
    catch (error) {
      // In case of error.
      Drupal.logJavascriptError('Uncaught errors', error);
    }

    return productData;
  };

})(jQuery, Drupal);
