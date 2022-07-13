/**
 * @file
 * Contains the logic of merging anonymous wishlist products upon sign in. Also,
 * responsible for loading wishlist items for authenticate customers on page
 * load as per the configurations.
 */
Drupal.alshayaWishlist = Drupal.alshayaWishlist || {};

// Flag used to check if wishlist data are already loaded from backend and
// stored data in local storage.
window.wishListLoadedFromBackend = window.wishListLoadedFromBackend || false;

(function (Drupal) {

  /**
   * Helper function to load wishlist information from the magento backend.
   */
  Drupal.alshayaWishlist.loadWishlistFromBackend = function() {
    window.commerceBackend.getWishlistFromBackend().then((response) => {
      let wishListItemCount = 0;
      if (typeof response.data.items !== 'undefined'
        && response.data.items !== null
        && response.data.items.length > 0) {
        const wishListItems = [];

        response.data.items.forEach((item) => {
          wishListItems.push({
            sku: item.sku,
            options: item.options,
            // We need this for removing the item from the wishlist.
            wishlistItemId: item.wishlist_item_id,
            // OOS status of product in backend.
            inStock: item.is_in_stock,
          });
        });

        // Save back to storage.
        // Store data to local storage.
        Drupal.addItemInLocalStorage(
          'loggedInUserwishlistInfo',
          wishListItems,
          ((typeof drupalSettings.wishlist.config !== 'undefined'
            && typeof drupalSettings.wishlist.config.localStorageExpirationForLoggedIn !== 'undefined')
            ? parseInt(drupalSettings.wishlist.config.localStorageExpirationForLoggedIn, 10)
            : 0),
        );

        // Get wishlist item count.
        wishListItemCount = Object.keys(wishListItems).length;
      }
      // Dispatch an event for other modules to know
      // that wishlist data is available in storage.
      const getWishlistFromBackendSuccess = new CustomEvent('getWishlistFromBackendSuccess', {
        bubbles: true,
        detail: {
          wishListItemCount,
        },
      });
      document.dispatchEvent(getWishlistFromBackendSuccess);

      // Alongside dispatching an success event above, let's set the global
      // variable flag to true as well. So other components like wishlist
      // product list, if rendered lately, can utilise this flag to fetch data
      // from the local storage as well instead of completing relying on
      // `getWishlistFromBackendSuccess` success event.
      window.wishListLoadedFromBackend = true;
    });
  };

  Drupal.behaviors.alshayaWishlistLoadOrMerge = {
    attach: function (context) {
      // Check if user is an authenticate user, we have wishlist data
      // in local storage from guest users and merge wishlist info for logged
      // in user is true, we will call backend api to merge wishlist info
      // in magento for the customer. Once it is merged, get updated
      // data from magento and update the wishlist info data in local storage.
      // If merge flag is false, we will check for wishlist data in local
      // storage for logged in user and if found nothing, we will load from
      // backend.
      if (typeof drupalSettings.wishlist === 'undefined'
        || typeof drupalSettings.wishlist.enabled === 'undefined'
        || !drupalSettings.wishlist.enabled
        || drupalSettings.user.uid === 0) {
        return;
      }

      // Guest user's wishlist data from local storage.
      const wishListDataOfGuestUser = Drupal.getItemFromLocalStorage('guestUserwishlistInfo');
      if (wishListDataOfGuestUser
        && typeof wishListDataOfGuestUser === 'object'
        && Object.keys(wishListDataOfGuestUser).length > 0
        && typeof drupalSettings.wishlist.mergeWishlistForLoggedInUsers !== 'undefined'
        && drupalSettings.wishlist.mergeWishlistForLoggedInUsers) {
        // Remove wishlist info from local storage for guest users, as
        // we don't want this info to share with logged in users.
        Drupal.removeItemFromLocalStorage('guestUserwishlistInfo');

        // Merge wishlist information to the magento backend from local storage,
        // if wishlist data available in local storage and merging wishlist
        // data flag is set to true.

        // Prepare the wishlist item data to push in backend api.
        const itemData = [];
        Object.values(wishListDataOfGuestUser).forEach((item) => {
          // Prepare sku options if available to push in backend api.
          const skuOptions = [];
          if (item.options.length > 0) {
            item.options.forEach((option) => {
              skuOptions.push({
                id: option.option_id,
                value: option.option_value,
              });
            });
          }

          itemData.push({
            sku: item.sku,
            options: skuOptions,
          });
        });

        // If we have items for wishlist then add the items in backend
        // wishlist with api call. Once added successfully, we need to
        // load the latest wishlist information from backend as well.
        if (itemData.length > 0) {
          window.commerceBackend.addRemoveWishlistItemsInBackend(
            itemData,
            'mergeWishlistItems',
          ).then((response) => {
            if (typeof response.data.status !== 'undefined'
              && response.data.status) {
              Drupal.alshayaWishlist.loadWishlistFromBackend();
            }
          });
        }
      } else {
        // Get wishlist data from the local storage.
        const wishListData = Drupal.getItemFromLocalStorage('loggedInUserwishlistInfo');
        if ((typeof drupalSettings.wishlist.config.forceLoadWishlistFromBackend !== 'undefined'
          && drupalSettings.wishlist.config.forceLoadWishlistFromBackend)
          || (wishListData === null
          || (typeof wishListData === 'object'
            && Object.keys(wishListData).length === 0))) {
          // First clean the existing data in storage.
          Drupal.removeItemFromLocalStorage('loggedInUserwishlistInfo');

          // Load wishlist information from the magento backend, if wishlist
          // data is empty in local storage for authenticate users. First check
          // if wishlist is available for the customer in backend.
          Drupal.alshayaWishlist.loadWishlistFromBackend();
        }
      }
    }
  };
})(Drupal);
