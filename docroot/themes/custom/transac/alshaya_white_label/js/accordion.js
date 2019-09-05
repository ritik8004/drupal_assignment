/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.privilegeCardAccordion = {
    attach: function (context, settings) {

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
