(function ($, Drupal) {
  document.addEventListener('refreshCart', (e) => {
    let amount;
    try {
      amount = e.detail.data().totals.base_grand_total;
    }
    catch (e) {
      amount = null;
    }

    // Check if the amount is invalid.
    if (typeof amount === 'undefined' || amount === null) {
      Drupal.alshayaLogger('warning', 'Invalid amount on cart page for tabby. Cart: @cart.', {
        '@cart': e.detail.data(),
      });
      return;
    }

    const tabbyWidget = $('#spc-cart').find('.' + drupalSettings.tabby.widgetInfo.class);
    // Check if the amount is zero.
    if (!(amount)) {
      tabbyWidget.hide();
      return;
    }

    tabbyWidget.show();
    tabbyWidget.each(function () {
      const selector = $(this).attr('id');
      if (selector !== undefined) {
        $(this).hasClass('spc-tabby-info')
          ? Drupal.tabbyInfoInit('#' + selector, amount)
          : Drupal.tabbyPromoInit('#' + selector, amount, 'cart');
      }
    });
  });
})(jQuery, Drupal);
