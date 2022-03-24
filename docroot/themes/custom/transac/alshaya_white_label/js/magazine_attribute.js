/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {

  /**
   * Js to convert to select2Option to transform into boxes from select list.
   *
   *  @param {context} context on ajax update.
   */
  Drupal.select2OptionConvert = function (context) {
    if ($(window).width() < 768) {
      $('#configurable_ajax', context).addClass('visually-hidden');
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

    // Adding class when we click on slider item to open the modal.
    if ($(window).width() > 767) {
      $('.nodetype--acq_product .view-id-product_slider .use-ajax').once().on('click', function (e) {
        $('body').addClass('magazine-layout-overlay');
      });
    }

    if (!$(context).hasClass('modal-content') && !$('body').hasClass('magazine-layout-overlay')) {
      // JS function to show less/more for colour swatches.
      Drupal.magazine_swatches_count();
    }
  };

  Drupal.magazine_swatches_count = function (context) {
    // Implementation of view more/less colour for swatches.
    if ($('.configurable-swatch .select-buttons li:nth-child(2) a', context).attr('data-swatch-type') === 'Details') {
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

    if ($('.configurable-swatch.product-swatch', context).length > 0) {
      swatch_items_to_show = product_swatches;
    }
    else {
      swatch_items_to_show = colour_swatches;
    }

    if ($('.content__title_wrapper', context).hasClass('show-all-swatch')) {
      $('.show-less-color', context).show();
      $('.configurable-swatch', context).addClass('swatch-toggle');
    }
    else if ($('.content__title_wrapper', context).hasClass('show-less-swatch')) {
      $('.show-more-color', context).show();
      $('.configurable-swatch', context).removeClass('swatch-toggle');
    }

    if ($(window).width() > 767) {
      if ($('.configurable-swatch .select-buttons li', context).length > swatch_items_to_show && !$('.content__title_wrapper', context).hasClass('show-all-swatch')) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")', context).hide();
        $('.configurable-swatch', context).addClass('swatch-toggle');
        $('.show-more-color', context).show();
        $('.configurable-swatch, .magazine-swatch-placeholder', context).addClass('swatch-effect');
      }

      else {
        $('.configurable-swatch', context).addClass('simple-swatch-effect');
      }
    }
    else {
      if ($('.magazine-swatch-placeholder .select-buttons li', context).length > swatch_items_to_show && !$('.content__title_wrapper', context).hasClass('show-all-swatch')) {
        $('.show-more-color', context).show();
        $('.configurable-swatch, .magazine-swatch-placeholder', context).addClass('swatch-effect');
        $('.magazine-swatch-placeholder', context).removeClass('simple-swatch-effect');
      }

      else if ($('.content__title_wrapper', context).hasClass('show-all-swatch')) {
        $('.magazine-swatch-placeholder', context).removeClass('simple-swatch-effect');
        $('.configurable-swatch, .magazine-swatch-placeholder', context).addClass('swatch-effect');
      }

      else {
        $('.magazine-swatch-placeholder', context).addClass('simple-swatch-effect');
      }
    }

    $('.show-more-color', context).once().on('click', function (e) {
      if ($(window).width() > 767) {
        $('.configurable-swatch .select-buttons li:gt(" ' + swatch_items_to_show + ' ")', context).slideToggle();
      }
      else {
        $('.configurable-swatch, .magazine-swatch-placeholder', context).addClass('swatch-toggle');
      }
      $('.content__title_wrapper', context).addClass('show-all-swatch');
      $('.content__title_wrapper', context).removeClass('show-less-swatch');
      $(this).hide();
      $('.show-less-color', context).show();
    });

    $('.show-less-color', context).once().on('click', function (e) {
      if ($(window).width() > 767) {
        $('.configurable-swatch .select-buttons li:gt(" ' + swatch_items_to_show + ' ")', context).slideToggle();
      }
      else {
        $('.configurable-swatch, .magazine-swatch-placeholder', context).removeClass('swatch-toggle');
      }
      $('.content__title_wrapper', context).removeClass('show-all-swatch');
      $('.content__title_wrapper', context).addClass('show-less-swatch');
      $(this).hide();
      $('.show-more-color', context).show();
    });
  };

  /**
   * JS function to move mobile colors to bellow of PDP main image in product description section.
   *
   * @param {context} context on ajax update.
   */
  function mobileColors(context) {
    var clickedOption = $('.sku-base-form .configurable-swatch select option:selected', context);

    // Moving color swatches from sidebar to main content in between the gallery after
    // first image as per design.
    var sku_swatch = $('.sku-base-form .configurable-swatch', context).clone(true, true);
    $('.magazine-swatch-placeholder', context).html(sku_swatch);

    // Add required classes.
    $('.magazine-swatch-placeholder', context).once('mobileColors').addClass('configurable-swatch form-item-configurables-article-castor-id');

    $('.magazine-swatch-placeholder', context).find('.select2Option .list-title .selected-text').html(clickedOption.text());
    sku_swatch.find('a[data-color-label="' + clickedOption.text() + '"]').addClass('picked');

    // Remove size tray link so we process size again.
    $('.content__title_wrapper').find('.size-tray-link').remove();
  }

  /**
   * JS function to move mobile size div to size-tray.
   *
   *  @param {context} context on ajax update.
   */
  function mobileSize(context) {
    var clickedOption = $('.sku-base-form #edit-configurables-size option:selected', context);

    var sizeDiv = $('#configurable_ajax .form-item-configurables-size', context).clone(true, true);
    var sizeTray = $('.size-tray', context);
    var sizeTrayButtons = sizeTray.find('.size-tray-buttons');
    if (sizeTrayButtons.find('.size-tray-close').length === 0) {
      // Move size-tray close buttons inside configurable size container.
      sizeTrayButtons.append('<div class="size-tray-close"></div>');
    }

    // Move the configurable select container to size tray.
    if (sizeDiv.length > 0) {
      $('.size-tray-content').html(sizeDiv).prepend(sizeTrayButtons);
    }

    $('.size-tray .select2Option .list-title .selected-text').text(clickedOption.text());
    $('.select-buttons a[data-value="' + clickedOption.text() + '"]', context).addClass('picked');

    $('.content__title_wrapper').find('.size-tray-link').remove();
    var sizeTraylinkText = Drupal.t('Select Size');
    // If size is default selected.
    if ($('.size-tray .select2Option .list-title .selected-text').text().length > 0) {
      sizeTraylinkText = $('.size-tray .select2Option .list-title .selected-text').text();
    }

    // Add Size link only with product having size.
    if ($('.form-item-configurables-size').length > 0) {
      $('<div class="size-tray-link">' + sizeTraylinkText + '</div>').insertBefore('.edit-add-to-cart');
    }

    $('.size-tray-link', context).off().on('click', function () {
      $('.size-tray').addClass('tray-open');
      $('.size-tray > div').slideDown(400);
      $('body').addClass('tray-overlay mobile--overlay');
    });

    $('.size-tray-close', context).off().on('click', function () {
      $('.size-tray > div').slideUp(400, function () {
        $('.size-tray').removeClass('tray-open');
      });
      $('body').removeClass('tray-overlay mobile--overlay');
      if ($('body').hasClass('open-tray-without-selection')) {
        $('body').removeClass('open-tray-without-selection mobile--overlay');
        $('.nodetype--acq_product .magazine-layout-node input.hidden-context').val('');
      }
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

  // If tray is opening on clicking of add to basket (missing attribute selection).
  // then on selection of attribute product should add to basket directly.
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (($(window).width() < 768)
      && (typeof settings['extraData'] !== 'undefined')
      && ((settings.extraData._triggering_element_name.indexOf('configurables') >= 0))
      && $('body').hasClass('open-tray-without-selection')
    ) {
      $('body').removeClass('open-tray-without-selection');
      $('.nodetype--acq_product .magazine-layout-node input.hidden-context').val('');
    }
  });

  /**
   * JS to implement Magazine layout - Contains logic for sticky sidebar.
   *
   * @type {{attach: Drupal.behaviors.mobileMagazine.attach}}
   */
  Drupal.behaviors.mobileMagazine = {
    attach: function (context, settings) {
      $('.sku-base-form').once('mobileMagazine').each(function () {
        var product = $(this).closest('article[gtm-type="gtm-product-link"]');

        if ($(window).width() < 768) {
          // Moving title section below delivery options in mobile.
          var tittleSection = $('.content__title_wrapper', product);
          tittleSection.insertAfter(product.find('.mobile-content-wrapper'));

          // Moving sharethis before description field in mobile.
          var sharethisSection = $('.basic-details-wrapper .modal-share-this', product).clone();
          // Check if express delivery feature is enabled.
          if ($('.magazine-product-description .modal-share-this', product).length < 1) {
            var magzine_swatch_placeholder = product.find('.magazine-swatch-placeholder');
            if (typeof settings.expressDelivery !== 'undefined'
              && typeof settings.expressDelivery.enabled !== 'undefined'
              && document.querySelector('.express-delivery.mobile') !== null) {
              // Moving sharethis below Express delivery tag placement in mobile magazine layout.
              magzine_swatch_placeholder = product.find('.express-delivery.mobile');
            }
            sharethisSection.once('bind-events').insertAfter(magzine_swatch_placeholder);
          }
          $('.basic-details-wrapper .modal-share-this', product).addClass('visually-hidden');
          if ($('.magazine-product-description .modal-share-this', product).hasClass('visually-hidden')) {
            $('.magazine-product-description .modal-share-this', product).removeClass('visually-hidden');
          }

          // JS function to move mobile colors to bellow of PDP main image in
          // product description section.
          if ($(product).parents('.modal-content').length === 0) {
            mobileColors(product);
          }

          // JS function to move mobile size div to size-tray.
          mobileSize(product);

          // JS function to show less/more for colour swatches.
          Drupal.magazine_swatches_count(product);

          var sizeTray = $('.size-tray', product);
          var sizeTrayButtons = sizeTray.find('.size-tray-buttons');
          var sizeGuideLink = $('#configurable_ajax .size-guide-link', product);
          // Move size guide link inside configurable size container.
          sizeTrayButtons.prepend(sizeGuideLink);

          $('.edit-add-to-cart', product).once().on('mousedown', function () {
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
        }

        $(this).on('variant-selected', function (event, variant, code) {
          // pdp additional attribute overlay called on selecting the color and size swatch.
          Drupal.behaviors.pdp_overlay_attributes.attach(document);
          if ($(window).width() > 767) {
            return;
          }

          var product = $(this).closest('article[gtm-type="gtm-product-link"]');

          // Do this after other "variant-selected" event attachments
          // are executed.
          // JS function to move mobile colors to bellow of PDP main image in
          // product description section.
          if ($(product).parents('.modal-content').length === 0) {
            mobileColors(product);
          }

          // JS function to move mobile size div to size-tray.
          mobileSize(product);

          // Closing the tray after selection.
          $('.size-tray > div').slideUp(400, function () {
            $('.size-tray').removeClass('tray-open');
            $('body').removeClass('tray-overlay mobile--overlay');
          });

          // JS function to show less/more for colour swatches.
          Drupal.magazine_swatches_count(product);
        });
      });

      if ($(window).width() > 767 && drupalSettings.pdp_gallery_type !== 'classic') {
        // JS to make sidebar sticky beyond Mobile.
        // PDP Sidebar.
        var sidebarWrapper = $('.content-sidebar-wrapper');
        var lastScrollTop = 0;
        var pageScrollDirection;
        var heightDiff;
        var initialSidebarTop = sidebarWrapper.offset().top;

        $(window).once('mobileMagazine').on('scroll', function (event) {
          // Magazine Gallery.
          var galleryWrapper = $('.gallery-wrapper');
          var topPosition;
          var mainBottom;

          // Figure out scroll direction.
          var currentScrollTop = $(this).scrollTop();
          if (currentScrollTop < lastScrollTop) {
            pageScrollDirection = 'up';
          }
          else {
            pageScrollDirection = 'down';
          }
          lastScrollTop = currentScrollTop;

          if (galleryWrapper.length > 0) {
            // Gallery top.
            topPosition = galleryWrapper.offset().top - 20;

            // Gallery Bottom.
            mainBottom = calculateBottom(galleryWrapper);
          }

          // Fixing sidebar when the top part of sidebar touches viewport top.
          if (($(this).scrollTop() > topPosition)) {
            if (!sidebarWrapper.hasClass('sidebar-fixed')) {
              sidebarWrapper.addClass('sidebar-fixed');
            }
          }
          else {
            if (sidebarWrapper.hasClass('sidebar-fixed')) {
              sidebarWrapper.removeClass('sidebar-fixed');
            }
          }

          // Once sidebar is fixed, we need to watch for the bottom part of gallery, after which the
          // sidebar needs to float with gallery.
          if (sidebarWrapper.hasClass('sidebar-fixed')) {
            var contentSidebarTop = sidebarWrapper.offset().top;
            var contentSidebarBottom = calculateBottom(sidebarWrapper);
            if (contentSidebarBottom > mainBottom) {
              if (!sidebarWrapper.hasClass('contain')) {
                // Calculate the height difference, this gives us the top needed when sidebar is in contain mode.
                var sideBarTop = initialSidebarTop + sidebarWrapper.height();
                heightDiff = mainBottom - sideBarTop;
                sidebarWrapper.addClass('contain');
                sidebarWrapper.css('top', heightDiff + 'px');
                $('.c-accordion__title').on('click', function () {
                  sidebarWrapper.css('top', 'auto');
                });
              }
            }

            if ($(window).scrollTop() < contentSidebarTop && pageScrollDirection === 'up') {
              if (sidebarWrapper.hasClass('contain')) {
                sidebarWrapper.removeClass('contain');
                // Remove top and let fixed work as defined for sticky.
                sidebarWrapper.css('top', '');
              }
            }
          }
        });

        $('body').once('size-guide-link').on('click', '.size-guide-link', function (e) {
          $('body').addClass('magazine-layout-ajax-throbber');
        });

        $('body').once('magazine-layout-ajax-throbber').on('click', '.ui-dialog-titlebar-close', function (e) {
          if ($('body').hasClass('magazine-layout-ajax-throbber')) {
            $('body').removeClass('magazine-layout-ajax-throbber');
          }
        });
      }

      /**
       * Calculates the bottom position of a element.
       *
       * @param {*} selector
       *   HTML Area selector.
       *
       * @return {*}
       *   Bottom position without 'px' suffix.
       */
      function calculateBottom(selector) {
        return selector.offset().top + selector.height();
      }
    }
  };
})(jQuery, Drupal);
