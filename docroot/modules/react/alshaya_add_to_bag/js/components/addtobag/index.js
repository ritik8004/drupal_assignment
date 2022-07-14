import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import AddToBagConfigurable from '../addtobag-configurable';
import AddToBagSimple from '../addtobag-simple';

const AddToBag = (props) => {
  const {
    sku,
    url,
    stockQty,
    productData,
    isBuyable,
    extraInfo,
    wishListButtonRef,
    styleCode,
  } = props;

  const skuType = productData.sku_type;

  return (
    <>
      <ConditionalView condition={skuType === 'simple'}>
        <AddToBagSimple
          sku={sku}
          stockQty={stockQty}
          productData={productData}
          url={url}
          isBuyable={isBuyable}
          extraInfo={extraInfo}
        />
      </ConditionalView>

      <ConditionalView condition={skuType === 'configurable'}>
        <AddToBagConfigurable
          sku={sku}
          url={url}
          productData={productData}
          isBuyable={isBuyable}
          extraInfo={extraInfo}
          wishListButtonRef={wishListButtonRef}
          styleCode={styleCode}
        />
      </ConditionalView>
    </>
  );
};

export default AddToBag;
