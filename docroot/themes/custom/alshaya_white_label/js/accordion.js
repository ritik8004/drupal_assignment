/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      function moveContextualLink(parent, body) {
        $(parent).each(function () {
          var contextualLink = $(this).find('.c-accordion__title').next();
          $(this).append(contextualLink);
        });
      }
      if (context === document) {
        moveContextualLink('.c-accordion');

        $('.c-facet__blocks').accordion({
          header: '.c-accordion__title'
        });
        if ($('.c-facet__blocks__wrapper').length) {
          var facetBlockWrapper = $('.c-facet__blocks__wrapper').clone(true, true);
          var mainBlock = $('.block-system-main-block');
          var facetLabel = facetBlockWrapper.find('.c-facet__label');
          var facetBlock = $(facetBlockWrapper).find('.c-facet__blocks');

          facetBlockWrapper.addClass('c-facet__blocks__wrapper--mobile');
          mainBlock.after(facetBlockWrapper);
          facetLabel.click(function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content').toggleClass('show-overlay');
            facetLabel.toggleClass('is-active');
            facetBlock.toggle();
          });
        }
      }
    }
  };

  Drupal.behaviors.footeraccordion = {
    attach: function (context, settings) {
      $('.region__footer-primary').accordion({
        header: 'h2'
      });
    }
  };

})(jQuery, Drupal);
