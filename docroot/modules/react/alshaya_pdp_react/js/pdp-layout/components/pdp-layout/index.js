import React from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';

const PdpLayout = () => {
  let skuItemCode = null;
  const { productInfo } = drupalSettings;
  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }
  const shortDesc = skuItemCode ? productInfo[skuItemCode].shortDesc : [];
  const description = skuItemCode ? productInfo[skuItemCode].description : [];
  const title = skuItemCode ? productInfo[skuItemCode].title : null;
  const priceRaw = skuItemCode ? productInfo[skuItemCode].priceRaw : null;
  const finalPrice = skuItemCode ? productInfo[skuItemCode].finalPrice : null;
  const pdpGallery = skuItemCode ? productInfo[skuItemCode].rawGallery : [];

  const emptyRes = (
    <div>Product data not available</div>
  );

  return (skuItemCode && pdpGallery) ? (
    <>
      {' '}
      <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
      <PdpDescription skuCode={skuItemCode} pdpDescription={description} pdpShortDesc={shortDesc} />
      <PdpInfo
        skuCode={skuItemCode}
        title={title}
        pdpProductPrice={priceRaw}
        finalPrice={finalPrice}
      />
      {' '}

    </>
  ) : emptyRes;
};

export default PdpLayout;
