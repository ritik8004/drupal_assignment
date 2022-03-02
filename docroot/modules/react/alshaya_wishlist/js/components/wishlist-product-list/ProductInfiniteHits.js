import React, { useEffect } from 'react';
import connectInfiniteHits from '../../../../alshaya_algolia_react/js/src/components/algolia/connectors/connectInfiniteHits';
import Teaser from '../../../../alshaya_algolia_react/js/src/components/teaser';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import {
  getWishlistItemInStockStatus,
  removeDiffFromWishlist,
  getWishlistLabel,
} from '../../../../js/utilities/wishlistHelper';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { removeFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';

const ProductInfiniteHits = connectInfiniteHits(({
  hits,
  nbHits,
  hasMore,
  refineNext,
  children = null,
  gtmContainer,
  pageType,
  wishListItemsCount,
}) => {
  useEffect(
    () => {
      // Check if we receive less number of products from Algolia and
      // have more data in wishlist storage, we will show a notification
      // to customers and will remove such products from their wishlist.
      if (wishListItemsCount > 0
        && nbHits > 0
        && nbHits < wishListItemsCount) {
        // Dispatch an event with notification message for notification
        // component to listen and show the given message on the page.
        dispatchCustomEvent(
          'showNotificationMessage',
          {
            // @todo: need to confirm the message and add translation.
            message: Drupal.t(
              "Some product in your @wishlist_label doesn't exist anymore!",
              { '@wishlist_label': getWishlistLabel() },
              { context: 'wishlist' },
            ),
          },
        );

        // Remove products from the wishlist which are not available
        // in the algolia search results. These products may be dis-
        // continued or removed from backend.
        removeDiffFromWishlist(hits);
      }

      if (hits.length > 0) {
        // Trigger gtm event one time, only when search we have search results.
        Drupal.algoliaReactWishlist.triggerResultsUpdatedEvent(hits);
      }

      // Remove loader after pagination results updated.
      removeFullScreenLoader();
    }, [hits],
  );

  return (
    <>
      <div className="view-content">
        <ConditionalView condition={hits.length > 0}>
          {
            hits.map((hit) => (
              <Teaser
                key={hit.objectID}
                hit={hit}
                gtmContainer={gtmContainer}
                pageType={pageType}
                extraInfo={{
                  isWishlistPage: true,
                  showAddToBag: true,
                  addToCartButtonText: Drupal.t('Move to basket', {}, { context: 'wishlist' }),
                  inStock: getWishlistItemInStockStatus(hit),
                }}
              />
            ))
          }
        </ConditionalView>
      </div>

      {children && children({
        results: hits.length,
        hasMore,
        refineNext,
      })}
    </>
  );
});

export default ProductInfiniteHits;
