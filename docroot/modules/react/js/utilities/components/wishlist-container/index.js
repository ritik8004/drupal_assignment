import React, { Suspense } from 'react';
import { isWishlistEnabled } from '../../wishlistHelper';
import EmptyErrorBoundary from '../empty-error-boundary/EmptyErrorBoundary';

function WishlistContainer(props) {
  // Return if feature is not enabled.
  if (!isWishlistEnabled()) {
    return null;
  }

  const {
    context,
    position,
    skuMainCode,
    sku,
    format,
    title,
  } = props;

  // Lazy load wishlist button component.
  const WishlistLazy = React.lazy(() => import('../../../../alshaya_wishlist/js/components/wishlist-button'));

  return (
    <EmptyErrorBoundary>
      <Suspense fallback={<div />}>
        <WishlistLazy
          context={context}
          position={position}
          format={format}
          skuMainCode={skuMainCode}
          sku={sku}
          title={title}
        />
      </Suspense>
    </EmptyErrorBoundary>
  );
}

export default WishlistContainer;
