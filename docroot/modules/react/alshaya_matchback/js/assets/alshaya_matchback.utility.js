(function(Drupal, $) {
  // Push to GTM when add to bag product drawer is opened.
  document.addEventListener('drawerOpenEvent', function ondrawerOpen(e) {
    var element = $(e.detail.triggerButtonElement).closest('article.entity--type-node');
    if (element.length) {
      Drupal.alshayaSeoGtmPushProductDetailView(element);
    }
  });
})(Drupal, jQuery);
