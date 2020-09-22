/**
 * @file
 * Aura Header JS file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.aura = Drupal.aura || {};

  Drupal.aura.rewardsHeader = function() {
    // Decide what to show in the Aura header in desktop and in the primary menu
    // in mobile.
    const auraHeader = $('.block-aura-rewards-header');
    const main_menu_element = $('.aura-main-menu-user');
    const mobilePopup = $('.block-alshaya-main-menu .menu__list .aura-header-popup-wrapper');
    const body = $('body');

    if (drupalSettings.user.uid == 0) {
      // Following code is executed for anonymous users.
      // Desktop mode.
      auraHeader.find('.aura-header-popup-wrapper').removeClass('hidden');
      // Mobile mode.
      mobilePopup.removeClass('hidden');
    }
    else if (body.hasClass('aura-my-account')) {
      // We do not show points on My account pages.
      // Desktop mode.
      $('.aura-my-account-page').removeClass('hidden');
      auraHeader.find('.aura-header-link').addClass('hidden');
      if (data.aura_user.is_loyalty_linked == 0) {
        // Mobile mode.
        mobilePopup.removeClass('hidden');
      }
    }
    else {
      // Following code is executed for logged in users.
      var userPointsAjaxFetch = Drupal.ajax({
        type: 'GET',
        progress: {type: 'throbber'},
        url: '/' + drupalSettings.path.currentLanguage + '/get/aura/user-points',
      });
      const userPointsSuccessCallback = function (data) {
        if (data.aura_user.points >= 0 && data.aura_user.is_loyalty_linked == 1) {
          // Desktop mode.
          auraHeader.find('.aura-general-pages .name').html(data.aura_user.name);
          auraHeader.find('.aura-general-pages .points').html(data.aura_user.points + Drupal.t(' Points'));
          auraHeader.find('.aura-general-pages .badge').addClass('badge-' + data.aura_user.tier);
          auraHeader.find('.aura-header-link').addClass('hidden');
          auraHeader.find('.aura-general-pages').removeClass('hidden');

          // Mobile mode.
          main_menu_element.find('.name').html(data.aura_user.name);
          main_menu_element.find('.points').html(data.aura_user.points + Drupal.t(' Points')).removeClass('hidden');
          main_menu_element.find('.badge').addClass('badge-' + data.aura_user.tier).removeClass('hidden');
        }
        else if (data.aura_user.is_loyalty_linked == 1) {
          // Loyalty card is linked, but points not there in the API response.
          // Then show the same things as for anonymous users.
          // Desktop mode.
          auraHeader.find('.aura-header-popup-wrapper').removeClass('hidden');
          // Mobile mode.
          main_menu_element.find('.name').html(data.aura_user.name);
        }
        else {
          // Loyalty card is not linked.
          main_menu_element.find('.name').html(data.aura_user.name);
        }
      }
      userPointsAjaxFetch.options.success = userPointsSuccessCallback;
      userPointsAjaxFetch.execute();
    }
  };

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

      $(window).on('load', function() {
        Drupal.aura.rewardsHeader();
      });
    }
  };

})(jQuery, Drupal)
