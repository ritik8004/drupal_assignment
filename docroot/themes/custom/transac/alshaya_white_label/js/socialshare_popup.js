/**
 * @file
 * Social share popup js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.socialiconPopup = {
    attach: function (context, settings) {
      function modalOverlay(button, className) {
        $(button).click(function () {
          $('html').removeClass(className);
        });
      }

      if ($('.sharethis-wrapper').hasClass('out-of-stock')) {
        $('.modal-share-this').addClass('social-share-out-of-stock');
      }
      else {
        $('.magazine-layout .modal-share-this .share-icon').on('click', function (e) {
          $('html').addClass('social-modal-overlay');
          var top_height = ($(window).height() - $('.social-modal-overlay .sharethis-container').height()) / 2;
          var left_width = ($(window).width() - $('.social-modal-overlay .sharethis-container').innerWidth()) / 2;
          // Make popup content in center of the window.
          $('.sharethis-container').css({top: top_height, left: left_width});
          modalOverlay('.close-icon', 'social-modal-overlay');

          $(document).ajaxComplete(function () {
            modalOverlay('.close-icon', 'social-modal-overlay');
          });
        });
      }
    }
  };

})(jQuery, Drupal);
