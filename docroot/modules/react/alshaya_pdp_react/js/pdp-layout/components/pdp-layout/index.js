import React, { useRef, useEffect, useState } from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';
import PdpInfo from '../pdp-info';
import PdpCart from '../pdp-cart';
import PdpHeader from '../pdp-header';
import { getProductValues } from '../../../utilities/pdp_layout';
import PdpStandardDelivery from '../pdp-standard-delivery';
import PdpSharePanel from '../pdp-share-panel';
import PdpClickCollect from '../pdp-click-and-collect';
import PdpRelatedProducts from '../pdp-related-products';
import PdpProductLabels from '../pdp-product-labels';
import PdpPromotionLabel from '../pdp-promotion-label';

const PdpLayout = () => {
  const [variant, setVariant] = useState(null);
  const { productInfo } = drupalSettings;
  let skuItemCode = '';

  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }
  const [skuMainCode, setSkuMainCode] = useState(skuItemCode);

  const pdpRefresh = (variantSelected, parentSkuSelected) => {
    setVariant(variantSelected);
    setSkuMainCode(parentSkuSelected);
  };

  // Refresh dynamic promo label based on cart data.
  const cartData = Drupal.alshayaSpc.getCartData();
  const [cartDataValue, setCartData] = useState(cartData);

  const pdpLabelRefresh = (cartDataVal) => {
    setCartData(cartDataVal);
  };

  // Get product data based on sku.
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
    relatedProducts,
    stockStatus,
  } = productValues;

  const emptyRes = (
    <div>Product data not available</div>
  );

  const outOfStock = (
    <span className="out-of-stock">{Drupal.t('Out of Stock')}</span>
  );

  const header = useRef();
  let content;

  const showStickyHeader = () => {
    window.onscroll = function () {
      if ((content !== null) && (content !== undefined)) {
        if (window.pageYOffset >= content.offsetTop + content.offsetHeight) {
          header.current.classList.add('magv2-pdp-sticky-header');
        } else {
          header.current.classList.remove('magv2-pdp-sticky-header');
        }
      }
    };
  };
  useEffect(() => {
    showStickyHeader();
  },
  []);

  return (skuItemCode) ? (
    <>
      <div className="magv2-header fadeInUp" style={{ animationDelay: '0.3s' }} ref={header}>
        <PdpHeader
          title={title}
          finalPrice={finalPrice}
          pdpProductPrice={priceRaw}
          brandLogo={brandLogo}
          brandLogoAlt={brandLogoAlt}
          brandLogoTitle={brandLogoTitle}
          skuCode={skuItemCode}
          configurableCombinations={configurableCombinations}
          productInfo={productInfo}
        />
      </div>
      <div className="magv2-main">
        <div className="magv2-content" id="pdp-gallery-refresh">
          <PdpProductLabels skuCode={skuItemCode} variantSelected={variant} />
          <PdpGallery skuCode={skuItemCode} pdpGallery={pdpGallery} />
        </div>
        <div className="magv2-sidebar">
          <PdpInfo
            title={title}
            finalPrice={finalPrice}
            pdpProductPrice={priceRaw}
            brandLogo={brandLogo}
            brandLogoAlt={brandLogoAlt}
            brandLogoTitle={brandLogoTitle}
          />
          <div className="promotions promotions-full-view-mode">
            <PdpPromotionLabel
              skuItemCode={skuItemCode}
              variantSelected={variant}
              skuMainCode={skuMainCode}
              cartDataValue={cartDataValue}
            />
          </div>
          {stockStatus ? (
            <PdpCart
              skuCode={skuItemCode}
              configurableCombinations={configurableCombinations}
              productInfo={productInfo}
              pdpRefresh={pdpRefresh}
              pdpLabelRefresh={pdpLabelRefresh}
              childRef={(ref) => { content = ref; }}
            />
          ) : outOfStock}
          <PdpDescription
            skuCode={skuItemCode}
            pdpDescription={description}
            pdpShortDesc={shortDesc}
            title={title}
            pdpProductPrice={priceRaw}
            finalPrice={finalPrice}
          />
          <PdpStandardDelivery />
          {stockStatus ? (
            <PdpClickCollect />
          ) : null}
          <PdpSharePanel />
        </div>
      </div>
      {relatedProducts ? (
        <div className="magv2-pdp-crossell-upsell-wrapper">
          {Object.keys(relatedProducts).map((type) => (
            <PdpRelatedProducts type={relatedProducts[type]} skuItemCode={skuItemCode} />
          ))}
        </div>
      ) : null}
    </>
  ) : emptyRes;
};

export default PdpLayout;
