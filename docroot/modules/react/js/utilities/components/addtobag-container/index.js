import React, { Suspense } from 'react';
import { isAddToBagEnabled } from '../../addToBagHelper';
import EmptyErrorBoundary from '../empty-error-boundary/EmptyErrorBoundary';

function AddToBagContainer(props) {
  const { productData } = props;

  // Return if product data is undefined or empty.
  if (typeof productData === 'undefined' || !productData) {
    return null;
  }

  if (isAddToBagEnabled()) {
    const AddToBagLazy = React.lazy(() => import('../../../../alshaya_add_to_bag/js/components/addtobag' /* webpackChunkName: "atb" */));

    return (
      <EmptyErrorBoundary>
        <Suspense fallback={<div />}>
          <AddToBagLazy
            url={props.url}
            sku={props.sku}
            stockQty={props.stockQty}
            productData={productData}
          />
        </Suspense>
      </EmptyErrorBoundary>
    );
  }

  return null;
}

export default AddToBagContainer;
