import React from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';

const PdpLayout = () => {
  let skuItemCode = null;
  const { pdpGallery } = drupalSettings;
  if (pdpGallery) {
    [skuItemCode] = Object.keys(pdpGallery);
  }
  return (
    <>
      <div className="pdp-layout-wrapper">
        Item Code:
        {skuItemCode}
      </div>
      {(skuItemCode && pdpGallery) && (
        <>
          <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
          <PdpDescription skuCode={skuItemCode} pdpDescription={pdpGallery} />
        </>
      )}

    </>
  );
};
export default PdpLayout;
