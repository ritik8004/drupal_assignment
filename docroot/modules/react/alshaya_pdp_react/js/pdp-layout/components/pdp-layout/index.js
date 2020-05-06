import React from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';

const PdpLayout = () => {
  let skuItemCode = null;
  const { pdpGallery } = drupalSettings;
  if (pdpGallery) {
    [skuItemCode] = Object.keys(pdpGallery);
  }
  const shortDesc = skuItemCode ? pdpGallery[skuItemCode].shortDesc : [];
  const description = skuItemCode ? pdpGallery[skuItemCode].description : [];
  const title = skuItemCode ? pdpGallery[skuItemCode].title : null;
  const productPrice = skuItemCode ? pdpGallery[skuItemCode].productPrice : [];

  const emptyRes = (
    <div>Product data not available</div>
  );

  return (skuItemCode && pdpGallery) ? (
    <>
      {' '}
      <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
      <PdpDescription skuCode={skuItemCode} pdpDescription={description} pdpShortDesc={shortDesc} />
      <PdpInfo skuCode={skuItemCode} title={title} pdpProductPrice={productPrice} />
      {' '}

    </>
  ) : emptyRes;
};

export default PdpLayout;
