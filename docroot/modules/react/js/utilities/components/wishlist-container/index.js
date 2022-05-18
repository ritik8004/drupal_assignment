import React, { Suspense } from 'react';
import {
  isWishlistEnabled,
  isShareWishlistPage,
} from '../../wishlistHelper';
import EmptyErrorBoundary from '../empty-error-boundary/EmptyErrorBoundary';

function WishlistContainer(props) {
  // Return if feature is not enabled or
  // we are on shared wishlist page.
  if (!isWishlistEnabled() || isShareWishlistPage()) {
    return null;
  }

  const {
    context,
    position,
    skuCode,
    sku,
    format,
    title,
    options,
    setWishListButtonRef,
    wishListButtonRef,
  } = props;

  // Lazy load wishlist button component.
  const WishlistLazy = React.lazy(() => import('../../../../alshaya_wishlist/js/components/wishlist-button' /* webpackChunkName: "wls" */));
  return (
    <EmptyErrorBoundary>
      <Suspense fallback={<div />}>
        {/* skuCode is parent sku of selected variant and sku is default sku of current pdp. */}
        <WishlistLazy
          context={context}
          position={position}
          format={format}
          skuCode={skuCode}
          sku={sku}
          title={title}
          options={options}
          ref={setWishListButtonRef}
          wishListButtonRef={wishListButtonRef}
        />
      </Suspense>
    </EmptyErrorBoundary>
  );
}

export default WishlistContainer;
