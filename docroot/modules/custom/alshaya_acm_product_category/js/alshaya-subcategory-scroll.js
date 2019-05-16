/**
 * @file
 * Sub category scroll.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.subCategoryScroll = {
    attach: function () {
      $('.sub-category').once().on('click', function() {
        var tid = $(this).attr('data-tid');
        scrollToCategoryHeader(tid);
      });

      // Hide the divs that do not have results.
      $('div.plp-subcategory-block').find('div.sub-category').each(function(){
        var tid = $(this).attr('data-tid');
        if($('term#' + tid).length === 0) {
          $(this).hide();
        }
      });

      /**
       * Animate & Scroll to subcategory header.
       *
       * @param {*} tid
       */
      function scrollToCategoryHeader(tid) {
        var element = '#' + tid;
        $('html, body').animate({
          scrollTop: ($(element).offset().top - $('.branding__menu').outerHeight() - 10)
        }, 500);
      }
    }
  };
})(jQuery, Drupal);
