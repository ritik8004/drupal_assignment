(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaHelloMemberProfile = {
    attach: function (context) {
      const phoneNumber = $('input[name="field_mobile_number[0][mobile]"]', context);
      // On changing phone number in my account disable submit till otp is verified.
      phoneNumber.on('keyup change input keypress paste', validate_phone_number);

      function validate_phone_number() {
        if( phoneNumber.val().length === parseInt(phoneNumber.attr('maxlength'), 10)
          && (phoneNumber.attr('value') !== phoneNumber.val())) {
          $('#hello-member-send-otp .btn-wrapper').removeClass('in-active');
          $('#edit-submit').addClass('in-active');
        } else {
          $('#hello-member-send-otp .btn-wrapper').addClass('in-active');
          $('#edit-submit').removeClass('in-active');
        }
      }
    },
  };

})(jQuery, Drupal);
