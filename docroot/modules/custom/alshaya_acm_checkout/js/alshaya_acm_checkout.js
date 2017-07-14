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
      $('#edit-delivery-tabs').once('bind-events').each(function () {
        $('input[data-drupal-selector="edit-actions-next"]').hide();

        $('.tab[gtm-type]', $(this)).on('click', function () {
          $('#selected-tab').val($(this).attr('gtm-type'));
          Drupal.behaviors.cvJqueryValidate.attach($("#block-alshaya-white-label-content"));
        });
      });

      // Bind this only once after every ajax call.
      $('[data-drupal-selector="edit-acm-payment-methods-payment-details-cc-number"]').once('validate-cc').each(function () {
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
      if ($('#shipping_methods_wrapper').length) {
        if ($('#shipping_methods_wrapper input:radio').length > 0) {
          $('#shipping_methods_wrapper fieldset').show();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').hide();
          $('[data-drupal-selector="edit-actions-next"]').show();

          // Select the first method by default.
          if ($('#shipping_methods_wrapper input[type="radio"]:checked').length === 0) {
            $('#shipping_methods_wrapper input[type="radio"]:first').trigger('click');
          }
        }
        else {
          $('#shipping_methods_wrapper fieldset').hide();
          $('[data-drupal-selector="edit-actions-get-shipping-methods"]').show();
          $('[data-drupal-selector="edit-actions-next"]').hide();
        }
      }

      $('#change-address').once('bind-events').each(function () {
        $('#add-address-button').hide();
        $('#edit-member-delivery-home-addresses').hide();
        $('#edit-member-delivery-home-header-add-profile').hide();

        $(this).on('click', function (e) {
          e.preventDefault();

          $('#add-address-button').show();
          $('#selected-address-wrapper').slideUp();
          $('[data-drupal-selector="edit-actions-next"]').hide();
          $('#shipping_methods_wrapper').slideUp();
          $('#edit-member-delivery-home-addresses').slideDown();
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
        Drupal.behaviors.cvJqueryValidate.attach(jQuery("#block-alshaya-white-label-content"));
      });
    }
  };

  // Ajax command to update search result header count.
  $.fn.editDeliveryAddress = function (data) {
    // Set values in form.
    $('[data-drupal-selector="edit-member-delivery-home-address-form-address-id"]').val(data.id);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-given-name"]').val(data.given_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-family-name"]').val(data.family_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-mobile-number-mobile"]').val(data.mobile);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').val(data.administrative_area);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-locality"]').val(data.locality);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line1"]').val(data.address_line1);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-dependent-locality"]').val(data.dependent_locality);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line2"]').val(data.address_line2);

    // Show the form.
    $('#address-book-form-wrapper').slideDown();
  };

})(jQuery, Drupal);
