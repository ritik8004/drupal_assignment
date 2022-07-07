(function ($,Drupal) {
  'use strict';

  Drupal.behaviors.alshayaHelloMemberProfile = {
    attach: function (context) {
      $('input#edit-field-mobile-number-0-mobile').change(function(){
        // On changing phone number in my account disable submit till otp is verified.
        if( $('input#edit-field-mobile-number-0-mobile').val() !== ''
          && ($('input#edit-field-mobile-number-0-mobile').attr('value') !== $('input#edit-field-mobile-number-0-mobile').val())) {
          $('#hello-member-send-otp .btn-wrapper').removeClass('in-active');
          $('#edit-submit').addClass('in-active');
        }
      });
    },
  };

})(jQuery,Drupal);
