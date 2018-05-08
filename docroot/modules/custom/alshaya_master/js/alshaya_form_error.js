/**
* @file
* Custom JS to mobile number field to have prefix.
*/

/**
* @namespace
*/
Drupal.alshayaFormError = Drupal.alshayaFormError || {};

(function ($, Drupal) {
'use strict';

  // Focus on first error.
  $.fn.firstErrorFocus = function (arg, scroll) {
    // We doing this as at this point of time, process is not fully completed
    // so we relying on this ajaxComplete. This will only be called when there
    // is error on address book form.
    $(document).ajaxComplete(function(event, xhr, settings) {
      var focusElement = $(arg+ ' .error:first');

      focusElement.focus();

      // Scroll to the first element with error.
      if (scroll) {
        // Sticky header is not on cart/checkout/* pages.
        var stickyHeaderHeight = ($('.branding__menu').length > 0) ? $('.branding__menu').height() + 40 : 40;
        $('html, body').animate({
            scrollTop: focusElement.offset().top - parseInt(stickyHeaderHeight)
        });
      }
    });
  };

Drupal.behaviors.alshayaFormError = {
  attach: function (context, settings) {
    $(context).find('form').each(function() {
      $(this).on('submit.validate', function() {
        var form_element = this;
        // This is because we getting some race condition.
        setTimeout(function() {
          Drupal.setFocusToFirstError($(form_element));
        }, 100);
      });
    });
  }
};

/**
* Helper function to set focus to first error element in the form.
*/
Drupal.setFocusToFirstError =  function(errorElement) {
  try {
    var focusElement = errorElement.find('input.error:first');
    var stickyHeaderHeight = $('branding__menu').height();

    focusElement.focus();

    // Scroll to the first element with error.
    $('html, body').animate({
      scrollTop: focusElement.offset().top + parseInt(stickyHeaderHeight)
    });
  }
  catch (e) {
  }
};

})(jQuery, Drupal);
