import React, { useRef, useEffect, useState } from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';
import PdpCart from '../pdp-cart';
import PdpHeader from '../pdp-header';
import { getProductValues } from '../../../utilities/pdp_layout';

const PdpLayout = () => {
  const [variant, setVariant] = useState(null);
  const pdpRefresh = (variantSelected) => {
    setVariant(variantSelected);
  };

  const { productInfo } = drupalSettings;
  let skuItemCode = null;
  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }

  const productValues = getProductValues(skuItemCode, variant, setVariant);
  const {
    brandLogo,
    brandLogoAlt,
    brandLogoTitle,
    title,
    priceRaw,
    finalPrice,
    pdpGallery,
    shortDesc,
    description,
    configurableCombinations,
  } = productValues;

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

  return (skuItemCode) ? (
    <>
      <div className="magv2-header" ref={header}>
        <PdpHeader
          title={title}
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
          <PdpInfo
            title={title}
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
          <PdpCart
            skuCode={skuItemCode}
            configurableCombinations={configurableCombinations}
            productInfo={productInfo}
            pdpRefresh={pdpRefresh}
          />
        </div>
      </div>
    </>
  ) : emptyRes;
};

export default PdpLayout;
