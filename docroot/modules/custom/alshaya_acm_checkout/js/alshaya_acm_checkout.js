(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for checkout flow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for checkout flow.
   */
  Drupal.behaviors.ZZAlshayaAcmCheckout = {
    attach: function (context, settings) {
      $('.delivery-home-next').once('bind-events').each(function () {
        $(this).hide();
      });

      // In order to show the form between radio buttons we do it
      // using custom markup. Here we update the radio buttons on
      // click of payment method names in custom markup.
      $('#payment_details_wrapper').once('bind-events').each(function () {
        $('.payment-plugin-wrapper-div', $(this)).on('click', function () {
          var selected_option = $(this).data('value');
          $('[data-drupal-selector="edit-acm-payment-methods-payment-options"]').find('input[value="' + selected_option + '"]').trigger('click');
        });
      });

      // Bind this only once after every ajax call.
      $('.cybersource-credit-card-input').once('validate-cc').each(function () {
        $(this).validateCreditCard(function (result) {
          // Reset error and card type active class.
          $(this).parent().removeClass('cc-error');
          $('.card-type').removeClass('active');

          // Don't do anything if card_type is null.
          if (result.card_type !== null) {
            switch (result.card_type.name) {
              case 'diners_club_carte_blanche':
              case 'diners_club_international':
                $('.card-type-diners-club').addClass('active');
                break;
              case 'visa':
              case 'mastercard':
              default:
                $('.card-type-' + result.card_type.name).addClass('active');
                break;
            }

            // Set error class on wrapper if invalid card number.
            if (!result.valid || !result.length_valid || !result.luhn_valid) {
              $(this).parent().addClass('cc-error');
            }
          }
        });
      });

      // Show/hide fields based on availability of shipping methods.
      $('#shipping_methods_wrapper').once('bind-events').each(function () {
        if ($('#shipping_methods_wrapper input:radio').length > 0) {
          $('#shipping_methods_wrapper fieldset').show();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').hide();
          $('[data-drupal-selector="edit-actions-next"]').show();
          $('#selected-address-wrapper').show();
          $('#shipping_methods_wrapper').show();
          $('.address-book-address').hide();

          // Select the first method by default.
          if ($('#shipping_methods_wrapper input[type="radio"]:checked').length === 0) {
            $('#shipping_methods_wrapper input[type="radio"]:first').trigger('click');
          }
        }
        else {
          $('#shipping_methods_wrapper fieldset').hide();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').show();
          $('[data-drupal-selector="edit-actions-next"]').hide();
          $('#selected-address-wrapper').hide();
          $('#shipping_methods_wrapper').hide();
          $('.address-book-address').show();
        }
      });

      $('#change-address').once('bind-events').each(function () {
        // We display the address boxes as is if we don't have any shipping method.
        if ($('#shipping_methods_wrapper input:radio').length === 0) {
          return;
        }

        $('#add-address-button').hide();
        $('#edit-member-delivery-home-header-add-profile').hide();
        $('#address-book-address').slideUp();
        $('#edit-member-delivery-home-addresses').hide();

        $(this).on('click', function (e) {
          e.preventDefault();

          $('#add-address-button').show();
          $('#selected-address-wrapper').slideUp();
          $('#shipping_methods_wrapper').slideUp();
          $('[data-drupal-selector="edit-actions-next"]').hide();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').show();
          $('#shipping_methods_wrapper').slideUp();
          $('#edit-member-delivery-home-addresses').slideDown();
          $('.address-book-address').slideDown();
          $('.delivery-address-title').html(Drupal.t('choose delivery address'));
          $('#edit-member-delivery-home-header-add-profile').show();
        });
      });

      $('#address-book-form-wrapper').once('bind-events').each(function () {
        $(this).hide();

        $('#add-address-button').on('click', function (event) {
          event.preventDefault();
          // Reset the form values.
          $('[data-drupal-selector="edit-member-delivery-home-address-form-address-id"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-given-name"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-family-name"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-mobile-number-mobile"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-locality"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line1"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-dependent-locality"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line2"]').val('');

          // Reset Mobile number prefix js.
          Drupal.alshayaMobileNumber.init($('[data-drupal-selector="edit-member-delivery-home-address-form-form-mobile-number-mobile"]'));

          // Show the form.
          $('#address-book-form-wrapper').slideDown();
        });

        $('#cancel-address-add-edit').on('click', function (event) {
          event.preventDefault();

          // Hide the form.
          $('#address-book-form-wrapper').slideUp();
        });
      });

      if (typeof Drupal.Ajax !== 'undefined' && typeof Drupal.Ajax.prototype.beforeSendAcmCheckout === 'undefined') {
        Drupal.Ajax.prototype.beforeSendAcmCheckout = Drupal.Ajax.prototype.beforeSend;
        Drupal.Ajax.prototype.successAcmCheckout = Drupal.Ajax.prototype.success;

        // See docroot/core/misc/ajax.js > Drupal.Ajax.prototype.beforeSend()
        Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
          // Invoke the original function.
          this.beforeSendAcmCheckout(xmlhttprequest, options);

          // Disable submit button.
          $('#edit-actions .form-submit').each(function () {
            if ($(this).prop('disabled') === false) {
              $(this).addClass('acm-checkout-ajax-disabled');
              $(this).prop('disabled', true);
            }
          });
        };

        // See docroot/core/misc/ajax.js > Drupal.Ajax.prototype.success()
        Drupal.Ajax.prototype.success = function (response, status) {
          // Invoke the original function.
          this.successAcmCheckout(response, status);

          // Disable submit button.
          $('.acm-checkout-ajax-disabled').each(function () {
            $(this).removeClass('acm-checkout-ajax-disabled');
            $(this).prop('disabled', false);
          });
        };
      }

      // Re-bind client side validations for billing address after form is updated.
      $('[data-drupal-selector="edit-billing-address-address-billing-given-name"]').once('bind-events').each(function () {
        Drupal.behaviors.cvJqueryValidate.attach($('#block-alshaya-white-label-content'));
      });

      // Show the form by default if user has no address saved in address book.
      $('#edit-member-delivery-home-addresses').once('member-has-address-check').each(function () {
        if ($('.views-row', $(this)).length === 0) {
          $('#addresses-header').hide();
          $(this).hide();
          $('#cancel-address-add-edit').hide();
          $('#address-book-form-wrapper').show();
        }
      });

      // Toggle the checkout guest login/returning customers sections on mobile.
      if ($('#edit-login-tabs').is(":visible")) {
        var tabs = $('#edit-login-tabs');
        tabs.parent().toggleClass('active');

        // Show Guest Checkout as selected by default
        tabs.find('.tab-new-customer').toggleClass('active');
        tabs.next('#edit-checkout-guest').toggleClass('active');

        // Add click handler for the tabs.
        tabs.find('.tab').each(function () {
          $(this).on('click', function () {
            // Do nothing when clicked on a tab that is already active.
            if ($(this).hasClass('active')) {
              return false;
            }
            // Add active class.
            $(this).toggleClass('active');
            $(this).siblings().toggleClass('active');
            // Check which tab is clicked and add active class on corresponding fieldset.
            if ($(this).has('#tab-new-customer')) {
              $(this).parent().nextAll('#edit-checkout-guest').toggleClass('active');
              $(this).parent().nextAll('#edit-checkout-login').toggleClass('active');
            }
            else {
              $(this).parent().nextAll('#edit-checkout-guest').toggleClass('active');
              $(this).parent().nextAll('#edit-checkout-login').toggleClass('active');
            }
          });
        });
      }
    }
  };

  // Ajax command to update search result header count.
  $.fn.editDeliveryAddress = function (data) {
    // Set values in form.
    $('[data-drupal-selector="edit-member-delivery-home-address-form-address-id"]').val(data.id);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-given-name"]').val(data.given_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-family-name"]').val(data.family_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').val(data.administrative_area);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-locality"]').val(data.locality);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line1"]').val(data.address_line1);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-dependent-locality"]').val(data.dependent_locality);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line2"]').val(data.address_line2);

    // Init Mobile number prefix js.
    Drupal.alshayaMobileNumber.init($('[data-drupal-selector="edit-member-delivery-home-address-form-form-mobile-number-mobile"]'), data.mobile);

    // Show the form.
    $('#address-book-form-wrapper').slideDown();
  };

})(jQuery, Drupal);
