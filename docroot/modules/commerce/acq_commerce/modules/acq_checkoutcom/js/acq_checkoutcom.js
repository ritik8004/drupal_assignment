/**
 * @file
 * JavaScript behaviors of acq_checkoutcom.form.js.
 */

(function ($, Drupal) {

  Drupal.checkoutComProcessed = false;
  var checkoutComOldFormData = '';

  // Display errors for form fields.
  $.fn.checkoutPaymentError = function (formErrors) {
    Drupal.checkoutComProcessed = false;
    for (var errorFieldName in formErrors) {
      Drupal.checkoutComShowError($('[name="' + errorFieldName + '"]'), formErrors[errorFieldName]);
    }
    $(document).trigger('checkoutcom_form_error');
  };

  // Helper method that will place errors.
  Drupal.checkoutComShowError = function (element, error) {
    var errorDiv = $('<div class="form-item--error-message" />');
    errorDiv.html(error);
    element.addClass('error');
    element.parent().find('.form-item--error-message').remove();
    element.parent().append(errorDiv);
  };

  // Helper method to display global error.
  Drupal.checkoutComShowGlobalError = function (error) {
    Drupal.checkoutComProcessed = false;
    var errorWrapper = $('<div class="messages__wrapper layout-container checkoutcom-global-error" />');
    var errorDiv = $('<div class="messages messages--error"></div>').html(error);
    errorWrapper.append(errorDiv);
    $('#payment_details_checkout_com').parents('form').find('.checkoutcom-global-error').remove();
    $('#payment_details_checkout_com').parents('form').prepend(errorWrapper);
    window.scrollTo(0, 0);
  };

  // Helper method to remove the name attributes to ensure it is not posted to server even by mistake.
  Drupal.checkoutComRemoveNameAttribute = function (form) {
    $(form).find('.payment_card_new').find('input:text, input[type="tel"], input:password, select').each(function () {
      $(this).data('name', $(this).attr('name'));
      $(this).removeAttr('name');
    });
  };

  Drupal.checkoutComValidateBeforeCheckout = function (form) {
    if (!$(form).valid()) {
      return;
    }

    Drupal.checkoutComRemoveNameAttribute(form);

    // Collect data to be processed.
    var formData = $(form).find('input:not(.checkoutcom-input), select:not(.checkoutcom-input)').serialize();

    // Validate form only when there's a change and form has any validation error.
    if (checkoutComOldFormData !== formData || Drupal.checkoutComProcessed === false) {
      // Store current billing address to validate again if there are any
      // change in already validated form.
      checkoutComOldFormData = formData;

      // Validate checkout.com payment form.
      Drupal.ajax({
        url: Drupal.url('checkoutcom/submit/payment-form'),
        element: $('#edit-actions-next').get(0),
        base: false,
        progress: {type: 'throbber'},
        submit: formData,
        dataType: 'json',
        type: 'POST',
      }).execute();
    }
  };

})(jQuery, Drupal);
