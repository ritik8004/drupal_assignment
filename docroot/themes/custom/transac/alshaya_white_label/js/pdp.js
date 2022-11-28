/**
 * @file
 * PDP JS.
 */

(function ($, Drupal) {

  Drupal.behaviors.pdpJs = {
    attach: function (context, settings) {
      var node = $('.sku-base-form').not('[data-sku *= "#"]').parents('article.entity--type-node:first');

      function moveContextualLink(parent, body) {
        if (typeof body === 'undefined') {
          body = '.c-accordion__title';
        }
        $(parent).each(function () {
          var contextualLink = $(this).find(body).next();
          $(this).append(contextualLink);
        });
      }

      // Toggle for Product description.
      $('.read-more-description-link', context).once('readmore').on('click', function () {
        // Close click and collect all stores wrapper if open.
        if ($('.click-collect-all-stores').hasClass('desc-open')) {
          $('.click-collect-all-stores').toggleClass('desc-open');
        }
        $(this).parents('.short-description-wrapper').siblings('.description-wrapper').toggleClass('desc-open');
      });
      var mobileStickyHeaderHeight = $('.branding__menu').height();

      $(document).once('read-more-description-link-mobile').on('click', '.read-more-description-link-mobile', function () {
        $(this).parent().toggleClass('show-detail');
        $(this).parent().find('.desc-wrapper:first-child').hide();
        $(this).parent().find('.desc-wrapper:not(:first-child)').slideToggle('slow');
        $(this).replaceWith('<span class="show-less-link">' + Drupal.t('show less') + '</span>');
      });
      $(document).once('show-less-link').on('click', '.show-less-link', function () {
        $(this).parent().toggleClass('show-detail');
        $(this).parent().find('.desc-wrapper:not(:first-child)').slideToggle('slow');
        $(this).parent().find('.desc-wrapper:first-child').show();
        var animate = $(this).parents('.matchback-description-wrapper') ? false : true;
        if (animate) {
          $(this).replaceWith('<span class="read-more-description-link-mobile">' + Drupal.t('Read more') + '</span>');
          $('html,body').animate({
            scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight
          }, 'slow');
        }
        else {
          $(this).replaceWith('<span class="read-more-description-link-mobile matchback-readmore">' + Drupal.t('Read more') + '</span>');
        }
      });

      $('.close').once('readmore').on('click', function () {
        $(this).parents('.description-wrapper').toggleClass('desc-open');
      });

      $(document).once('remove-desc-class').on('click', function (e) {
        if ($(e.target).closest('.c-pdp .content__sidebar .short-description-wrapper').length === 0 && $(e.target).closest('.c-pdp .content__sidebar .description-wrapper').length === 0 && $('.c-pdp .description-wrapper').hasClass('desc-open')) {
          $('.c-pdp .description-wrapper').removeClass('desc-open');
        }
      });

      moveContextualLink('.acq-content-product .c-accordion');

      /**
       * Function to create accordion.
       *
       * @param {object} element
       *   The HTML element inside which we want to make accordion.
       */
      Drupal.convertIntoAccordion = function (element) {
        element.once('accordion-init').accordion({
          heightStyle: 'content',
          collapsible: true,
          active: false
        });
      };

      // Accordion for delivery option section on PDP.
      $('.delivery-options-wrapper', node).find('.c-accordion-delivery-options').each(function () {
        Drupal.convertIntoAccordion($(this));
        if ($(this).attr('data-state') === 'disabled') {
          $(this).accordion('option', 'disabled', true);
        }
      });

      // Accordion for dimensions and care section on PDP.
      $('.content--dimensions-and-care', context).find('.dimensions-and-care').each(function () {
        Drupal.convertIntoAccordion($(this));
      });

      // Accordion for product details section on PDP.
      $('.content--product-details', context).find('.product-details').each(function () {
        Drupal.convertIntoAccordion($(this));
      });
    }
  };
})(jQuery, Drupal);
