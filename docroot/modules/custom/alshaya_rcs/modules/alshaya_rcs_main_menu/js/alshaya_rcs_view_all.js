(function main(Drupal, RcsEventManager) {
  // Event listener to add the View All link in L3 menus.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Only when placeholder is navigation_menu.
    if (Drupal.hasValue(e.detail.placeholder)
      && e.detail.placeholder === 'navigation_menu'
      && e.detail.result) {
      e.detail.result.map(function (l2data) {
        l2data.children.map(function (l3data) {
          if (l3data.display_view_all === 1) {
            l3data.children.unshift({
              display_view_all: 0,
              id: '',
              include_in_menu: l3data.include_in_menu,
              is_anchor: l3data.is_anchor,
              level: (l3data.level) + 1,
              meta_title: l3data.meta_title,
              name: Drupal.t('View All'),
              position: 1,
              show_in_app_navigation: l3data.show_in_app_navigation,
              show_in_lhn: l3data.show_in_lhn,
              show_on_dpt: l3data.show_on_dpt,
              url_key: 'view_all',
              url_path: l3data.url_path + '/view-all',
            });
          }
        });
      });
    }
  });
})(Drupal, RcsEventManager);
