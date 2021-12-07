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
import { getWishListData } from '../../utilities/wishlist-utils';
import { createConfigurableDrawer } from '../../../../js/utilities/addToBagHelper';

class WishlistProductList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      filters: null,
      wishListItemsCount: 0,
    };
  }

  componentDidMount() {
    // Get the wishlist items.
    const wishListItems = getWishListData() || {};
    const wishListItemsCount = Object.keys(wishListItems).length;

    // Early return if no products in wishlist.
    if (!wishListItemsCount) {
      // Update the state with wishListItemsCount and return.
      this.setState({
        wait: false,
        wishListItemsCount,
      });
      return;
    }

    // Proceed further if we have products in wishlist.
    const filters = [];
    let finalFilter = '';

    Object.keys(wishListItems).forEach((key, index) => {
      // Prepare filter to pass in search widget. For example,
      // 1. "sku:0433350007 OR sku:0778064001 OR sku:HM0485540011187007"
      // 2. "sku:0433350007<score=5> OR sku:0778064001<score=4>
      // OR sku:HM0485540011187007<score=3>".
      // We are using filter scoring to sort the results. So
      // the higher score item will display first.
      filters.push(`sku: ${key}<score=${wishListItemsCount - index}>`);
    });

    // Prepare the final filter to pass in search widget.
    finalFilter = `${finalFilter}(${filters.join(' OR ')})`;

    // Update the state as filters are ready.
    this.setState({
      wait: false,
      filters: finalFilter,
      wishListItemsCount,
    });
  }

  render() {
    const { wait, filters, wishListItemsCount } = this.state;

    // Return null if products data are not available yet.
    if (wait) {
      return null;
    }

    // Render empty wishlist component if wishlist is empty.
    if (!wishListItemsCount) {
      const message = drupalSettings.wishlist.config.emptyWishListMessage;
      return PageEmptyMessage(message);
    }

    // Get the items per page setting from the drupal settings.
    const { itemsPerPage } = drupalSettings.algoliaSearch;

    // Add the drawer markup for add to bag feature.
    createConfigurableDrawer(true);

    return (
      <>
        <LoginMessage />
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
