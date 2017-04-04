(function ($, Drupal, debounce) {
  "use strict";

  // Cached reference to $(window).
  var $window = $(window);

  var $pager = null;

  // The threshold for how far to the bottom you should reach before reloading.
  var scrollThreshold = 200;

  // The event and namespace that is bound to window for automatic scrolling.
  var scrollEvent = 'scroll.orders_list_infinite_scroll';

  /**
   * Handle the automatic paging based on the scroll amount.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initialize infinite scroll pagers and bind the scroll event.
   * @prop {Drupal~behaviorDetach} detach
   *   During `unload` remove the scroll event binding.
   */
  Drupal.behaviors.orders_list_infinite_scroll = {
    attach : function() {
      $('.orders-list-infinite-scroll-pager-wrapper').once('orders-list-infinite-scroll').each(function() {
        $pager = $(this);
        Drupal.bindOrdersListInfiniteScrollEvent();
      });
    }
  };

  Drupal.bindOrdersListInfiniteScrollEvent = function () {
    $window.on(scrollEvent, debounce(function() {
      if (window.innerHeight + window.pageYOffset > $pager.offset().top - scrollThreshold) {
        $pager.find('[rel=next]').click();
        $window.off(scrollEvent);
      }
    }, 200));

    $pager.find('[rel=next]').on('click', function(event) {
      event.preventDefault();

      // Disable the event now.
      $(this).off('click');

      // Build the next page Url.
      var url = window.location.pathname.replace('/orders', '/orders-ajax') + $(this).attr('href');

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          // Append orders.
          $('.order-items').append(response.orders_list);

          // Replace the pager.
          $('.orders-list-infinite-scroll-pager-wrapper').html(response.pager);

          // Bind the events again.
          Drupal.bindOrdersListInfiniteScrollEvent();
        }
      });
    });
  };

})(jQuery, Drupal, Drupal.debounce);
