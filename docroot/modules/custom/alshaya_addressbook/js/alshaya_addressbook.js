(function ($, Drupal) {

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
  $.fn.mobileNumberPrefixAjax = function () {
    // Re-attaching the behaviors so that it applies the dom change again.
    Drupal.attachBehaviors(document, Drupal.settings);
  }

})(jQuery, Drupal);
