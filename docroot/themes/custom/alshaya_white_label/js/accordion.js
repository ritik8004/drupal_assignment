/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

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
       * Toggles the footer accordions for desktop.
       */
      function toggleAccordion(){
        var desktopView = $(document).width();
        if(desktopView >= '768'){
          $('.c-footer-is-accordion').accordion('disable');
          $('.c-footer-is-accordion .ui-accordion-content').show();
        }
        else{
          $('.c-footer-is-accordion').accordion('enable');
        }
      }

      if (context === document) {
        moveContextualLink('.c-accordion');

        $('.c-facet__blocks').accordion({
          header: '.c-accordion__title'
        });

        $('.c-footer-is-accordion').accordion({
          header: 'h2',
          collapsible: true,
          active: false
        });
        // Toggle Accordion in desktop & mobile.
        toggleAccordion();
        $(window).on("resize", function(){
          toggleAccordion();
        });

        if ($('.c-facet__blocks__wrapper').length) {
          var facetBlockWrapper = $('.c-facet__blocks__wrapper').clone(true, true);
          var mainBlock = $('.block-system-main-block');
          var facetLabel = facetBlockWrapper.find('.c-facet__label');
          var facetBlock = facetBlockWrapper.find('.c-facet__blocks');

          facetBlockWrapper.addClass('c-facet__blocks__wrapper--mobile').addClass('is-filter');
          mainBlock.after(facetBlockWrapper);
          facetLabel.click(function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content').toggleClass('show-overlay');
            facetLabel.toggleClass('is-active');
            facetBlock.toggle();
          });
        }

        var viewFilter = $('.c-products-list .view-filters');
        viewFilter.addClass('is-filter');

        $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
      }
    }
  };

})(jQuery, Drupal);
