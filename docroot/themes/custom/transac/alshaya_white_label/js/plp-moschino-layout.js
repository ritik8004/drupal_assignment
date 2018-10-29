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
      if ($('.moschino-plp-layout .plp-video').length !== 0) {
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
      $('.moschino-plp-layout .field--name-field-plp-menu').find('.mos-menu-item').each(function () {
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

      // For Desktop, we show sublins in a different markup.
      if ($(window).width() > 1024) {
        $('.moschino-plp-layout .mos-menu-heading').once().on('click', function () {
          var l2LinksWrapper = $('.moschino-plp-layout .moschino-sub-menu-content .l2-links-wrapper');
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
        });

        // Making div vertically in center.
        var padding_value = ($(window).height() - mos_menu_item_height) / 2;
        $('.field__items.moschino-sub-menu-content').css({'padding-top': padding_value, 'padding-bottom': padding_value});
      }

      var startAnimationCounter = 500;
      // Adding different transition durations for each heading links.
      $('.moschino-plp-layout .mos-menu-heading').each(function () {
        $(this).css('transition-duration', startAnimationCounter + 'ms');
        startAnimationCounter = startAnimationCounter + 50;
      });

      // Show the sub menu on click of the sub menu btn.
      $('.moschino-plp-layout .moschino-layout-submenu-icon .sub-menu-btn', context).on('click', function () {
        $('.moschino-sub-menu-content').toggleClass('visible');
      });

      $('.moschino-plp-layout .moschino-sub-menu-content .close-btn', context).on('click', function () {
        $('.moschino-sub-menu-content').toggleClass('visible');
        // Clean up links.
        var l2LinksWrapper = $('.moschino-plp-layout .moschino-sub-menu-content .l2-links-wrapper');
        l2LinksWrapper.empty();
        l2LinksWrapper.removeClass('visible');
        $('.moschino-sub-menu-content > .field--name-field-plp-menu').removeClass('active-menu');
      });

      // Add class if it is moschino modal.
      $(document).on('mousedown', '.moschino-modal-link.use-ajax', function () {
        $(document).on('dialogopen', '.ui-dialog', function () {
          $(this).addClass('moschino-modal');
        });
      });

      // Remove the class when the modal is closed.
      $(document).on('dialogclose', '.ui-dialog', function () {
        $(this).removeClass('moschino-modal');
      });

      /**
       * Animate the child links.
       */
      function l2sublinksAnimate() {
        setTimeout(function () {
          var startAnimationCounter = 400;
          $('.moschino-plp-layout .l2-links-wrapper > .field--name-field-sub-link .field--name-field-sub-link').each(function () {
            $(this).css('transition-duration', startAnimationCounter + 'ms');
            $(this).addClass('animate');
            startAnimationCounter = startAnimationCounter + 70;
          });
        }, 2);
      }
    }
  };
})(jQuery, Drupal);
