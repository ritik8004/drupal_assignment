/**
 * @file
 * Aura Header JS file.
 */

(function ($, Drupal) {
  'use strict';

  $(document).ready(function() {
    // Decide what to show in the Aura header in desktop and in the primary menu
    // in mobile.
    const auraHeader = $('.block-aura-rewards-header');
    const main_menu_element = $('.aura-main-menu-user');
    const mobilePopup = $('.block-alshaya-main-menu .menu__list .aura-header-popup-wrapper');

    if ($('body').hasClass('logged-out')) {
      // Desktop mode.
      auraHeader.find('.aura-header-link').removeClass('hidden');
      auraHeader.find('.aura-header-popup-wrapper').removeClass('hidden');
      // Mobile mode.
      mobilePopup.removeClass('hidden');
    }
    else {
      $.ajax({
        type: 'GET',
        url: '/' + drupalSettings.path.currentLanguage + drupalSettings.aura.user_points_route,
        success: function(data) {
          if (data.aura_user.points >= 0 && data.aura_user.is_loyalty_linked == 1) {
            // Desktop mode.
            auraHeader.find('.aura-general-pages .name').html(data.aura_user.name);
            auraHeader.find('.aura-general-pages .points').html(data.aura_user.points + Drupal.t(' Points'));
            auraHeader.find('.aura-general-pages .badge').addClass('badge-' + data.aura_user.tier);

            // Mobile mode.
            main_menu_element.find('.name').html(data.aura_user.name);
            main_menu_element.find('.points').html(data.aura_user.points).removeClass('hidden');
            main_menu_element.find('.badge').addClass('badge-' + data.aura_user.tier).removeClass('hidden');
          }
          else if (data.aura_user.is_loyalty_linked == 1) {
            // Loyalty card is linked, but points not there in the API response.
            // Then show the same things as for anonymous users.
            // Desktop mode.
            auraHeader.find('.aura-header-link').removeClass('hidden');
            auraHeader.find('.aura-header-popup-wrapper').removeClass('hidden');
            // Mobile mode.
            mobilePopup.removeClass('hidden');
          }
          else {
            // Loyalty card is not linked.
            main_menu_element.find('.name').html(data.aura_user.name);
          }

          // We show different content in the header depending on whether the user is
          // logged in or anonymous.
          if ($('body').hasClass('aura-my-account')) {
            $('.aura-my-account-page').removeClass('hidden');
            if (data.aura_user.is_loyalty_linked == 0) {
              // Mobile mode.
              mobilePopup.removeClass('hidden');
            }
          }
          else {
            if (data.aura_user.is_loyalty_linked == 1) {
              // We show the user points data.
              $('.aura-general-pages').removeClass('hidden')
            }
            else {
              // We do not show the user points data.
              // Desktop mode.
              auraHeader.find('.aura-header-link').removeClass('hidden');
              auraHeader.find('.aura-header-popup-wrapper').removeClass('hidden');
              // Mobile mode.
              mobilePopup.removeClass('hidden');
            }
          }
        }
      });
    }
  });

  Drupal.behaviors.auraHeaderPopup = {
    attach: function (context) {
      if ($(window).width() > 1024) {
        $('.aura-header-link a', context).once().on('click', function (e) {
          e.preventDefault();
          $('.aura-header-popup-wrapper').toggle();
          $('body').toggleClass('aura-header-open');
          e.stopPropagation();
        });

        $(document, context).once().on('click', function (e) {
          var displayState = $('.aura-header-popup-wrapper').css('display');

          if (displayState !== 'none') {
            if (!($(e.target).closest('.aura-header-popup-wrapper').length)) {
              $('.aura-header-popup-wrapper').hide();
            }
          }
        });
      }
    }
  };

})(jQuery, Drupal)
