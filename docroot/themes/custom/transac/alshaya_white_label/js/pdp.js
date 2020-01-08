/**
 * @file
 * PDP JS.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.pdpJs = {
    attach: function (context, settings) {
      function moveContextualLink(parent, body) {
        if (typeof body === 'undefined') {
          body = '.c-accordion__title';
        }
        $(parent).each(function () {
          var contextualLink = $(this).find(body).next();
          $(this).append(contextualLink);
        });
      }

      $('#drupal-modal .short-description-wrapper').once('readmore').each(function () {
        $(this).on('click', '.read-more-description-link-gift', function () {
          $(this).parent().toggleClass('show-gift-detail');
          $(this).parent().find('.desc-wrapper:first-child').hide();
          $(this).parent().find('.desc-wrapper:not(:first-child)').slideToggle('slow');
          $(this).parent().scroll();
          $(this).replaceWith('<span class="show-less-link">' + Drupal.t('show less') + '</span>');
        });
        $(this).on('click', '.show-less-link', function () {
          $(this).parent().toggleClass('show-gift-detail');
          $(this).parent().find('.desc-wrapper:first-child').show();
          $(this).parent().find('.desc-wrapper:not(:first-child)').slideToggle('slow');
          $(this).replaceWith('<span class="read-more-description-link-gift">' + Drupal.t('Read more') + '</span>');
        });
      });

      if (context === document) {
        // Toggle for Product description.
        $('.read-more-description-link').once('readmore').on('click', function () {
          // Close click and collect all stores wrapper if open.
          if ($('.click-collect-all-stores').hasClass('desc-open')) {
            $('.click-collect-all-stores').toggleClass('desc-open');
          }
          $('.c-pdp .description-wrapper').toggleClass('desc-open');
        });
        var mobileStickyHeaderHeight = $('.branding__menu').height();

        $('.c-pdp .short-description-wrapper', context).once('readmore').each(function () {
          $(this).on('click', '.read-more-description-link-mobile', function () {
            $(this).parent().toggleClass('show-detail');
            $(this).parent().find('.desc-wrapper:first-child').hide();
            $(this).parent().find('.desc-wrapper:not(:first-child)').slideToggle('slow');
            $(this).replaceWith('<span class="show-less-link">' + Drupal.t('show less') + '</span>');
          });
          $(this).on('click', '.show-less-link', function () {
            $(this).parent().toggleClass('show-detail');
            $(this).parent().find('.desc-wrapper:first-child').show();
            $(this).parent().find('.desc-wrapper:not(:first-child)').slideToggle('slow');
            $(this).replaceWith('<span class="read-more-description-link-mobile">' + Drupal.t('Read more') + '</span>');
            $('html,body').animate({
              scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight
            }, 'slow');
          });
        });

        $('.close').once('readmore').on('click', function () {
          $('.c-pdp .description-wrapper').toggleClass('desc-open');
        });

        $(document).on('click', function (e) {
          if ($(e.target).closest('.c-pdp .content__sidebar .short-description-wrapper').length === 0 && $(e.target).closest('.c-pdp .content__sidebar .description-wrapper').length === 0 && $('.c-pdp .description-wrapper').hasClass('desc-open')) {
            $('.c-pdp .description-wrapper').removeClass('desc-open');
          }
        });

        moveContextualLink('.c-accordion');

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
        $('.delivery-options-wrapper').find('.c-accordion-delivery-options').each(function () {
          Drupal.convertIntoAccordion($(this));
        });

        // Accordion for dimensions and care section on PDP.
        $('.content--dimensions-and-care').find('.dimensions-and-care').each(function () {
          Drupal.convertIntoAccordion($(this));
        });
      }
    }
  };
})(jQuery, Drupal);
