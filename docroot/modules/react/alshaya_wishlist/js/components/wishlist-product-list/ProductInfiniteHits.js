import React, { useRef, useEffect } from 'react';
import connectInfiniteHits from '../../../../alshaya_algolia_react/js/src/components/algolia/connectors/connectInfiniteHits';
import Teaser from '../../../../alshaya_algolia_react/js/src/components/teaser';
import { removeLoader } from '../../../../alshaya_algolia_react/js/src/utils';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { getWishlistItemInStockStatus } from '../../utilities/wishlist-utils';

const ProductInfiniteHits = connectInfiniteHits(({
  hits, hasMore, refineNext, children = null, gtmContainer, pageType,
}) => {
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  useEffect(
    () => {
      if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
        removeLoader();
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
