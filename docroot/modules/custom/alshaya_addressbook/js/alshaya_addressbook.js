(function ($, Drupal) {
  'use strict';

  $.fn.correctFloorFieldLabel = function () {
    if ($.trim($('.form-item-field-address-floor').val()).length !== 0) {
      var label = $('.form-item-field-address-floor').parent().find('label:not(".error")');
      label.addClass('active-label');
    }
  };

  // Focus on first error.
  $.fn.firstErrorFocus = function (arg, scroll) {
    console.error(arg);
    // We doing this as at this point of time, process is not fully completed
    // so we relying on this ajaxComplete. This will only be called when there
    // is error on address book form.
    $(document).ajaxComplete(function(event, xhr, settings) {
      var focusElement = $(arg+ ' input.error:first');
      focusElement.focus();

      // Scroll to the first element with error.
      if (scroll) {
        var stickyHeaderHeight = $('.branding__menu').height();
        $('html, body').animate({
            scrollTop: focusElement.offset().top - parseInt(stickyHeaderHeight)
        });
      }
    });
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
