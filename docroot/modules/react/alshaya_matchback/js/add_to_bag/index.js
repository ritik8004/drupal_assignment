import React from 'react';
import AddToBagContainer from '../../../js/utilities/components/addtobag-container';

function MatchbackAddToBag({
  sku,
  url,
  stockQty,
  isBuyable,
  extraInfo,
  productData,
}) {
  return (
    <AddToBagContainer
      url={url}
      sku={sku}
      stockQty={stockQty}
      productData={productData}
      isBuyable={isBuyable}
      extraInfo={extraInfo}
      wishListButtonRef={{}}
      styleCode={null}
    />
  );
}

export default MatchbackAddToBag;
