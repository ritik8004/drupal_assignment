/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {

      if ($('#size-select').length === 0) {
        $('.form-item-configurables-size select').after("<ul id='size-select' />")
          .children('option').each(function (index, el) {
            if (index > 0) {
              $('#size-select').append('<li><div class=' + $(this).text() + '>' + $(this).text() + '</div></li>');
            }
          }
        );
        var currentsize = $('.form-item-configurables-size select option:selected').text();
        $('#size-select').before('<div><span class="size-label">Size : </span><span class="size-value">' + currentsize + '</span></div>');
      }
      $('.form-item-configurables-size select').hide();

      $('#size-select li').click(function (event) {
        var size = $(this).text();
        $(this).siblings('li').removeClass('active');
        $(this).addClass('active');
        $('.form-item-configurables-size select').children('option').each(function (index, el) {
          if (size === $(this).text()) {
            $(this).siblings('option').attr('selected', false);
            $(this).attr('selected', 'selected').change();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
