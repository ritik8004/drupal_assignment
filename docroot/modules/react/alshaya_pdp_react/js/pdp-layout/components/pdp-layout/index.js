import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';
import PdpCart from '../pdp-cart';

const PdpLayout = () => {
  let skuItemCode = null;
  let configurableCombinations = null;
  const { productInfo } = drupalSettings;
  let title = '';
  let priceRaw = '';
  let finalPrice = '';
  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
    title = productInfo[skuItemCode].title.label;
    priceRaw = productInfo[skuItemCode].priceRaw;
    finalPrice = productInfo[skuItemCode].finalPrice;
    if (productInfo[skuItemCode].type === 'configurable') {
      configurableCombinations = drupalSettings.configurableCombinations;
      const variantSelected = configurableCombinations[skuItemCode].firstChild;
      title = productInfo[skuItemCode].variants[variantSelected].title;
      priceRaw = productInfo[skuItemCode].variants[variantSelected].priceRaw;
      finalPrice = productInfo[skuItemCode].variants[variantSelected].finalPrice;
    }
  }
  const shortDesc = skuItemCode ? productInfo[skuItemCode].shortDesc : [];
  const description = skuItemCode ? productInfo[skuItemCode].description : [];
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
        <div className="magv2-content" id="pdp-gallery-refresh">
          <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
        </div>
        <div className="magv2-sidebar">
          <PdpDescription
            skuCode={skuItemCode}
            pdpDescription={description}
            pdpShortDesc={shortDesc}
          />
          <div id="pdp-info">
            <PdpInfo
              title={title}
              pdpProductPrice={priceRaw}
              finalPrice={finalPrice}
            />
          </div>
          <PdpCart
            skuCode={skuItemCode}
            configurableCombinations={configurableCombinations}
            productInfo={productInfo}
          />
        </div>
      </div>
    </>
  ) : emptyRes;
};

export default PdpLayout;
