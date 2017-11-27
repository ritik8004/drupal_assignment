(function ($, Drupal) {
  'use strict';

  $.fn.correctFloorFieldLabel = function () {
    if ($('.form-item-field-address-floor').length) {
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
