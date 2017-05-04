/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {
      // JS for converting select list to unformatted list on PDP pages.
      if ($('#block-alshaya-white-label-content #size-select').length === 0) {
        $('#block-alshaya-white-label-content .form-item-configurables-size select').after("<ul id='size-select' />")
          .children('option').each(function (index, el) {
            if (index > 0) {
              $('#size-select').append('<li><div class=' + $(this).text() + '>' + $(this).text() + '</div></li>');
            }
          }
        );
        var currentsize = $('#block-alshaya-white-label-content .form-item-configurables-size select option:selected').text() === '- Select -' ? $('#block-alshaya-white-label-content .form-item-configurables-size select option:nth-child(2)').text() : $('#block-alshaya-white-label-content .form-item-configurables-size select option:selected').text();
        $('#block-alshaya-white-label-content #size-select').before('<div><span class="size-label">Size : </span><span class="size-value">' + currentsize + '</span></div>');
      }
      $('#block-alshaya-white-label-content .form-item-configurables-size select').hide();

      $('#block-alshaya-white-label-content #size-select li').click(function (event) {
        var size = $(this).text();
        $(this).siblings('li').removeClass('active');
        $(this).addClass('active');
        $('#block-alshaya-white-label-content .form-item-configurables-size select').children('option').each(function (index, el) {
          if (size === $(this).text()) {
            $(this).siblings('option').attr('selected', false);
            $(this).attr('selected', 'selected').change();
          }
        });
      });

      $('#drupal-modal .field--name-body, #drupal-modal .sharethis-wrapper').wrapAll('<div class="modal-product-wrapper"></div>');
      $('.modal-product-wrapper').before('<h3 class="more-content"><span class="link">' + Drupal.t('View full product details') + '</span><span class="arrow"></span></h3>');
      $('.modal-product-wrapper').hide();
      $('h3.more-content').click(function () {
        $('.modal-product-wrapper').toggle();
      });
    }
  };

  Drupal.behaviors.sizeguidemodal = {
    attach: function (context, settings) {

      // JS for converting select list to unformatted list in the sizeguide modal popup.
      if ($('#drupal-modal #size-select-modal').length === 0) {
        $('#drupal-modal .form-item-configurables-size select').after("<ul id='size-select-modal' />")
          .children('option').each(function (index, el) {
            if (index > 0) {
              $('#size-select-modal').append('<li><div class=' + $(this).text() + '>' + $(this).text() + '</div></li>');
            }
          }
        );
        var currentsize = $('#drupal-modal .form-item-configurables-size select option:selected').text() === '- Select -' ? $('#drupal-modal .form-item-configurables-size select option:nth-child(2)').text() : $('#drupal-modal .form-item-configurables-size select option:selected').text();
        $('#drupal-modal #size-select-modal').before('<div><span class="size-label">Size : </span><span class="size-value">' + currentsize + '</span></div>');

      }
      $('#drupal-modal .form-item-configurables-size select').hide();

      // JS for triggering select option on clicking of list item in size list in the sizeguide modal popup.
      $('#drupal-modal #size-select-modal li').click(function (event) {
        var size = $(this).text();
        $(this).siblings('li').removeClass('active');
        $(this).addClass('active');
        $('#drupal-modal .form-item-configurables-size select').children('option').each(function (index, el) {
          if (size === $(this).text()) {
            $(this).siblings('option').attr('selected', false);
            $(this).attr('selected', 'selected').change();
          }
        });
      });

      $('#drupal-modal .field--name-body, #drupal-modal .sharethis-wrapper').wrapAll('<div class="modal-product-wrapper"></div>');
      $('.modal-product-wrapper').before('<h3 class="more-content"><span class="link">' + Drupal.t('View full product details') + '</span><span class="arrow"></span></h3>');
      $('.modal-product-wrapper').hide();
      $('h3.more-content').click(function () {
        $('.modal-product-wrapper').toggle();
      });
    }
  };

})(jQuery, Drupal);
