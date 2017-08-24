/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.privilegeCardAccordion = {
    attach: function (context, settings) {
      var applyCoupon = $('#apply_coupon');
      applyCoupon.hide();
      if (context === document) {
        applyCoupon.prev().andSelf().wrapAll('<div class="card__content">');
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

      if (context === document) {
        // Toggle for Product description.
        $('.read-more-description-link').on('click', function () {
          $('.c-pdp .description-wrapper').toggleClass('desc-open');
          if ($(window).width() < 768) {
            $('.c-pdp .short-description-wrapper').toggle('appear');
            $('.c-pdp .description-wrapper').toggle('appear');
            if ($('.c-pdp .description-wrapper .show-less-link').length < 1) {
              $('.c-pdp .description-wrapper .field__content')
                .append('<div class="show-less-link">' + Drupal.t('Show less') + '</div>');
            }
          }
        });

        $('.close').on('click', function () {
          $('.c-pdp .description-wrapper').toggleClass('desc-open');
        });

        $(document).on('click', function (e) {
          if ($(e.target).closest('.c-pdp .content__sidebar').length === 0 && $('.c-pdp .description-wrapper').hasClass('desc-open')) {
            $('.c-pdp .description-wrapper').removeClass('desc-open');
          }
        });

        var mobileStickyHeaderHeight = $('.branding__menu').height();
        $('.c-pdp .description-wrapper .field__content').on('click', '.show-less-link', function () {
          if ($(window).width() < 768) {
            $('.c-pdp .short-description-wrapper').toggle('appear');
            $('.c-pdp .description-wrapper').toggle('appear');
            $('html,body').animate({
              scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight
            }, 'slow');
          }
        });

        moveContextualLink('.c-accordion');

        // Accordion for delivery option section on PDP.
        $('.delivery-options-wrapper').find('.c-accordion-delivery-options').each(function () {
          $(this).once('accordion-init').accordion({
            heightStyle: 'content',
            collapsible: true,
            active: false
          });
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
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          listOrder.not($ub).hide();
          $ub.parent().toggleClass('open--accordion');
          listOrder.not($ub).parent().removeClass('open--accordion');
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
        $('.collapse-row').slideUp();
        $('.product--count').on('click', function () {
          $('#edit-confirmation-continue-shopping')
            .toggleClass('expanded-table');
          $(this).toggleClass('expanded-row');
          $(this).nextAll('.collapse-row').slideToggle();
        });
      }

    }
  };

  Drupal.alshayaAccordion = function (element) {
    $(element).siblings().slideToggle('slow');
    $(element).toggleClass('ui-state-active');
  };

})(jQuery, Drupal);
