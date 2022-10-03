/**
 * @file
 * Alo purpose block mobile accordions.
 */

(function ($, Drupal) {
  Drupal.behaviors.aloPurposeMobileAccordion = {
    attach: function (context) {
      if ($(window).width() < 768) {
        var $accordionHead = $('.brand-purpose__accordion-header', context);
        var $accordionBody = $($accordionHead).nextAll().hide();

        $($accordionHead).once('accordion-click').click(function () {
          var $head = $(this);
          var $body = $head.nextAll().stop(true, true).slideToggle();
          $accordionBody.not($body).slideUp();
          $head.toggleClass('active');
          $accordionHead.not($head).removeClass('active');
        });
      }
    }
  };
})(jQuery, Drupal);
