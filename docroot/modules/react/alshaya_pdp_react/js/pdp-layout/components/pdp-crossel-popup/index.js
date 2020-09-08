import React from 'react';
import axios from 'axios';
import PdpInfo from '../pdp-info';
import PdpGallery from '../pdp-gallery';
import PdpProductLabels from '../pdp-product-labels';
import PdpPopupContainer from '../utilities/pdp-popup-container';
import PdpPopupWrapper from '../utilities/pdp-popup-wrapper';
import PdpCart from '../pdp-cart';

class CrossellPopupContent extends React.Component {
  constructor(props) {
    super(props);
    const { relatedSku } = this.props;
    this.state = {
      relatedProductData: null,
      variantSelected: relatedSku,
    };
  }

  getRelatedProductsInfo = (relatedProductData, url, relatedSku) => {
    // If related products is already processed.
    if (relatedProductData === null) {
      axios.get(url).then((response) => {
        if (response.data.length !== 0) {
          const configurable = response.data.configurableCombinations;
          this.setState({
            relatedProductData: response.data,
            variantSelected: configurable ? configurable[relatedSku].firstChild : relatedSku,
          });
        }
      });
    }
  }


  render() {
    const { closeModal, relatedSku } = this.props;

    const url = Drupal.url(`rest/v1/product/${relatedSku}`);
    const { relatedProductData, variantSelected } = this.state;
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
    let relatedProductInfo = '';
    let stockQty = '';
    let firstChild = '';
    if (relatedProductData) {
      title = relatedProductData.title;
      pdpProductPrice = parseInt(relatedProductData.original_price, 10);
      finalPrice = parseInt(relatedProductData.final_price, 10);
      pdpGallery = relatedProductData.media[0].media;
      labels = relatedProductData.product_labels[relatedSku];
      stockStatus = relatedProductData.stockStatus;
      stockQty = relatedProductData.stock;
      firstChild = relatedSku;
      configurableCombinations = relatedProductData.configurableCombinations;
      relatedProductInfo = relatedProductData.relatedProductInfo;
      if (relatedProductData.brand_logo !== undefined) {
        brandLogo = relatedProductData.brand_logo;
        brandLogoAlt = relatedProductData.brand_alt;
        brandLogoTitle = relatedProductData.brand_title;
      }

      // For configurable products.
      if (configurableCombinations && variantSelected) {
        stockQty = relatedProductData.variants[variantSelected].stock;
        firstChild = configurableCombinations[relatedSku].firstChild;
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
              miniFullScreenGallery
            >
              <PdpProductLabels skuCode={relatedSku} variantSelected={relatedSku} labels={labels} />
            </PdpGallery>
            <PdpInfo
              title={title}
              finalPrice={finalPrice}
              pdpProductPrice={pdpProductPrice}
              brandLogo={brandLogo}
              brandLogoAlt={brandLogoAlt}
              brandLogoTitle={brandLogoTitle}
            />
            <div className="promotions promotions-full-view-mode">
              <p>
                <a href="buy-1-get-1-free-dee/">Buy 1 get 1 free - Dee</a>
              </p>
            </div>
            {stockStatus ? (
              <PdpCart
                skuCode={relatedSku}
                configurableCombinations={configurableCombinations}
                productInfo={relatedProductInfo}
                stockQty={stockQty}
                firstChild={firstChild}
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
