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
    $(document).ajaxComplete(function (event, xhr, settings) {
      var focusElement = $(arg + ' .error:first');

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
    $(context).find('form').each(function () {
      $(this).on('submit.validate', function () {
        var form_element = this;
        // This is because we getting some race condition.
        setTimeout(function () {
          Drupal.setFocusToFirstError($(form_element));
        }, 100);
      });
    });

    /**
     * Create Subscriber.
     *
     * @param {*} mutations
     */
    function subscriber(mutations) {
      mutations.forEach(function (mutation) {
        // Only when Mutation is for changes in childlist.
        if (mutation.type === 'childList') {
          var addedNode = mutation.addedNodes[0];
          // Check if added node is inline clientside error label.
          if ($(addedNode).is('label') && $(addedNode).hasClass('error')) {
            // Check if we have a BE message lying around.
            if ($(addedNode).siblings('.form-item--error-message').length) {
              // Remove the message to avoid two error messages.
              $(addedNode).siblings('.form-item--error-message').remove();
            }
          }
        }
      });
    }

    // Only on Change password form under my account.
    if ($('form.change-pwd-form').length > 0) {
      // Mutation observer to remove the BE validation message if we also get a
      // FE validaion message for change password fields.
      const target1 = document.querySelector('.change-pwd-form .form-item-current-pass');
      const target2 = document.querySelector('.change-pwd-form .form-item-pass');
      const observerConfig = {
        childList: true,
        attributes: true,
        attributeOldValue: true,
      };

      // Create observer.
      const observer = new MutationObserver(subscriber);

      // Observing targets.
      observer.observe(target1, observerConfig);
      observer.observe(target2, observerConfig);
    }
  }
};

/**
* Helper function to set focus to first error element in the form.
*/
Drupal.setFocusToFirstError = function (errorElement) {
  try {
    var focusElement = errorElement.find('input.error:first');
    var stickyHeaderHeight = ($('.branding__menu').length > 0) ? $('.branding__menu').height() + 40 : 40;

    focusElement.focus();

    // Scroll to the first element with error.
    $('html, body').animate({
      scrollTop: focusElement.offset().top - parseInt(stickyHeaderHeight)
    });
  }
  catch (e) {
  }
};

})(jQuery, Drupal);
