import React from 'react';
import {
  Configure,
  InstantSearch,
} from 'react-instantsearch-dom';
import { searchClient } from '../../../../alshaya_algolia_react/js/src/config/SearchClient';
import LoginMessage from '../login-message';
import ProductInfiniteHits from './ProductInfiniteHits';
import WishlistPagination from './WishlistPagination';
import { getWishListData } from '../../utilities/wishlist-utils';
import { createConfigurableDrawer } from '../../../../js/utilities/addToBagHelper';

class WishlistProductList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      filters: null,
    };
  }

  componentDidMount() {
    // Get the wishlist items.
    const wishListItems = getWishListData();
    const wishListItemsCount = Object.keys(wishListItems).length;
    if (wishListItemsCount > 0) {
      const filters = [];
      let finalFilter = '';

      // Do not show out of stock products.
      if (drupalSettings.algoliaSearch.filterOos === true) {
        finalFilter = '(stock > 0) AND ';
      }

      Object.keys(wishListItems).forEach((key, index) => {
        // Push sku filter with filter score.
        filters.push(`sku: ${key}<score=${wishListItemsCount - index}>`);
      });

      // Prepare filter to pass in search widget. For example,
      // 1. filters="sku:0433350007 OR sku:0778064001 OR sku:HM0485540011187007"
      // 2. filters="sku:0433350007<score=5> OR sku:0778064001<score=4>
      // OR sku:HM0485540011187007<score=3>"
      finalFilter = `${finalFilter}(${filters.join(' OR ')})`;

      // Update the state as filters are ready.
      this.setState({
        wait: false,
        filters: finalFilter,
      });
    }
  }

  render() {
    const { wait, filters } = this.state;

    // Return null if products data are not available yet.
    if (wait) {
      // @todo: add no result display component.
      return null;
    }

    // Get the items per page setting from the drupal settings.
    const { itemsPerPage } = drupalSettings.algoliaSearch;

    // Add the drawer markup for add to bag feature.
    createConfigurableDrawer(true);

    return (
      <>
        <div className="c-plp c-plp-only l-one--w lhn-without-sidebar l-container">
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
                pageType="wishlist"
              >
                {(paginationArgs) => (
                  <WishlistPagination {...paginationArgs}>
                    {Drupal.t('Load more products')}
                  </WishlistPagination>
                )}
              </ProductInfiniteHits>
            </div>
          </InstantSearch>
        </div>
      </>
    );
  }
}

export default WishlistProductList;
