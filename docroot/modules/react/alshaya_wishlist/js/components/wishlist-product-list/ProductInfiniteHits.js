import React, { useRef, useEffect } from 'react';
import connectInfiniteHits from '../../../../alshaya_algolia_react/js/src/components/algolia/connectors/connectInfiniteHits';
import Teaser from '../../../../alshaya_algolia_react/js/src/components/teaser';
import { removeLoader } from '../../../../alshaya_algolia_react/js/src/utils';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { getWishlistItemInStockStatus, removeDiffFromWishlist } from '../../utilities/wishlist-utils';
import dispatchCustomEvent from '../../../../js/utilities/events';

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
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  useEffect(
    () => {
      if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
        removeLoader();
      }

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
              "Some product in your wishlist doesn't exist anymore!",
              {},
              { context: 'wishlist' },
            ),
          },
        );

        // Remove products from the wishlist which are not available
        // in the algolia search results. These products may be dis-
        // continued or removed from backend.
        removeDiffFromWishlist(hits);
      }
    }, [hits],
  );

  return (
    <>
      <div className="view-content" ref={teaserRef}>
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
