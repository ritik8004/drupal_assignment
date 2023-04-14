/**
 * @file
 * NewPDP sticky container.
 */

(function ($, Drupal) {
  Drupal.behaviors.newpdpStickyContainer = {
    attach: function () {
      $(window).on('load', function () {
        var root = document.querySelector(':root');
        var newPdpGallery = document.querySelector('.magv2-content');
        var newPdpSidebar = document.querySelector('.magv2-sidebar');
        var galleryWidth = newPdpGallery && newPdpGallery.offsetWidth;
        var sidebarWidth = newPdpSidebar && newPdpSidebar.offsetWidth;
        if (galleryWidth && sidebarWidth) {
          var totalWidth = galleryWidth + sidebarWidth + 'px';
          root.style.setProperty('--dynamic-container-width', totalWidth);
        }
      });
    }
  };
})(jQuery, Drupal);
