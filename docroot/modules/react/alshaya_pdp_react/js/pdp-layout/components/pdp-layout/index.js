import React, {
  useRef, useEffect, useState, useCallback,
} from 'react';
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
import PpdPanel from '../pdp-popup-panel';
import PdpFreeGift from '../pdp-free-gift';
import magv2Sticky from '../../../../../js/utilities/magv2StickySidebar';

const PdpLayout = () => {
  const [variant, setVariant] = useState(null);
  const [panelContent, setPanelContent] = useState([]);
  const { productInfo } = drupalSettings;
  let skuItemCode = '';

  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }
  const [skuMainCode, setSkuMainCode] = useState(skuItemCode);

  const isMobile = window.innerWidth < 768;
  const isTouchDevice = window.innerWidth < 1025;

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
    labels,
    stockQty,
    firstChild,
    promotions,
    freeGiftImage,
    freeGiftTitle,
    freeGiftPromoCode,
  } = productValues;

  const emptyRes = (
    <div>Product data not available</div>
  );

  const outOfStock = (
    <span className="out-of-stock">{Drupal.t('Out of Stock')}</span>
  );

  const header = useRef();
  const mainContainer = useRef();
  const galleryContainer = useRef();
  const sidebarContainer = useRef();
  const crosssellContainer = useRef();
  const addToBagContainer = useRef();
  let content;

  const getChildRef = (ref) => {
    content = ref;
  };

  // Sticky Sidebar
  const sidebarSticky = () => {
    const sidebarWrapper = sidebarContainer.current;
    const mainWrapper = mainContainer.current;
    const crosssellWrapper = crosssellContainer.current;
    const galleryWrapper = galleryContainer.current;

    if (!isMobile) {
      magv2Sticky(sidebarWrapper, galleryWrapper, crosssellWrapper, mainWrapper);
    }
  };

  const showStickyHeader = () => {
    window.addEventListener('scroll', () => {
      const rect = addToBagContainer.current.getBoundingClientRect();

      if ((content !== null) && (content !== undefined)) {
        // Check addToBagContainer is not in viewport & 20 is the margin which we are excluding.
        if (rect.bottom < 20) {
          header.current.classList.remove('magv2-pdp-non-sticky-header');
          header.current.classList.add('magv2-pdp-sticky-header');
          header.current.classList.add('fadeInUp');
          header.current.classList.remove('fadeOutVertical');
        } else if (isMobile && window.pageYOffset <= header.current.offsetHeight) {
          header.current.classList.remove('magv2-pdp-non-sticky-header');
        } else {
          header.current.classList.remove('magv2-pdp-sticky-header');
          header.current.classList.add('magv2-pdp-non-sticky-header');
          header.current.classList.add('fadeOutVertical');
          header.current.classList.remove('fadeInUp');
        }
      }
    });
  };

  useEffect(() => {
    sidebarSticky();
    showStickyHeader();
  },
  []);

  const getPanelData = useCallback((data) => {
    setPanelContent([...panelContent, data]);
  }, [panelContent]);

  const removePanelData = useCallback(() => {
    if (panelContent !== undefined) {
      const panelData = [...panelContent];
      panelData.splice(-1, 1);
      setPanelContent(panelData);
    }
  }, [panelContent]);

  return (skuItemCode) ? (
    <>
      <div className={`magv2-header ${(isMobile ? 'fadeInUp' : '')}`} style={{ animationDelay: '0.3s' }} ref={header}>
        <PdpHeader
          title={title}
          finalPrice={parseFloat(finalPrice)
            .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
          pdpProductPrice={parseFloat(priceRaw)
            .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
          brandLogo={brandLogo}
          brandLogoAlt={brandLogoAlt}
          brandLogoTitle={brandLogoTitle}
          skuCode={skuItemCode}
          configurableCombinations={configurableCombinations}
          productInfo={productInfo}
          pdpLabelRefresh={pdpLabelRefresh}
          context="main"
        />
      </div>
      <div className="magv2-main" ref={mainContainer}>
        <div className="magv2-content" id="pdp-gallery-refresh" ref={galleryContainer}>
          <PdpGallery
            skuCode={skuItemCode}
            pdpGallery={pdpGallery}
            showFullVersion={!isMobile}
            context="main"
            miniFullScreenGallery={isTouchDevice}
            animateMobileGallery
          >
            <PdpProductLabels skuCode={skuItemCode} variantSelected={variant} labels={labels} context="main" />
          </PdpGallery>
        </div>
        <div className="magv2-sidebar" ref={sidebarContainer}>
          <PdpInfo
            title={title}
            finalPrice={parseFloat(finalPrice)
              .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
            pdpProductPrice={parseFloat(priceRaw)
              .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
            brandLogo={brandLogo}
            brandLogoAlt={brandLogoAlt}
            brandLogoTitle={brandLogoTitle}
            animateTitlePrice
          />
          <div className="promotions promotions-full-view-mode">
            <PdpPromotionLabel
              skuItemCode={skuItemCode}
              variantSelected={variant}
              skuMainCode={skuMainCode}
              cartDataValue={cartDataValue}
              promotions={promotions}
            />
          </div>
          {freeGiftTitle ? (
            <PdpFreeGift
              freeGiftImage={freeGiftImage}
              freeGiftTitle={freeGiftTitle}
              freeGiftPromoCode={freeGiftPromoCode}
            />
          ) : null}
          <div className="addtobag-button-wrapper" ref={addToBagContainer}>
            {stockStatus ? (
              <PdpCart
                skuCode={skuItemCode}
                configurableCombinations={configurableCombinations}
                productInfo={productInfo}
                pdpRefresh={pdpRefresh}
                pdpLabelRefresh={pdpLabelRefresh}
                childRef={(ref) => (getChildRef(ref))}
                stockQty={stockQty}
                firstChild={firstChild}
                context="main"
                animatePdpCart
              />
            ) : outOfStock}
          </div>
          <PdpDescription
            skuCode={skuMainCode}
            pdpDescription={description}
            pdpShortDesc={shortDesc}
            title={title}
            pdpProductPrice={parseFloat(priceRaw)
              .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
            finalPrice={parseFloat(finalPrice)
              .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
            getPanelData={getPanelData}
            removePanelData={removePanelData}
          />
          <PdpStandardDelivery />
          {stockStatus ? (
            <PdpClickCollect />
          ) : null}
          <PdpSharePanel />
        </div>
      </div>
      {relatedProducts ? (
        <div className="magv2-pdp-crossell-upsell-wrapper" ref={crosssellContainer}>
          {Object.keys(relatedProducts).map((type) => (
            <PdpRelatedProducts
              key={relatedProducts[type]}
              keyId={relatedProducts[type]}
              type={relatedProducts[type]}
              skuItemCode={skuItemCode}
              getPanelData={getPanelData}
              removePanelData={removePanelData}
            />
          ))}
        </div>
      ) : null}
      <PpdPanel panelContent={panelContent} skuItemCode={skuItemCode} />
    </>
  ) : emptyRes;
};

export default PdpLayout;
