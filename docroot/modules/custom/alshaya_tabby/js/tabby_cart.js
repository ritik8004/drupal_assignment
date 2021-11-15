(function ($, Drupal) {
  document.addEventListener('refreshCart', (e) => {
    let amount;
    try {
      amount = e.detail.data().totals.base_grand_total;
    }
    catch (e) {
      amount = 0;
    }
    const tabbyWidget = $('#spc-cart').find('.' + drupalSettings.tabby.widgetInfo.class);
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
