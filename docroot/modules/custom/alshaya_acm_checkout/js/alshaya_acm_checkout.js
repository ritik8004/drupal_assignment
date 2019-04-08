/**
 * @file
 */

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
          // Check if this payment method is already active, if yes return.
          // We don't want to remove payment_details in this case else active payment form is lost.
          if ($(this).hasClass('plugin-selected')) {
            return false;
          }
          // Remove additional payment fields.
          $('#payment_details').remove();
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

      // Highlight the shipping method row when an option is selected.
      $('#shipping_methods_wrapper .form-type-radio input[type="radio"]:checked').parent().addClass('selected');
      $('#shipping_methods_wrapper .form-type-radio input[type="radio"]').once('bind-events').on('click', function () {
          $(this).parent().addClass('selected').siblings().removeClass('selected');
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
          $('.active--tab--content .fieldset-wrapper .title').html(Drupal.t('delivery information'));
          $('#edit-member-delivery-home-header-add-profile').show();
        });
      });

      $('#address-book-form-wrapper').once('bind-events').each(function () {
        $(this).hide();

        $('#add-address-button').on('click', function (event) {
          event.preventDefault();
          $('#address-book-form-wrapper .form-item--error-message, #address-book-form-wrapper label.error').remove();
          $('#address-book-form-wrapper .form-item--error').removeClass('form-item--error');

          // Reset the form values.
          $('[data-drupal-selector="edit-member-delivery-home-address-form-address-id"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-given-name"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-family-name"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-mobile-number-mobile"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-locality"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line1"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-dependent-locality"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line2"]').val('');

          // Select value and trigger change to ensure js dropdown shows proper value.
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-area-parent"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-area-parent"]').trigger('change');

          // Select value and trigger change to ensure js dropdown shows proper value.
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').val('');
          $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').trigger('change');

          // Show the form.
          $('#address-book-form-wrapper').slideDown();
          $(this).hide();
          $('.delivery-address-title').addClass('full-width');
        });

        $('#cancel-address-add-edit').on('click', function (event) {
          event.preventDefault();

          // Update fieldset title to edit address.
          $('.delivery-address-form-title').html(Drupal.t('add new address'));

          $('#addresses-header').show();

          // Hide the form.
          $('#address-book-form-wrapper').slideUp();

          // Display the hidden address which was being edited.
          $('#edit-member-delivery-home-addresses').slideDown();
          $('#add-address-button').show();
          $('.delivery-address-title').removeClass('full-width');
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
        Drupal.behaviors.cvJqueryValidate.attach($('#block-content'));
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

      // For view on map link (in mobile) we hide the loader after
      // few seconds as user will come back on same page.
      // Ideally it should not trigger before unload event but it does.
      $('.view-on-map.mobile-only').once('bind-js').on('click', function () {
        setTimeout('jQuery(".checkout-ajax-progress-throbber").remove()', 250);
      });
    }
  };

  // Ajax command to update search result header count.
  $.fn.editDeliveryAddress = function (data) {
    $('#address-book-form-wrapper .form-item--error-message, #address-book-form-wrapper label.error').remove();
    $('#address-book-form-wrapper .form-item--error').removeClass('form-item--error');

    // Set values in form.
    $('[data-drupal-selector="edit-member-delivery-home-address-form-address-id"]').val(data.id);

    // Input values.
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-given-name"]').val(data.given_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-family-name"]').val(data.family_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-locality"]').val(data.locality);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line1"]').val(data.address_line1);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-dependent-locality"]').val(data.dependent_locality);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-address-line2"]').val(data.address_line2);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-sorting-code"]').val(data.sorting_code);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-additional-name"]').val(data.additional_name);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-postal-code"]').val(data.postal_code);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-mobile-number-mobile"]').val(data.mobile);

    // Select value and trigger change to ensure js dropdown shows proper value.
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').val(data.administrative_area);
    $('[data-drupal-selector="edit-member-delivery-home-address-form-form-administrative-area"]').trigger('change');

    // Select value and trigger change to ensure js dropdown shows proper value.
    if (typeof data.area_parent !== 'undefined') {
      $('[data-drupal-selector="edit-member-delivery-home-address-form-form-area-parent"]').val(data.area_parent);
      $('[data-drupal-selector="edit-member-delivery-home-address-form-form-area-parent"]').trigger('change');
    }

    // Show the form.
    $('#address-book-form-wrapper').slideDown();

    // Update fieldset title to default value.
    $('.delivery-address-form-title').html(Drupal.t('edit address'));
  };

  // Ajax command to show loader on checkout pages.
  $.fn.showCheckoutLoader = function (data) {
    if ($('.checkout-ajax-progress-throbber').length === 0) {
      // Add the loader div if not available.
      $('.page-standard').append('<div class="ajax-progress ajax-progress-throbber checkout-ajax-progress-throbber"><div class="throbber"></div></div>');
    }

    // Show the loader.
    $('.checkout-ajax-progress-throbber').show();
  };

  Drupal.behaviors.fixCheckoutSummaryBlock = {
    attach: function (context, settings) {
      var block = $('.block-checkout-summary-block');

      if (block.length > 0) {
        $(window).once().on('scroll', function () {
          // Fix the block after a certain height.
          if ($(window).scrollTop() > 122) {
            block.addClass('fix-block');
          }
          else {
            block.removeClass('fix-block');
          }

          var blockbottom = block.offset().top + block.height();
          // 40 is the pixel offset above footer where we stop the fixed block.
          var footertop = $('.c-post-content').offset().top - 40;
          // Add class at this point to stop block going over footer.
          if (blockbottom >= footertop) {
            block.addClass('contain');
          }
          // Make the block sticky again when the top is visible.
          if ($(document).scrollTop() <= block.offset().top) {
            if (block.hasClass('contain')) {
              block.removeClass('contain');
            }
          }
        });
      }
    }
  };

  // Show loader every-time we are moving to a different page.
  // Do this on specific selectors only.
  var addLoaderTargets = '.tab-home-delivery:not(.disabled--tab--head), .tab-click-collect:not(.disabled--tab--head), .back-link, .tab-new-customer a, .tab-returning-customer a, button.cc-action, button.delivery-home-next';
  $(addLoaderTargets).on('click', function () {
    $(this).showCheckoutLoader();
  });

   // For Payment, we check for payment method before we add a loader.
   // Handle the "Cybersrouce" usecase as the acq_cybersource module adds it own
   // loader.
   var paymentPageTarget = '.checkout-payment .multistep-checkout .form-actions button.form-submit';
   $(paymentPageTarget).on('click', function () {
     // Check if we are using Cybersource.
     if (!$('#payment_method_cybersource .payment-plugin-wrapper-div').hasClass('plugin-selected')) {
       $(this).showCheckoutLoader();
     }
   });

  // As a precaution stop loader when the page is fully loaded.
  $(window).on('load', function () {
    $('.checkout-ajax-progress-throbber').remove();
  });

})(jQuery, Drupal);
