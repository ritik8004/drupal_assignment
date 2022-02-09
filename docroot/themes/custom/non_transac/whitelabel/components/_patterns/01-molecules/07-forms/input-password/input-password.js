(function ($) {

  $(document).ready(function () {
    function showPassword(el, elToggle) {
      el.attr('type', 'text');
      elToggle.text('Hide');
      el.addClass('input--password-visible');
    }

    function hidePassword(el, elToggle) {
      el.attr('type', 'password');
      elToggle.text('Show');
      el.removeClass('input--password-visible');
    }

    function showHidePass(elToggle, el) {
      elToggle.on('click', function () {
        if (el.val().length > 0 || el.hasClass('input--password-visible')) {
          if (el.hasClass('input--password-visible')) {
            hidePassword(el, elToggle);
          }
          else {
            showPassword(el, elToggle);
          }
        }
      });
    }

    function toggleVisible(visiblePassword, el) {
      visiblePassword.each(function (_, el) {
        var $el = $(el);
        var $elToggle = $(el).siblings('.input__password');
        hidePassword($el, $elToggle);
      });
    }

    function hideOnSubmit(el) {
      el.on('submit', function () {
        var visiblePassword = $(this).find('.input--password-visible');
        toggleVisible(visiblePassword, el);
        return true;
      });
    }

    var inputsWithPassword = $('.input--password input');
    inputsWithPassword.each(function (_, el) {
      var $el = $(el);
      var $elToggle = $(el).siblings('.input__password');
      showHidePass($elToggle, $el);
    });

    var forms = $('form');
    forms.each(function (_, el) {
      var $el = $(el);
      hideOnSubmit($el);
    });

  });
})(jQuery);
