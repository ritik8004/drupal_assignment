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

Drupal.behaviors.alshayaFormError = {
  attach: function (context, settings) {
    var observerConfig = {
      attributes: true
    };

    // Create an error Observer to test class change.
    var errorObserver = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        var newVal = $(mutation.target).prop(mutation.attributeName);
        if (mutation.attributeName === "class") {
          if (newVal.indexOf('error') !== -1) {
            Drupal.setFocusToFirstError($(mutation.target));
          }
        }
      });
    });

    // Attach error Observer to all form input elements.
    $('form').each(function() {
      $(this).find('input').each(function() {
        errorObserver.observe($(this)[0], observerConfig);
      });
    });
  }
};

/**
* Helper function to set focus to first error element in the form.
*/
Drupal.setFocusToFirstError =  function(errorElement) {
  var focusElement = errorElement.closest('form').find('input.error:first');
  var stickyHeaderHeight = $('branding__menu').height();
  var animateOptions = {
    duration: 5000,
    specialEasing: {
      width: "linear",
      height: "easeOutBounce"
    }
  };
  focusElement.focus();

  // Scroll to the first element with error.
  $('html, body').animate({
    scrollTop: focusElement.offset().top + parseInt(stickyHeaderHeight)
  }, animateOptions);
};

})(jQuery, Drupal);