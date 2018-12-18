/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Js to convert to select2Option to transform into boxes from select list.
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
    Drupal.magazine_swatches_count();
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
        Drupal.alshaya_hm_images_update_selected_label();
      }
    });
  };

  /**
   * Implementation of view more/less colour for swatches.
   */
  Drupal.magazine_swatches_count = function () {
    if ($('.form-item-configurables-article-castor-id .select-buttons li:nth-child(2) a').attr('data-swatch-type') === 'Details') {
      $('.form-item-configurables-article-castor-id').addClass('product-swatch');
    }
    else {
      $('.form-item-configurables-article-castor-id').addClass('colour-swatch');
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
      swatch_items_to_show = product_swatches + 1;
    }
    else {
      swatch_items_to_show = colour_swatches + 1;
    }

    if ($('.form-item-configurables-article-castor-id .select-buttons li').length > swatch_items_to_show) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
        $('.form-item-configurables-article-castor-id').addClass('swatch-toggle');
      }
      $('.form-item-configurables-article-castor-id').addClass('swatch-effect');
      $('.show-more-color').show();
    }
    else {
      $('.form-item-configurables-article-castor-id').addClass('simple-swatch-effect');
    }

    $('.show-more-color').on('click', function (e) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
      }
      else {
        $('.form-item-configurables-article-castor-id').addClass('swatch-toggle');
      }
      $(this).hide();
      $('.show-less-color').show();
    });

    $('.show-less-color').on('click', function (e) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
      }
      else {
        $('.form-item-configurables-article-castor-id').removeClass('swatch-toggle');
      }
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
   * Helper function to compute height of add to cart button and make it sticky.
   *
   * @param {String} direction The scroll direction.
   * @param {string} state The moment when function is called, initial/after.
   */
  function mobileMagazineSticky(direction, state) {
    // Sticky Section.
    var stickyDiv = $('.magazine-layout .content__title_wrapper');
    // This is the wrapper that holds delivery options.
    var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper');
    var windowBottom;
    var mobileCWBottom;
    if (state === 'initial') {
      // Button top.
      var stickyDivTop = stickyDiv.offset().top + stickyDiv.height();
      // Screen bottom.
      windowBottom = $(window).scrollTop() + $(window).height();
      if (stickyDivTop > windowBottom) {
        stickyDiv.addClass('fixed');
      }
      else {
        stickyDiv.removeClass('fixed');
      }
      return;
    }
    else {
      // mobileContentWrapper bottom, based on direction we have to factor in
      // the height of button if it is already fixed.
      mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height();
      if (direction === 'up') {
        mobileCWBottom = mobileContentWrapper.offset().top + mobileContentWrapper.height() + stickyDiv.outerHeight() - 60;
      }

      // Screen scroll offset.
      windowBottom = $(window).scrollTop() + $(window).height();
      // Hide button when we are below delivery wrapper.
      if (windowBottom > mobileCWBottom && mobileContentWrapper.length) {
        stickyDiv.removeClass('fixed');
      }
      else {
        stickyDiv.addClass('fixed');
      }
    }
  }

  /**
   *
   * JS function to move mobile colors to bellow of PDP main image in
   * product description section.
   * @param {context} context on ajax update.
   */
  function mobileColors(context) {
    // Moving color swatches from sidebar to main content in between the gallery after
    // first image as per design.
    var sku_swatch = $('.configurable-swatch', context);
    if (sku_swatch !== 'undefined') {
      $('.magazine-swatch-placeholder').replaceWith('<div class="magazine-swatch-placeholder">' + sku_swatch.html() + '</div>');

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
  }

  /**
   *
   * JS function to move mobile size div to size-tray.
   *  @param {context} context on ajax update.
   */
  function mobileSize(context) {
    if (context === document) {
      var sizeDiv = $('#configurable_ajax .form-item-configurables-size', context).clone();
      var sizeTray = $('.size-tray', context);
      var sizeGuideLink = $('#configurable_ajax .size-guide-link', context);
      // Move size guide link & size-tray close buttons inside confiruable size container.
      sizeTray.find('.size-tray-buttons').prepend(sizeGuideLink);
      sizeTray.find('.size-tray-buttons').append('<div class="size-tray-close"></div>');
      // Move the configurable select container to size tray.
      if (sizeDiv.length > 0) {
        $('.size-tray-content').html(sizeDiv);
      }
    }

    if ($('.content__title_wrapper').find('.size-tray-link').length < 1) {
      $('<div class="size-tray-link">' + Drupal.t('Select Size') + '</div>').insertBefore('.edit-add-to-cart');
    }

    $('.size-tray-link', context).once().on('click', function () {
      $('.size-tray').toggleClass('tray-open');
    });

    $('.size-tray-close', context).once().on('click', function () {
      $('.size-tray').toggleClass('tray-open');
    });

    $('.size-tray-content .select2Option li a').on('click', function (e) {
      e.preventDefault();
      var select = $('.sku-base-form .form-item-configurables-size select');
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
        $('.size-tray-content .select-buttons').find('a, span').removeClass('picked');
        $(this).addClass('picked');
        clickedOption.prop('selected', true);
      }
      select.trigger('change');
    });
  }

  /**
   * Js to make title section and add-to-cart form in mobile - sticky.
   * @type {{attach: Drupal.behaviors.stickyMagazineDiv.attach}}
   */
  Drupal.behaviors.stickyMagazineDiv = {
    attach: function (context, settings) {
      // Only on mobile.
      if ($(window).width() < 768) {
        // Select the node that will be observed for mutations.
        var targetNode = document.querySelector('.acq-content-product .sku-base-form');
        // Options for the observer (which mutations to observe).
        var config = {attributes: true, childList: false, subtree: false};
        // Callback function to execute when mutations are observed.
        var callback = function (mutationsList, observer) {
          mutationsList.forEach(function (mutation) {
            if ((mutation.type === 'attributes') &&
              (mutation.attributeName === 'class') &&
              (!mutation.target.classList.contains('visually-hidden'))) {
              var buttonHeight = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper .edit-add-to-cart').outerHeight();
              var mobileContentWrapper = $('.c-pdp .mobile-content-wrapper .basic-details-wrapper');
              mobileContentWrapper.css('height', 'auto');
              mobileContentWrapper.css('height', mobileContentWrapper.height() + buttonHeight - 8);
              observer.disconnect();
            }
          });
        };
        // Create an observer instance linked to the callback function.
        var observer = new MutationObserver(callback);
        // Start observing the target node for configured mutations.
        observer.observe(targetNode, config);
        mobileMagazineSticky('bottom', 'initial');
        var lastScrollTop = 0;
        $(window).on('scroll', function () {
          var windowScrollTop = $(this).scrollTop();
          var direction = 'bottom';
          if (windowScrollTop > lastScrollTop) {
            direction = 'bottom';
          }
          else {
            direction = 'up';
          }
          lastScrollTop = windowScrollTop;
          mobileMagazineSticky(direction, 'after');
        });

      }
    }
  };

  /**
   * Js to implement mobile magazine layout.
   * @type {{attach: Drupal.behaviors.mobileMagazine.attach}}
   */
  Drupal.behaviors.mobileMagazine = {
    attach: function (context, settings) {
      if ($(window).width() < 768) {
        // Moving title section below delivery options in mobile.
        var tittleSection = $('.content__title_wrapper');
        tittleSection.insertAfter('.mobile-content-wrapper');

        // Moving sharethis before description field in mobile.
        var sharethisSection = $('.basic-details-wrapper .modal-share-this');
        sharethisSection.once('bind-events').insertBefore('.magazine-product-description .form-item-configurables-article-castor-id');
        $('.basic-details-wrapper .modal-share-this').hide();

        // JS function to move mobile colors to bellow of PDP main image in
        // product description section.
        mobileColors(context);

        // JS function to move mobile size div to size-tray.
        mobileSize(context);

        $('.edit-add-to-cart', context).on('mousedown', function () {
          var that = this;
          setTimeout(function () {
            if ($(that).closest('form').hasClass('ajax-submit-prevented')) {
              $('.size-tray').toggleClass('tray-open');
            }
          }, 10);
        });

      }
      else {
        // JS to make sidebar sticky in desktop.
        var topposition = $('.gallery-wrapper').offset().top - $('.branding__menu').height();
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

          if ($(this).scrollTop() <= $('.content-sidebar-wrapper').offset().top) {
            if ($('.content-sidebar-wrapper').hasClass('contain')) {
              $('.content-sidebar-wrapper').removeClass('contain');
            }
          }
        });
      }
    }
  };
})(jQuery, Drupal);
