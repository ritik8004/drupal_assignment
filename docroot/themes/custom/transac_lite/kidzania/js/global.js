/**
 * @file
 * Globaly required scripts.
 */

(function ($) {
  'use strict';

  $('.menu-toggle').on('click', function (e) {
    $('.menu-navigation').toggleClass('show-menu');
    e.preventDefault();
  });

})(jQuery);
