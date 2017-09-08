(function ($) {
  'use strict';

  $(document).ready(function () {
    function checkIfEmpty(el) {
      var $el = $(el);
      if ($el.val().length > 0) {
        $el.addClass('input--active');
      }
      else {
        $el.removeClass('input--active');
      }
    }

    function checkBlur(el) {
      $(el).on('blur', function () {
        checkIfEmpty(el);
      });
    }

    var inputsWithLabel = $('.input-textual__container input');
    inputsWithLabel.each(function (_, el) {
      checkIfEmpty(el);
      checkBlur(el);
    });
  });
})(jQuery);
