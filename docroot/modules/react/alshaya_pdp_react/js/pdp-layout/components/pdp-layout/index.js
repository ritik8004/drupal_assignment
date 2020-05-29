import React,{ useRef, useEffect } from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';
import PdpCart from '../pdp-cart';
import PdpDetail from '../pdp-detail';
import PdpHeader from '../pdp-header';

const PdpLayout = () => {
  let skuItemCode = null;
  const { productInfo } = drupalSettings;
  const { configurableCombinations } = drupalSettings;
  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }
  const shortDesc = skuItemCode ? productInfo[skuItemCode].shortDesc : [];
  const description = skuItemCode ? productInfo[skuItemCode].description : [];
  const title = skuItemCode ? productInfo[skuItemCode].title : null;
  const priceRaw = skuItemCode ? productInfo[skuItemCode].priceRaw : null;
  const finalPrice = skuItemCode ? productInfo[skuItemCode].finalPrice : null;
  const pdpGallery = skuItemCode ? productInfo[skuItemCode].rawGallery : [];
  const brandLogo = skuItemCode ? productInfo[skuItemCode].brandLogo.logo : null;
  const brandLogoAlt = skuItemCode ? productInfo[skuItemCode].brandLogo.alt : null;
  const brandLogoTitle = skuItemCode ? productInfo[skuItemCode].brandLogo.title : null;

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
      }
      else {
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
        <ConditionalView condition={window.innerWidth < 768}>
          <PdpHeader
            title={title.label}
            finalPrice={finalPrice}
            pdpProductPrice={priceRaw}
          />
        </ConditionalView>
        <ConditionalView condition={window.innerWidth > 767}>
          {/* Render desktop sticky header component */}
        </ConditionalView>
      </div>
      <div className="magv2-main">
        <div className="magv2-content">
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
          <PdpInfo
            skuCode={skuItemCode}
            title={title}
            pdpProductPrice={priceRaw}
            finalPrice={finalPrice}
          />
          <PdpCart skuCode={skuItemCode} configurableCombinations={configurableCombinations} />
        </div>
      </div>
    </>
  ) : emptyRes;
};

export default PdpLayout;
