/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Js to convert to select2Option to transform into boxes from select list.
   *
   *  @param {context} context on ajax update.
   */
  Drupal.select2OptionConvert = function (context) {
    if ($(window).width() < 768) {
      $('#configurable_ajax').addClass('visually-hidden');
    }
    // Hide the dropdowns when user resizes window and is now in desktop mode.
    $('.form-item-configurable-select').addClass('visually-hidden');
    Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-select', context));

    // Always hide the dropdown for swatch field.
    $('.form-item-configurable-swatch').addClass('visually-hidden');

    Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-swatch', context));

    // Markup for show more/less color swatches.
    var showMoreHtml = $('<div class="show-more-color">' + Drupal.t('View all colours') + '</div>');
    var showLessHtml = $('<div class="show-less-color">' + Drupal.t('View less colours') + '</div>');

    if ($('.show-more-color').length === 0) {
      showMoreHtml.insertAfter($('.configurable-swatch .select-buttons')).hide();
    }
    if ($('.show-less-color').length === 0) {
      showLessHtml.insertAfter($('.configurable-swatch .select-buttons')).hide();
    }

    if (!$(context).hasClass('modal-content')) {
      // JS function to show less/more for colour swatches.
      Drupal.magazine_swatches_count();
    }
  };

  /**
   * JS for converting select list for size to unformatted list on PDP pages.
   *
   * @param {object} element
   *   The HTML element inside which we want to convert select list into unformatted list.
   */
  Drupal.convertSelectListtoUnformattedList = function (element) {
    element.once('bind-events').each(function () {
      var that = $(this).parent();
      $('select', that).select2Option();

      $('.select2Option', that).find('.list-title .selected-text').html('');

      var clickedOption = $('select option:selected', that);
      if (!clickedOption.is(':disabled')) {
        $('.select2Option', that).find('.list-title .selected-text').html(clickedOption.text());
        Drupal.alshaya_color_swatch_update_selected_label();
      }
    });
  };

  /**
   * Implementation of view more/less colour for swatches.
   */
  Drupal.magazine_swatches_count = function () {
    if ($('.configurable-swatch .select-buttons li:nth-child(2) a').attr('data-swatch-type') === 'Details') {
      $('.configurable-swatch').addClass('product-swatch');
    }
    else {
      $('.configurable-swatch').addClass('colour-swatch');
    }

    var colour_swatches = drupalSettings.colour_swatch_items_mob;
    var product_swatches = drupalSettings.product_swatch_items_mob;
    var swatch_items_to_show = 0;

    if ($(window).width() > 767 && $(window).width() < 1025) {
      colour_swatches = drupalSettings.colour_swatch_items_tab;
      product_swatches = drupalSettings.product_swatch_items_tab;
    }

    else if ($(window).width() > 1024) {
      colour_swatches = drupalSettings.colour_swatch_items_desk;
      product_swatches = drupalSettings.product_swatch_items_desk;
    }

    if ($('.configurable-swatch.product-swatch').length > 0) {
      swatch_items_to_show = product_swatches;
    }
    else {
      swatch_items_to_show = colour_swatches;
    }

    if ($('.content__title_wrapper').hasClass('show-all-swatch')) {
      $('.show-less-color').show();
    }
    else if ($('.content__title_wrapper').hasClass('show-less-swatch')) {
      $('.show-more-color').show();
    }

    if ($(window).width() > 767) {
      if ($('.configurable-swatch .select-buttons li').length > swatch_items_to_show && !$('.content__title_wrapper').hasClass('show-all-swatch')) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').hide();
        $('.configurable-swatch').addClass('swatch-toggle');
        $('.show-more-color').show();
        $('.configurable-swatch, .magazine-swatch-placeholder').addClass('swatch-effect');
      }

      else {
        $('.configurable-swatch').addClass('simple-swatch-effect');
      }
    }
    else {
      if ($('.magazine-swatch-placeholder .select-buttons li').length > swatch_items_to_show && !$('.content__title_wrapper').hasClass('show-all-swatch')) {
        $('.show-more-color').show();
        $('.configurable-swatch, .magazine-swatch-placeholder').addClass('swatch-effect');
        $('.magazine-swatch-placeholder').removeClass('simple-swatch-effect');
      }

      else if ($('.content__title_wrapper').hasClass('show-all-swatch')) {
        $('.magazine-swatch-placeholder').removeClass('simple-swatch-effect');
      }

      else {
        $('.magazine-swatch-placeholder').addClass('simple-swatch-effect');
      }
    }

    $('.show-more-color').on('click', function (e) {
      if ($(window).width() > 767) {
        $('.configurable-swatch .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
      }
      else {
        $('.configurable-swatch, .magazine-swatch-placeholder').addClass('swatch-toggle');
      }
      $('.content__title_wrapper').addClass('show-all-swatch');
      $('.content__title_wrapper').removeClass('show-less-swatch');
      $(this).hide();
      $('.show-less-color').show();
    });

    $('.show-less-color').on('click', function (e) {
      if ($(window).width() > 767) {
        $('.configurable-swatch .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
      }
      else {
        $('.configurable-swatch, .magazine-swatch-placeholder').removeClass('swatch-toggle');
      }
      $('.content__title_wrapper').removeClass('show-all-swatch');
      $('.content__title_wrapper').addClass('show-less-swatch');
      $(this).hide();
      $('.show-more-color').show();
    });
  };

  Drupal.behaviors.configurableAttributeBoxes = {
    attach: function (context, settings) {
      $('.form-item-configurable-swatch').parent().addClass('configurable-swatch');
      $('.form-item-configurable-select').parent().addClass('configurable-select');

      // Show mobile slider only on mobile resolution.
      Drupal.select2OptionConvert(context);

      if ($(window).width() <= drupalSettings.show_configurable_boxes_after) {
        $('.form-item-configurable-select, .form-item-configurable-swatch').on('change', function () {
          $(this).closest('.sku-base-form').find('div.error, label.error, span.error').remove();
        });
      }
    }
  };

  /**
   * JS function to move mobile colors to bellow of PDP main image in product description section.
   *
   * @param {context} context on ajax update.
   */
  function mobileColors(context) {
    // Moving color swatches from sidebar to main content in between the gallery after
    // first image as per design.
    var sku_swatch = $('.configurable-swatch', context).clone();
    $('.magazine-swatch-placeholder').html(sku_swatch);
    $('.magazine-swatch-placeholder').addClass('configurable-swatch form-item-configurables-article-castor-id');

    $('.magazine-product-description .select2Option li a').on('click', function (e) {
      e.preventDefault();
      var select = $('.sku-base-form .configurable-swatch select');
      var clickedOption = $(select.find('option')[$(this).attr('data-select-index')]);

      if (clickedOption.is(':selected')) {
        return;
      }

      $(this).closest('.select2Option').find('.list-title .selected-text').html(clickedOption.text());
      if ($(this).hasClass('picked')) {
        $(this).removeClass('picked');
        clickedOption.removeProp('selected');
      }
      else {
        $('.magazine-product-description .select-buttons').find('a, span').removeClass('picked');
        $(this).addClass('picked');
        clickedOption.prop('selected', true);
      }
      select.trigger('change');
    });
  }

  /**
   * JS function to move mobile size div to size-tray.
   *
   *  @param {context} context on ajax update.
   */
  function mobileSize(context) {
    if (context === document) {
      var sizeDiv = $('#configurable_ajax .form-item-configurables-size', context).clone();
      var sizeTray = $('.size-tray', context);
      var sizeTrayButtons = sizeTray.find('.size-tray-buttons');
      // Move size-tray close buttons inside configurable size container.
      sizeTrayButtons.append('<div class="size-tray-close"></div>');
      // Move the configurable select container to size tray.
      if (sizeDiv.length > 0) {
        $('.size-tray-content').html(sizeDiv).prepend(sizeTrayButtons);
      }
    }

    if ($('.content__title_wrapper').find('.size-tray-link').length < 1) {
      var sizeTraylinkText = Drupal.t('Select Size');
      // If size is default selected.
      if ($('.size-tray .select2Option .list-title .selected-text').text().length > 0) {
        sizeTraylinkText = $('.size-tray .select2Option .list-title .selected-text').text();
      }

      // Add Size link only with product having size.
      if ($('.form-item-configurables-size').length > 0) {
        $('<div class="size-tray-link">' + sizeTraylinkText + '</div>').insertBefore('.edit-add-to-cart');
      }
    }

    $('.size-tray-link', context).once().on('click', function () {
      $('.size-tray').addClass('tray-open');
      $('.size-tray > div').toggle('slide', {direction: 'down'}, 400);
      $('body').addClass('tray-overlay mobile--overlay');
    });

    $('.size-tray-close', context).once().on('click', function () {
      $('.size-tray > div').toggle('slide', {direction: 'down'}, 400, function () {
        $('.size-tray').removeClass('tray-open');
      });
      $('body').removeClass('tray-overlay mobile--overlay');
      if ($('body').hasClass('open-tray-without-selection')) {
        $('body').removeClass('open-tray-without-selection mobile--overlay');
        $('.nodetype--acq_product .magazine-layout-node input.hidden-context').val('');
      }
    });

    $('.size-tray-content .select2Option li a').on('click', function (e) {
      e.preventDefault();
      var select = $('.sku-base-form .form-item-configurables-size select');
      var clickedOption = $(select.find('option')[$(this).attr('data-select-index')]);

      if (clickedOption.is(':selected')) {
        return;
      }

      $(this).closest('.select2Option').find('.list-title .selected-text').html(clickedOption.text());

      // Replace the size tray text with selected value..
      $('.size-tray-link').addClass('selected-text').html(clickedOption.text());

      if ($(this).hasClass('picked')) {
        $(this).removeClass('picked');
        clickedOption.removeProp('selected');
      }
      else {
        $('.size-tray-content .select-buttons').find('a, span').removeClass('picked');
        $(this).addClass('picked');
        clickedOption.prop('selected', true);
      }
      select.trigger('change');

      // Closing the tray after selection.
      $('.size-tray > div').toggle('slide', {direction: 'down'}, 400);
      // Close with a delay allowing time for sliding animation to finish.
      setTimeout(function () {
        $('.size-tray').removeClass('tray-open');
      }, 400);
      $('body').removeClass('tray-overlay mobile--overlay');
    });
  }

  /**
   * Js to make title section and add-to-cart form in mobile - sticky.
   *
   * @type {{attach: Drupal.behaviors.stickyMagazineDiv.attach}}
   */
  Drupal.behaviors.stickyMagazineDiv = {
    attach: function (context, settings) {
      // Only on mobile.
      if ($(window).width() < 768) {
        var stickyDiv = $('.magazine-layout .content__title_wrapper');
        var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper');
        stickyDiv.addClass('fixed');
        $(window).on('scroll', function () {
          // Screen bottom.
          var mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height() + stickyDiv.height() + 64;
          var windowBottom = $(this).scrollTop() + $(this).height();
          if (mobileCWBottom > windowBottom) {
            stickyDiv.addClass('fixed');
          }
          else {
            stickyDiv.removeClass('fixed');
          }
        });

        if ($(context).find('.store-sequence').length !== 0) {
          window.scrollTo(0, $('#pdp-stores-container h3.c-accordion__title').offset().top);
        }
      }
    }
  };

  /**
   * Js to implement mobile magazine layout.
   *
   * @type {{attach: Drupal.behaviors.mobileMagazine.attach}}
   */
  Drupal.behaviors.mobileMagazine = {
    attach: function (context, settings) {
      if ($(window).width() < 768) {
        // Moving title section below delivery options in mobile.
        var tittleSection = $('.content__title_wrapper');
        tittleSection.insertAfter('.mobile-content-wrapper');

        // Moving sharethis before description field in mobile.
        var sharethisSection = $('.basic-details-wrapper .modal-share-this').clone();
        if ($('.magazine-product-description .modal-share-this').length < 1) {
          sharethisSection.once('bind-events').insertBefore('.magazine-swatch-placeholder');
        }
        $('.basic-details-wrapper .modal-share-this').addClass('visually-hidden');
        if ($('.magazine-product-description .modal-share-this').hasClass('visually-hidden')) {
          $('.magazine-product-description .modal-share-this').removeClass('visually-hidden');
        }

        // JS function to move mobile colors to bellow of PDP main image in
        // product description section.
        if (!$(context).hasClass('modal-content')) {
          mobileColors(context);
        }

        // JS function to move mobile size div to size-tray.
        mobileSize(context);

        // JS function to show less/more for colour swatches.
        Drupal.magazine_swatches_count();

        var sizeTray = $('.size-tray', context);
        var sizeTrayButtons = sizeTray.find('.size-tray-buttons');
        var sizeGuideLink = $('#configurable_ajax .size-guide-link', context);
        // Move size guide link inside configurable size container.
        sizeTrayButtons.prepend(sizeGuideLink);

        $('.edit-add-to-cart', context).once().on('mousedown', function () {
          var that = this;
          setTimeout(function () {
            if ($(that).closest('form').hasClass('ajax-submit-prevented')) {
              $('.size-tray').addClass('tray-open');
              $('.size-tray > div').toggle('slide', {direction: 'down'}, 400);
              $('body').addClass('open-tray-without-selection');
              $('.nodetype--acq_product .magazine-layout-node input.hidden-context').val('submit');
            }
          }, 10);
        });

        // If tray is opening on clicking of add to basket (missing attribute selection).
        // then on selection of attribute product should add to basket directly.
        $(document).ajaxComplete(function (event, xhr, settings) {
          if ((settings.hasOwnProperty('extraData')) &&
            ((settings.extraData._triggering_element_name.indexOf('configurables') >= 0)) &&
            $('body').hasClass('open-tray-without-selection')) {
            $('body').removeClass('open-tray-without-selection');
            $('.nodetype--acq_product .magazine-layout-node input.hidden-context').val('');
          }
        });

      }
      else {
        // JS to make sidebar sticky in desktop.
        var topposition = $('.gallery-wrapper').offset().top - $('.branding__menu').height() - 20;
        var mainbottom = $('.gallery-wrapper').offset().top + $('.gallery-wrapper').height();
        $(window).on('scroll', function () {
          if (($(this).scrollTop() > topposition)) {
            $('.content-sidebar-wrapper').addClass('sidebar-fixed');
          }
          else {
            $('.content-sidebar-wrapper').removeClass('sidebar-fixed');
          }

          if (($('.content-sidebar-wrapper').offset().top + $('.content-sidebar-wrapper').height()) > mainbottom) {
            $('.content-sidebar-wrapper').addClass('contain');
          }

          if ($(document).scrollTop() <= $('.content-sidebar-wrapper').offset().top - $('.branding__menu').height() - 16) {
            if ($('.content-sidebar-wrapper').hasClass('contain')) {
              $('.content-sidebar-wrapper').removeClass('contain');
            }
          }
        });

        $('.size-guide-link').on('click', function (e) {
          $('body').addClass('magazine-layout-ajax-throbber');
        });

        setTimeout(function () {
          $('.ui-dialog-titlebar-close').on('click', function (e) {
            if ($('body').hasClass('magazine-layout-ajax-throbber')) {
              $('body').removeClass('magazine-layout-ajax-throbber');
            }
          });
        }, 10);
      }
    }
  };
})(jQuery, Drupal);
