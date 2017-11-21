(function ($, Drupal) {
  'use strict';

  $.fn.correctFloorFieldLabel = function () {
    if ($('.form-item-field-address-floor').length) {
      var label = $('.form-item-field-address-floor').parent().find('label:not(".error")');
      label.addClass('active-label');
    }
  };

  $.fn.updateAreaList = function (areas) {
    var emptyOption = $('.area-list-dropdown option[value=""]');
    $('.area-list-dropdown option').remove();
    $('.area-list-dropdown').append(emptyOption);

    for (var i in areas) {
      var option = $('<option />');
      option.attr('value', i);
      option.html(areas[i]);
      $('.area-list-dropdown').append(option);
    }

    $('.area-list-dropdown').trigger('change');
  }

})(jQuery, Drupal);
