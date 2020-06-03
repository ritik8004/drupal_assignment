import React, { useRef, useEffect } from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';
import PdpCart from '../pdp-cart';
import PdpDetail from '../pdp-detail';
import PdpHeader from '../pdp-header';

const PdpLayout = () => {
  let skuItemCode; let brandLogo; let brandLogoAlt; let
    brandLogoTitle = null;
  let configurableCombinations = '';

  const { productInfo } = drupalSettings;
  let title = '';
  let priceRaw = '';
  let finalPrice = '';
  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }
  if (skuItemCode) {
    if (productInfo[skuItemCode].brandLogo) {
      brandLogo = productInfo[skuItemCode].brandLogo.logo
        ? productInfo[skuItemCode].brandLogo.logo : null;
      brandLogoAlt = productInfo[skuItemCode].brandLogo.alt
        ? productInfo[skuItemCode].brandLogo.alt : null;
      brandLogoTitle = productInfo[skuItemCode].brandLogo.title
        ? productInfo[skuItemCode].brandLogo.title : null;
    }
    title = productInfo[skuItemCode].title;
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

  const header = useRef();
  let content;
  const getChildRef = (ref) => {
    content = ref;
  };

  const showStickyHeader = () => {
    window.onscroll = function () {
      if (window.pageYOffset >= content.current.offsetTop + content.current.offsetHeight) {
        header.current.classList.add('magv2-pdp-sticky-header');
      } else {
        header.current.classList.remove('magv2-pdp-sticky-header');
      }
    };
  };
  useEffect(() => {
    showStickyHeader();
  },
  [
    showStickyHeader,
  ]);

  return (skuItemCode && pdpGallery) ? (
    <>
      <div className="magv2-header" ref={header}>
        <PdpHeader
          title={title.label}
          finalPrice={finalPrice}
          pdpProductPrice={priceRaw}
          brandLogo={brandLogo}
          brandLogoAlt={brandLogoAlt}
          brandLogoTitle={brandLogoTitle}
        />
      </div>
      <div className="magv2-main">
        <div className="magv2-content" id="pdp-gallery-refresh">
          <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
        </div>
        <div className="magv2-sidebar">
          <PdpDetail
            title={title.label}
            finalPrice={finalPrice}
            pdpProductPrice={priceRaw}
            childRef={(ref) => (getChildRef(ref))}
            brandLogo={brandLogo}
            brandLogoAlt={brandLogoAlt}
            brandLogoTitle={brandLogoTitle}
          />
          <PdpDescription
            skuCode={skuItemCode}
            pdpDescription={description}
            pdpShortDesc={shortDesc}
            title={title}
            pdpProductPrice={priceRaw}
            finalPrice={finalPrice}
          />
          <div id="pdp-info">
            <PdpInfo
              skuCode={skuItemCode}
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
