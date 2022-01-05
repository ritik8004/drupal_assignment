import React from 'react';
import {
  Configure,
  InstantSearch,
} from 'react-instantsearch-dom';
import { searchClient } from '../../../../js/utilities/algoliaHelper';
import LoginMessage from '../../../../js/utilities/components/login-message';
import ProductInfiniteHits from './ProductInfiniteHits';
import WishlistPagination from './WishlistPagination';
import PageEmptyMessage from '../../../../js/utilities/components/page-empty-message';
import NotificationMessage from '../../../../js/utilities/components/notification-message';
import {
  getWishListData,
  isAnonymousUser,
  isShareWishlistPage,
  getSharedWishlistFromBackend,
  getWishlistLabel,
} from '../../../../js/utilities/wishlistHelper';
import { createConfigurableDrawer } from '../../../../js/utilities/addToBagHelper';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import Loading from '../../../../js/utilities/loading';

class WishlistProductList extends React.Component {
  constructor(props) {
    super(props);
    let wishListItems = {};
    let wishListItemsCount = 0;
    // If the current page is not a shared wishlist page. We
    // get wishlist data from the local storage.
    if (!isShareWishlistPage()) {
      // Get the wishlist items.
      wishListItems = getWishListData() || {};
      wishListItemsCount = Object.keys(wishListItems).length;
    }

    const filters = (wishListItemsCount > 0)
      ? this.getFiltersFromWishListItems(wishListItems)
      : null;

    this.state = {
      filters,
      wishListItemsCount,
      wait: true,
    };
  }

  componentDidMount() {
    // If this is a shared wishlist page, we need to fetch shared wishlist
    // products from the backend via API and show on the page.
    if (isShareWishlistPage()) {
      // Get the shared wishlist items from the backend API.
      getSharedWishlistFromBackend().then((response) => {
        if (hasValue(response.data.items)) {
          const wishListItems = {};

          response.data.items.forEach((item) => {
            wishListItems[item.sku] = {
              sku: item.sku,
              options: item.options,
            };
          });

          // Update the wishlist items count and filters in state
          // to show the shared wishlist data.
          const wishListItemsCount = Object.keys(wishListItems).length;
          if (wishListItemsCount > 0) {
            const filters = this.getFiltersFromWishListItems(wishListItems);
            this.setState({ filters, wishListItemsCount });
          }
        }

        this.setState({ wait: false });
      });
      // We don't want event listeners for shared wishlist page.
      return;
    }

    if (!isAnonymousUser()) {
      // Add event listener for get wishlist load event for logged in user.
      // This will execute when wishlist loaded from the backend
      // and page loads before.
      document.addEventListener('getWishlistFromBackendSuccess', this.updateWisListProductsList, false);
    }
    // For wishlist page, data will always be available in storage.
    // So, we should stop the loader after data has loaded from storage.
    if (!isShareWishlistPage()) {
      this.setState({
        wait: false,
      });
    }
    // Update wishlist items after any product is removed.
    document.addEventListener('productRemovedFromWishlist', this.updateWisListProductsList, false);
  }

  /**
   * Remove event listners after component gets unmount.
   */
  componentWillUnmount() {
    // We don't have event listeners for shared wishlist page.
    if (isShareWishlistPage()) {
      return;
    }

    if (!isAnonymousUser()) {
      document.removeEventListener('getWishlistFromBackendSuccess', this.updateWisListProductsList, false);
    }
    document.removeEventListener('productRemovedFromWishlist', this.updateWisListProductsList, false);
  }

  /**
   * Update product listing on my wishlist page after
   * wishlist info is available in storage.
   */
  updateWisListProductsList = () => {
    // Get the wishlist items.
    const wishListItems = getWishListData() || {};
    const wishListItemsCount = Object.keys(wishListItems).length;

    const filters = (wishListItemsCount > 0)
      ? this.getFiltersFromWishListItems(wishListItems)
      : null;

    this.setState({
      filters,
      wishListItemsCount,
    });
  };

  /**
   * Prepare search filters for the provided wishlist items.
   *
   * @param {object} wishListItems
   *  Data containing wishlist items information.
   *
   * @returns {string}
   *  Filters to pass in search widget.
   */
  getFiltersFromWishListItems = (wishListItems) => {
    const filters = [];
    Object.keys(wishListItems).forEach((key, index) => {
      // Prepare filter to pass in search widget. For example,
      // 1. "sku:0433350007 OR sku:0778064001 OR sku:HM0485540011187007"
      // 2. "sku:0433350007<score=5> OR sku:0778064001<score=4>
      // OR sku:HM0485540011187007<score=3>".
      // We are using filter scoring to sort the results. So
      // the higher score item will display first.
      filters.push(`sku: "${key}"<score=${index}>`);
    });

    // Prepare the final filter to pass in search widget.
    return `(${filters.join(' OR ')})`;
  };

  render() {
    const { filters, wishListItemsCount, wait } = this.state;
    if (wait) {
      return <Loading />;
    }
    // Render empty wishlist component.
    // Check for wishlist data loaded via api if logged in user.
    // If anonymous user, check if wishlist item count is 0.
    if (wishListItemsCount === 0) {
      return PageEmptyMessage(Drupal.t(
        'your @wishlist_label is empty.',
        { '@wishlist_label': getWishlistLabel() },
        { context: 'wishlist' },
      ));
    }

    // Get the items per page setting from the drupal settings.
    const { itemsPerPage } = drupalSettings.algoliaSearch;

    // Add the drawer markup for add to bag feature.
    createConfigurableDrawer(true);

    return (
      <>
        <ConditionalView condition={isAnonymousUser() && !isShareWishlistPage()}>
          <LoginMessage />
        </ConditionalView>
        <NotificationMessage />
        <InstantSearch indexName={drupalSettings.wishlist.indexName} searchClient={searchClient}>
          <Configure
            // To test the pagination we can hardcode this to static number.
            hitsPerPage={itemsPerPage}
            filters={filters}
          />
          <div id="plp-hits" className="c-products-list product-small view-algolia-plp">
            <ProductInfiniteHits
              gtmContainer="wishlist page"
              pageType="plp"
              wishListItemsCount={wishListItemsCount}
            >
              {(paginationArgs) => (
                <WishlistPagination {...paginationArgs}>
                  {Drupal.t('Load more products')}
                </WishlistPagination>
              )}
            </ProductInfiniteHits>
          </div>
        </InstantSearch>
      </>
    );
  }
}

export default WishlistProductList;
