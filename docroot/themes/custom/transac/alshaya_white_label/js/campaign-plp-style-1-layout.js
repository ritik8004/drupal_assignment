/**
 * @file
 * Custom js for videos on PLP page.
 */

(function ($, Drupal) {
  'use strict';

  /* global videojs */
  /* global MobileDetect */

  Drupal.behaviors.alshayaPLPVideos = {
    attach: function (context, settings) {
      if ($('.campaign-plp-style-1-plp-layout .plp-video').length !== 0) {
        // Store the video object.
        var plpPlayer = videojs('#plp-video-player');
        var md = new MobileDetect(window.navigator.userAgent);

        if (md.mobile() || md.tablet()) {
          var mobileVideos = drupalSettings.mobileVideos;
          var mobVideo = mobileVideos[Math.floor(Math.random() * mobileVideos.length)];
          plpPlayer.src({type: 'video/' + mobVideo['type'], src: mobVideo['src']});
        }
        else {
          var desktopVideos = drupalSettings.desktopVideos;
          var deskVideo = desktopVideos[Math.floor(Math.random() * desktopVideos.length)];
          plpPlayer.src({type: 'video/' + deskVideo['type'], src: deskVideo['src']});
        }

        // If autoplay does not work by default, play the video programatically.
        var autoplay = drupalSettings.autoplay;
        if (typeof autoplay !== 'undefined' && autoplay === 1) {
          setTimeout(function () { plpPlayer.play(); }, 3000);
        }

        // Set click functions.
        $('.video-js').on('click', function () {
          if (plpPlayer.muted()) {
            plpPlayer.muted(false);
          }
          else {
            plpPlayer.muted(true);
          }
        });
      }

      var mos_menu_item_height = 0;
      // Accordion for submenu links, but this is only for tablets and below.
      $('.campaign-plp-style-1-plp-layout .field--name-field-plp-menu').find('.mos-menu-item').each(function () {
        // Create accordion if the menu has sub links.
        if ($(this).find('.mos-menu-sublink').length !== 0) {
          $(this).once('accordion-init').accordion({
            heightStyle: 'content',
            collapsible: true,
            active: false
          });
        }

        mos_menu_item_height = mos_menu_item_height + $(this).height();
      });

      // Making L1 links vertically in center in all breakpoints on load.
      var padding_value = ($(window).height() - mos_menu_item_height) / 2;
      var subMenuContent = $('.field__items.campaign-plp-style-1-sub-menu-content');
      if (!subMenuContent.hasClass('padding-processed')) {
        subMenuContent.css({'padding-top': padding_value, 'padding-bottom': padding_value});
        subMenuContent.addClass('padding-processed');
      }

      // For desktop making the L2 wrapper for L2 links start after 1st L1 item.
      var l1LinksSelector = $('.campaign-plp-style-1-sub-menu-content > .field--name-field-plp-menu');
      var l2LinksWrapper = $('.campaign-plp-style-1-plp-layout .campaign-plp-style-1-sub-menu-content .l2-links-wrapper');
      if (!l2LinksWrapper.hasClass('top-processed')) {
        l2LinksWrapper.css('top', l1LinksSelector.first().offset().top + l1LinksSelector.outerHeight());
        l2LinksWrapper.addClass('top-processed');
      }

      // For Desktop, we show sublins in a different markup.
      $('.campaign-plp-style-1-plp-layout .mos-menu-heading').once().on('click', function (event) {
        if ($(window).width() > 1024) {
          // Clicking on same link again.
          if ($(this).parent().parent().hasClass('active-menu')) {
            $(this).parent().parent().removeClass('active-menu');
            l2LinksWrapper.empty();
            l2LinksWrapper.removeClass('visible');
            return;
          }
          $(this).parent().parent().siblings().removeClass('active-menu');
          $(this).parent().parent().addClass('active-menu');
          l2LinksWrapper.removeClass('visible');
          l2LinksWrapper.empty();
          // Sublinks.
          var subLinks = $(this).siblings('.mos-menu-sublink').children().clone();
          l2LinksWrapper.html(subLinks).addClass('visible');

          // Handle animation on sublinks.
          // This 2ms delay helps to not make the animation too immidiate.
          l2sublinksAnimate();
        }
        else {
          // In tablet and mobile we ensure only accordion panel is open at one
          // time. Because each panel is a separate accordion due to markup
          // limitations, we are doing housekeeping separately.
          $(this).parent().parent().siblings('.field--name-field-plp-menu').each(function () {
            closeOpenAccordion($(this));
          });
        }
      });

      var startAnimationCounter = 500;
      // Adding different transition durations for each heading links.
      $('.campaign-plp-style-1-plp-layout .mos-menu-heading').each(function () {
        $(this).css('transition-duration', startAnimationCounter + 'ms');
        startAnimationCounter = startAnimationCounter + 50;
      });

      // Show the sub menu on click of the sub menu btn.
      $('.campaign-plp-style-1-plp-layout .campaign-plp-style-1-layout-submenu-icon .sub-menu-btn', context).on('click', function () {
        $('.campaign-plp-style-1-sub-menu-content').toggleClass('visible');
      });

      $('.campaign-plp-style-1-plp-layout .campaign-plp-style-1-sub-menu-content .close-btn', context).on('click', function () {
        $('.campaign-plp-style-1-sub-menu-content').toggleClass('visible');
        // Clean up links.
        var l2LinksWrapper = $('.campaign-plp-style-1-plp-layout .campaign-plp-style-1-sub-menu-content .l2-links-wrapper');
        l2LinksWrapper.empty();
        l2LinksWrapper.removeClass('visible');
        $('.campaign-plp-style-1-sub-menu-content > .field--name-field-plp-menu').removeClass('active-menu');

        // Clean up accordion panels.
        $('.campaign-plp-style-1-sub-menu-content > .field--name-field-plp-menu').each(function () {
          closeOpenAccordion($(this));
        });
      });

      // Add class if it is campaign-plp-style-1 modal.
      $(document).on('mousedown', '.campaign-plp-style-1-modal-link.use-ajax', function () {
        $(document).on('dialogopen', '.ui-dialog', function () {
          $(this).addClass('campaign-plp-style-1-modal');
        });
      });

      // Remove the class when the modal is closed.
      $(document).on('dialogclose', '.ui-dialog', function () {
        $(this).removeClass('campaign-plp-style-1-modal');
      });

      /**
       * Animate the child links.
       */
      function l2sublinksAnimate() {
        setTimeout(function () {
          var startAnimationCounter = 400;
          $('.campaign-plp-style-1-plp-layout .l2-links-wrapper > .field--name-field-sub-link .field--name-field-sub-link').each(function () {
            $(this).css('transition-duration', startAnimationCounter + 'ms');
            $(this).addClass('animate');
            startAnimationCounter = startAnimationCounter + 70;
          });
        }, 2);
      }

      /**
       * Helper function to close accordions.
       *
       * @param {*} element
       * field--name-field-plp-menu parent HTML element which contains
       * accordion children element to close.
       */
      function closeOpenAccordion(element) {
        if (element.children('.mos-menu-item').hasClass('c-accordion')) {
          if (element.find('.mos-menu-heading').hasClass('ui-state-active')) {
            // Close the panel.
            element.children('.mos-menu-item').accordion('option', 'active', false);
          }
        }
      }
    }
  };
})(jQuery, Drupal);
