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
       * Toggles the Tabs.
       */
      if ($('.checkout .multistep-checkout').length) {
        $('.multistep-checkout legend').click(function () {
          $(this).next('.fieldset-wrapper').slideToggle();
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
