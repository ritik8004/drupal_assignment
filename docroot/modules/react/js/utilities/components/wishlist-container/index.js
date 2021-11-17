import React, { Suspense } from 'react';
import { isWishlistEnabled } from '../../wishlistHelper';
import EmptyErrorBoundary from '../empty-error-boundary/EmptyErrorBoundary';

function WishlistContainer(props) {
  // Return if feature is not enabled.
  if (!isWishlistEnabled()) {
    return null;
  }

  const { context, position, sku } = props;

  // Lazy load wishlist button component.
  const WishlistLazy = React.lazy(() => import('../../../../alshaya_wishlist/js/components/wishlist-button'));

  return (
    <EmptyErrorBoundary>
      <Suspense fallback={<div />}>
        <WishlistLazy
          context={context}
          position={position}
          sku={sku}
        />
      </Suspense>
    </EmptyErrorBoundary>
  );
}

export default WishlistContainer;
