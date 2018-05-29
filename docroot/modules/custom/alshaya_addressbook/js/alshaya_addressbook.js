(function ($, Drupal) {
  'use strict';

  $.fn.correctFloorFieldLabel = function () {
    if ($.trim($('.form-item-field-address-floor').val()).length !== 0) {
      var label = $('.form-item-field-address-floor').parent().find('label:not(".error")');
      label.addClass('active-label');
    }
  };

  /**
   * Updates area list options based on options provided in argument.
   *
   * This function is called as AJAX command (InvokeCommand) in our
   * custom AJAX callback endpoint.
   *
   * @param areas
   *   New areas based on selected parent.
   */
  $.fn.updateAreaList = function (areas) {
    var currentSelection = $('.area-list-dropdown').val();
    var emptyOption = $('.area-list-dropdown option[value=""]');
    $('.area-list-dropdown option').remove();
    $('.area-list-dropdown').append(emptyOption);

    for (var i in areas) {
      var option = $('<option />');
      option.attr('value', i);
      option.html(areas[i]);
      $('.area-list-dropdown').append(option);
    }

    $('.area-list-dropdown').val(currentSelection);

    $('.area-list-dropdown').trigger('change');
  }

  /**
   * On addressbook ajax validation, mobile number prefix is lost
   * as its added by the JS. Here we just adding that again.
   */
  $.fn.mobileNumberPrefixAjax = function() {
    $('.mobile-number-field .country').once('field-setup').each(function () {
      var $input = $(this);
      var val = $input.val();
      $input.data('value', val);
      $input.wrap('<div class="country-select"></div>').before('<div class="mobile-number-flag"></div><span class="arrow"></span><div class="prefix"></div>');
    });
  }

})(jQuery, Drupal);
