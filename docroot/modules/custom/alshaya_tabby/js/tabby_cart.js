(function ($, Drupal) {
  document.addEventListener('refreshCart', (e) => {
    let amount;
    try {
      amount = e.detail.data().totals.base_grand_total;
    }
    catch (e) {
      return;
    }

    const tabbyWidget = $('#spc-cart').find('.' + drupalSettings.tabby.widgetInfo.class);
    tabbyWidget.show();
    // Check if the amount is invalid.
    if (typeof amount === 'undefined' || !(amount)) {
      if (typeof amount !== 'undefined') {
        tabbyWidget.hide();
      }
      return;
    }

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
