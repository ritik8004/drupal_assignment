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

    // Check cart have tabby info widget.
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', Drupal.tabbyInfoChange);
    } else {
      Drupal.tabbyInfoChange();
    }
  });

  // Function to monitor info widget change.
  Drupal.tabbyInfoChange = function () {
    if (!$('.spc-pre-content').hasClass('hidden')) {
      return;
    }
    setTimeout(() => {
      // Check if tabby info is present and make pre content visible.
      const tabbyInfo = $('.spc-tabby-info');
      if (tabbyInfo.children().length) {
        tabbyInfo.closest('.spc-pre-content').removeClass('hidden');
      }
    }, 500);
  }
})(jQuery, Drupal);
