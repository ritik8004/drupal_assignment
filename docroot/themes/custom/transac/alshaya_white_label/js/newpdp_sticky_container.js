/**
 * @file
 * NewPDP sticky container.
 */

(function ($, Drupal) {
  Drupal.behaviors.newpdpStickyContainer = {
    attach: function () {
      var root = document.querySelector(':root');
      var galleryWidth = document.querySelector('.magv2-content') && document.querySelector('.magv2-content').offsetWidth;
      var sidebarWidth = document.querySelector('.magv2-sidebar') && document.querySelector('.magv2-sidebar').offsetWidth;
      var totalWidth = galleryWidth + sidebarWidth + 'px';
      root.style.setProperty('--dynamic-container-width', totalWidth);
    }
  };
})(jQuery, Drupal);
