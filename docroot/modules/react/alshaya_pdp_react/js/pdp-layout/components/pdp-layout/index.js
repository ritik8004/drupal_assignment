import React from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';

const PdpLayout = () => {
  let skuItemCode = null;
  const { pdpGallery } = drupalSettings;
  if (pdpGallery) {
    [skuItemCode] = Object.keys(pdpGallery);
  }
  const emptyRes = (
    <div>Product data not available</div>
  );

  return (skuItemCode && pdpGallery) ? (
    <>
      {' '}
      <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
      <PdpDescription skuCode={skuItemCode} pdpDescription={pdpGallery} />
      {' '}

    </>
  ) : emptyRes;
};

export default PdpLayout;
