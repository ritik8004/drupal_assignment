/**
 * @file
 * My Account Recent Order.
 */

(function ($, Drupal) {

  /**
   * Scroll To Element
   *
   * @param {string} element
   *   Element selector.
   */
  function scrollToElement(element) {
    $('html, body').animate({
      scrollTop: $(element).offset().top
    }, 350);
  }

  Drupal.behaviors.myaccountRecentOrder = {
    attach: function () {
      if ($('.recent__orders--list .order-summary-row').length) {
        var parentOrder = $('.recent__orders--list .order-summary-row');
        var cancelLink = $('.recent__orders--list .order-summary-row .cancel-link');
        var listOrder = $('.recent__orders--list .order-item-row');

        $(listOrder).hide();

        $(parentOrder).once('expand-row').on('click', function () {
          var $ub = $(this).nextAll().stop(true, true).fadeToggle('slow');
          listOrder.not($ub).hide();
          $ub.parent().toggleClass('open--accordion');
          listOrder.not($ub).parent().removeClass('open--accordion');

          // Trigger an event when order is viewed.
          var recentOrderViewEvent = new CustomEvent('onRecentOrderView', {
            bubbles: true,
            detail: {
              data: {
                orderId: $ub.parent().attr('data-order-id'),
                // Pass the dom element in the context.
                context: $(this).parent()[0],
              }
            }
          });
          document.dispatchEvent(recentOrderViewEvent);
        });

        $(cancelLink).once('expand-cancel-link').on('click', function (e) {
          e.preventDefault();

          var targetedElement = e.target;
          var cancelRowSelector = $(targetedElement).attr('href');
          var closestElementClosestRow = $(targetedElement).parents('.order-summary-row');

          if (closestElementClosestRow.parent().hasClass('open--accordion')) {
            e.stopImmediatePropagation();
            scrollToElement(cancelRowSelector);
          } else {
            closestElementClosestRow.parent().addClass('open--accordion');
            listOrder.not(closestElementClosestRow).parent().removeClass('open--accordion');
            setTimeout(function () {
              scrollToElement(cancelRowSelector);
            }, 200);
          }
        });
      }
    }
  };
})(jQuery, Drupal);
