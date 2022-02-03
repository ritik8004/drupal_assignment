/**
 * @file
 * Store Finder preserve history.
 */

(function () {
    Drupal.behaviors.storeFinderPreserveHistory = {
        attach: function () {
            // Push store finder to history when accessing the store detail via store-finder AJAX.
            window.history.pushState(history.state, Drupal.t('Store Finder'), Drupal.url('store-finder'));

            // Force reload the store finder page on pop-state here so that end-users
            // see the store-finder listing page content as well along with url switch.
            window.addEventListener("popstate", function (e) {
                window.location.reload();
            });
        }
    }
}());
