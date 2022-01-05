/**
 * @file
 * Custom js file.
 */

(function ($) {
  $('.menu-language-switcher--desktop li').removeClass('is-hidden');
  $('.pdf-link').attr('href', $('.field-pdf-file-upload a').attr('href'));
})(jQuery);
