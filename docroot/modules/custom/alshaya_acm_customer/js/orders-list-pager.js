(function ($, Drupal) {
  "use strict";

  var $pager = null;

  /**
   * Handle infinite paging based on show more button.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initialize orders list pager and bind the show more button event.
   */
  Drupal.behaviors.orders_list_pager = {
    attach : function() {
      $('.orders-list-pager-wrapper').once('orders-list-pager').each(function() {
        $pager = $(this);
        Drupal.bindOrdersListPaginationEvent();
      });
    }
  };

  Drupal.bindOrdersListPaginationEvent = function () {
    $pager.find('button').on('click', function(event) {
      event.preventDefault();

      // Disable the event now.
      $(this).off('click');
      $(this).prop('disabled', true);

      // Build the next page Url.
      var url = $(this).attr('attr-next-page');

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          // Append orders.
          $('.order-items').append(response.orders_list);

          // Replace the pager.
          $pager.html(response.next_page_button);

          // Bind the events again.
          Drupal.bindOrdersListPaginationEvent();
        }
      });
    });
  };

})(jQuery, Drupal);
