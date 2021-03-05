import React from 'react';
import axios from 'axios';
import PdpInfo from '../pdp-info';
import PdpGallery from '../pdp-gallery';
import PdpProductLabels from '../pdp-product-labels';
import PdpPopupContainer from '../utilities/pdp-popup-container';
import PdpPopupWrapper from '../utilities/pdp-popup-wrapper';
import PdpCart from '../pdp-cart';
import PdpPromotionLabel from '../pdp-promotion-label';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import isAuraEnabled from '../../../../../js/utilities/helper';
import AuraPDP from '../../../../../alshaya_aura_react/js/components/aura-pdp';

class CrossellPopupContent extends React.Component {
  constructor(props) {
    super(props);
    const { relatedSku } = this.props;
    const cartData = Drupal.alshayaSpc.getCartData();
    this.state = {
      relatedProductData: null,
      variantSelected: relatedSku,
      cartDataValue: cartData,
      skuMainCode: relatedSku,
    };
  }

  getRelatedProductsInfo = (relatedProductData, url, relatedSku) => {
    // If related products is already processed.
    if (relatedProductData === null) {
      // Show loader.
      showFullScreenLoader();
      axios.get(url).then((response) => {
        if (response.data.length !== 0) {
          const configurable = response.data.configurableCombinations;
          this.setState({
            relatedProductData: response.data,
            variantSelected: configurable ? configurable[relatedSku].firstChild : relatedSku,
          }, () => {
            document.querySelector('body').classList.add('overlay-crossel');
          });

          // Remove loader.
          removeFullScreenLoader();
        }
      });
    }
  }

  pdpRelatedRefresh = (variantSelected, parentSkuSelected) => {
    this.setState({
      variantSelected,
      skuMainCode: parentSkuSelected,
    });
  }

  pdpRelatedLabelRefresh = (cartDataVal) => {
    this.setState({
      cartDataValue: cartDataVal,
    });
  }

  render() {
    const { closeModal, relatedSku } = this.props;

    const url = Drupal.url(`rest/v2/product/${btoa(relatedSku)}?pdp=magazinev2`);
    const {
      relatedProductData, variantSelected, skuMainCode, cartDataValue,
    } = this.state;
    this.getRelatedProductsInfo(relatedProductData, url, relatedSku);
    let title = '';
    let pdpProductPrice = '';
    let finalPrice = '';
    let pdpGallery = '';
    let brandLogo = '';
    let brandLogoAlt = '';
    let brandLogoTitle = '';
    let labels = '';
    let stockStatus = '';
    let configurableCombinations = '';
    const relatedProductInfo = {};
    let stockQty = '';
    let firstChild = '';
    let promotions = '';
    if (relatedProductData) {
      title = relatedProductData.title;
      pdpProductPrice = parseInt(relatedProductData.original_price, 10);
      finalPrice = parseInt(relatedProductData.final_price, 10);
      pdpGallery = relatedProductData.media[0].media;
      labels = relatedProductData.labels[0].labels;
      stockStatus = relatedProductData.in_stock;
      stockQty = relatedProductData.stock;
      firstChild = relatedSku;
      configurableCombinations = relatedProductData.configurableCombinations;
      relatedProductInfo[relatedSku] = relatedProductData;
      promotions = relatedProductData.promotionsRaw;
      if (relatedProductData.brand_logo !== undefined) {
        brandLogo = relatedProductData.brand_logo.image;
        brandLogoAlt = relatedProductData.brand_logo.alt;
        brandLogoTitle = relatedProductData.brand_logo.title;
      }

      // For configurable products.
      if (configurableCombinations && variantSelected) {
        stockQty = relatedProductData.variants[variantSelected].stock;
        firstChild = configurableCombinations[relatedSku].firstChild;
        pdpGallery = relatedProductData.variants[variantSelected].media[0].media;
      }
    }

    const outOfStock = (
      <span className="out-of-stock">{Drupal.t('Out of Stock')}</span>
    );

    return (relatedProductData) ? (
      <PdpPopupContainer className="magv2-crossell-popup-container">
        <PdpPopupWrapper className="magv2-crossell-popup-wrapper">
          <div className="magv2-crossell-popup-header-wrapper">
            <a className="close" onClick={() => closeModal()}>
              &times;
            </a>
            <label>{Drupal.t('Quick View')}</label>
          </div>
          <div className="magv2-crossell-popup-content-wrapper">
            <PdpGallery
              skuCode={relatedSku}
              pdpGallery={pdpGallery}
              showFullVersion={false}
              context="related"
              miniFullScreenGallery={false}
              animateMobileGallery={false}
            >
              <PdpProductLabels skuCode={relatedSku} variantSelected={relatedSku} labels={labels} context="related" />
            </PdpGallery>
            <PdpInfo
              title={title}
              finalPrice={parseFloat(finalPrice)
                .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
              pdpProductPrice={parseFloat(pdpProductPrice)
                .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
              brandLogo={brandLogo}
              brandLogoAlt={brandLogoAlt}
              brandLogoTitle={brandLogoTitle}
              animateTitlePrice={false}
            />
            <div className="promotions promotions-full-view-mode">
              <PdpPromotionLabel
                skuItemCode={relatedSku}
                variantSelected={variantSelected}
                skuMainCode={skuMainCode}
                cartDataValue={cartDataValue}
                promotions={promotions}
              />
            </div>
            {isAuraEnabled()
              ? <AuraPDP mode="related" />
              : null}
            {stockStatus ? (
              <PdpCart
                skuCode={relatedSku}
                configurableCombinations={configurableCombinations}
                productInfo={relatedProductInfo}
                stockQty={stockQty}
                firstChild={firstChild}
                context="related"
                pdpRefresh={this.pdpRelatedRefresh}
                pdpLabelRefresh={this.pdpRelatedLabelRefresh}
                closeModal={closeModal}
              />
            ) : outOfStock}
            {(relatedProductData.link)
              ? (
                <div className="magv2-product-redirect-link">
                  <a href={relatedProductData.link}>
                    {Drupal.t('Go to product page')}
                  </a>
                </div>
              )
              : null }
          </div>
        </PdpPopupWrapper>
      </PdpPopupContainer>
    ) : null;
  }
}

export default CrossellPopupContent;
