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
        console.log(tid);
        scrollToCategoryHeader(tid);
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
