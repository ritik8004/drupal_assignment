import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
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
      <div className="magv2-header">
        <ConditionalView condition={window.innerWidth < 768}>
          {/* Render mobile sticky header component */}
        </ConditionalView>
        <ConditionalView condition={window.innerWidth > 768}>
          {/* Render desktop sticky header component */}
        </ConditionalView>
      </div>
      <div className="magv2-main">
        <div className="magv2-content">
          <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
        </div>
        <div className="magv2-sidebar">
          <PdpDescription
            skuCode={skuItemCode}
            pdpDescription={description}
            pdpShortDesc={shortDesc}
          />
          <PdpInfo
            skuCode={skuItemCode}
            title={title}
            pdpProductPrice={priceRaw}
            finalPrice={finalPrice}
          />
        </div>
      </div>
    </>
  ) : emptyRes;
};

export default PdpLayout;
