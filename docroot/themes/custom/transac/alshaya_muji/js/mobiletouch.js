(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.mobiletouchJS = {
    attach: function () {

      // add selector for all the elements for touch handling here.
      var elementsList = [
        'header.c-header .block-alshaya-react-mini-cart .cart-link',
        '.c-menu-primary .store'
      ];

      for (var i = 0; i < elementsList.length; i++) {
        document.querySelector(elementsList[i]).addEventListener('touchstart', handleTouchStart);
        document.querySelector(elementsList[i]).addEventListener('touchend', handleTouchEnd);
      }

      function handleTouchStart(event) {
        var target = $(event.target) || $(event.srcElement);
        if (target && !target.hasClass('isTouching')) {
          $(target).addClass('isTouching');
        }
      }

      function handleTouchEnd(event) {
        var target = $(event.target) || $(event.srcElement);
        if (target && target.hasClass('isTouching')) {
          target.removeClass('isTouching');
        }
      }
    }
  };
})(jQuery, Drupal);
