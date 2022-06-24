import React, { Suspense } from 'react';
import { isAddToBagEnabled } from '../../addToBagHelper';
import EmptyErrorBoundary from '../empty-error-boundary/EmptyErrorBoundary';
import AddToBag from '../../../../alshaya_add_to_bag/js/components/addtobag';

function AddToBagContainer(props) {
  const {
    productData,
    isBuyable,
    extraInfo,
    wishListButtonRef,
    styleCode,
  } = props;

  // Return if product data is undefined or empty.
  if (typeof productData === 'undefined' || !productData) {
    return null;
  }

  // 'showAddToBag' is used to decide whether we want
  // to show the add to bag button or not.
  const { showAddToBag } = extraInfo || {};

  if (isAddToBagEnabled() || showAddToBag) {
    return (
      <EmptyErrorBoundary>
        <Suspense fallback={<div />}>
          <AddToBag
            url={props.url}
            sku={props.sku}
            stockQty={props.stockQty}
            productData={productData}
            isBuyable={isBuyable}
            extraInfo={extraInfo || {}}
            wishListButtonRef={wishListButtonRef}
            styleCode={styleCode}
          />
        </Suspense>
      </EmptyErrorBoundary>
    );
  }

  return null;
}

export default AddToBagContainer;
