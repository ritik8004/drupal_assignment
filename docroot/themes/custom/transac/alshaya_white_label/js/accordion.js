/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.privilegeCardAccordion = {
    attach: function (context, settings) {
      var applyCoupon = $('#coupon-button');
      if (context === document) {
        applyCoupon.prev().addBack().wrapAll('<div class="card__content">');
      }
      $('.coupon-code-wrapper, .alias--cart #details-privilege-card-wrapper').each(function () {
        if (context === document) {
          var error = $(this).find('.form-item--error-message');
          var active = false;
          if (error.length > 0) {
            active = 0;
          }
          $(this).accordion({
            header: '.card__header',
            collapsible: true,
            heightStyle: 'content',
            active: active
          });
        }
      });

      $('.alias--user-register #details-privilege-card-wrapper').each(function () {
        if (context === document) {
          var error = $(this).find('.form-item--error-message');
          var active = false;
          if (error.length > 0) {
            active = 0;
          }

          $(this).accordion({
            header: '.privilege-card-wrapper-title',
            collapsible: true,
            active: active
          });
        }
      });
      $('.path--user #details-privilege-card-wrapper').each(function () {
        if (context === document) {
          var error = $(this).find('.form-item--error-message');
          var active = false;
          if (error.length > 0) {
            active = 0;
          }

          $(this).accordion({
            header: '.privilege-card-wrapper-title',
            collapsible: true,
            active: active
          });
        }
      });
    }
  };

  Drupal.behaviors.accordion = {
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

      /**
       * Toggles the footer accordions.
       */

      if ($('.c-footer-is-accordion').length) {
        var accordionHead = $('.c-footer-is-accordion .is-accordion');
        var accordionBody = $(accordionHead).nextAll();

        $(accordionBody).addClass('accordion--body');
        $(accordionHead).once().click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          accordionBody.not($ub).slideUp();
          $ub.parent().toggleClass('open--accordion');
          accordionBody.not($ub).parent().removeClass('open--accordion');
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

        // Accordion for department page category for mobile.
        $('.paragraph--type--product-carousel-category').find('.c-accordion-delivery-options').each(function () {
          if ($(this).find('ul').length > 0) {
            Drupal.convertIntoAccordion($(this));
          }
          else {
            $(this).addClass('empty-accordion-delivery-options');
          }
          // Add class on parent of c-accordion-delivery-options so we can hide
          // the paragraph with margin in desktop.
          $(this).parents('.c-promo__item').addClass('c-accordion-delivery-option-parent');
        });
      }

      $('.c-facet__blocks')
        .find('.c-accordion__title')
        .off()
        .on('click', function (e) {
          Drupal.alshayaAccordion(this);
        });

      /**
       * Toggles the Expand Order Accordions.
       */

      if ($('.recent__orders--list .order-summary-row').length) {
        var parentOrder = $('.recent__orders--list .order-summary-row');
        var listOrder = $('.recent__orders--list .order-item-row');

        $(listOrder).hide();

        $(parentOrder).click(function () {
          var $ub = $(this).nextAll().stop(true, true).fadeToggle('slow');
          listOrder.not($ub).hide();
          $ub.parent().toggleClass('open--accordion');
          listOrder.not($ub).parent().removeClass('open--accordion');

          if (typeof Drupal.blazyRevalidate !== 'undefined') {
            Drupal.blazyRevalidate();
          }
        });
      }

      /**
       * Toggles the Tabs.
       */
      if ($('.checkout .multistep-checkout').length) {
        $('.multistep-checkout legend').click(function () {
          $(this).next('.fieldset-wrapper').slideToggle();
        });
      }

      /**
       * Toggles the Search on Order list.
       */
      if ($('.alshaya-acm-customer-order-list-search').length) {
        $('.alshaya-acm-customer-order-list-search label')
          .on('click', function () {
            $('.alshaya-acm-customer-order-list-search')
              .toggleClass('active--search');
          });
      }

      /**
       * Toggles the Order confirmation table.
       */
      if ($('.multistep-checkout .user__order--detail').length) {
        $('.collapse-row').fadeOut();
        $('.product--count').on('click', function () {
          $('#edit-confirmation-continue-shopping')
            .toggleClass('expanded-table');
          $(this).toggleClass('expanded-row');
          $(this).nextAll('.collapse-row').fadeToggle('slow');
        });
      }

    }
  };

  Drupal.alshayaAccordion = function (element) {
    $(element).siblings().slideToggle('slow');
    $(element).toggleClass('ui-state-active');
    $(element).parent().toggleClass('facet-active');
    if ($(element).hasClass('ui-state-active')) {
      $(element).siblings('.facets-soft-limit-link').show();
    }
    else {
      $(element).siblings('.facets-soft-limit-link').hide();
    }
  };

})(jQuery, Drupal);
