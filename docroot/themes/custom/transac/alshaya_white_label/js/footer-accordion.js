/**
 * @file
 * Footer accordion.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.footerAccordion = {
    attach: function (context, settings) {
      if ($('.c-footer-is-accordion').length) {
        var accordionHead = $('.c-footer-is-accordion .is-accordion');
        var accordionBody = $(accordionHead).nextAll();

        $(accordionBody).addClass('accordion--body');
        $(accordionHead).once().click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          accordionBody.not($ub).slideUp();
          $ub.parent().toggleClass('open--accordion');
          accordionBody.not($ub).parent().removeClass('open--accordion');
        });
      }
    }
  };

})(jQuery, Drupal);
