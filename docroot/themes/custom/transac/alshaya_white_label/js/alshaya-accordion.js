/**
 * @file
 * Alshaya simple accordions.
 */

(function ($, Drupal) {
  Drupal.behaviors.alshayaAccordion = {
    attach: function (context) {
      var viewportWidth = $(window).width();
      var alshayaAccordions = ['.alshaya-accordion'];

      if (viewportWidth < 768) {
        alshayaAccordions.push('.alshaya-accordion--mobile');
      }
      else if (viewportWidth >= 768) {
        alshayaAccordions.push('.alshaya-accordion--desktop');
      }

      if (alshayaAccordions.length > 0) {
        var $accordionHead = $(alshayaAccordions.join(), context).find('.alshaya-accordion-header');
        var $accordionBody = $accordionHead.nextAll().hide();

        $accordionHead.once('accordion-click').click(function () {
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
