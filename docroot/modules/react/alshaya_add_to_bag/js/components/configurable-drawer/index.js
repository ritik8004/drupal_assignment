import React from 'react';
import Slider from 'react-slick';
import MagicSliderDots from 'react-magic-slider-dots';
import ProductDrawer from '../product-drawer';
import ConfigurableForm from '../configurable-form';
import Price from '../../../../js/utilities/components/price';
import Promotions from '../promotions';
import { getVatText } from '../../../../js/utilities/price';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import Lozenges
  from '../../../../alshaya_algolia_react/js/common/components/lozenges';
import getStringMessage from '../../../../js/utilities/strings';
import { isWishlistPage, getFirstChildWithWishlistData } from '../../../../js/utilities/wishlistHelper';
import LoginMessage from '../../../../js/utilities/components/login-message';
import { isUserAuthenticated } from '../../../../js/utilities/helper';

class ConfigurableProductDrawer extends React.Component {
  constructor(props) {
    super(props);
    const { sku, productData, extraInfo } = props;

    // Check for the firstChild is set for the default variant otherwise
    // set the first variant in the list as the default variant.
    let firstChild = (productData.configurable_combinations.firstChild !== 'undefined')
      ? productData.configurable_combinations.firstChild
      : productData.variants[0].sku;

    // If the current page is withlist page, we will check if user has
    // choosen a specific variant to store in wishlist. If we found one
    // we open the same child variant in the drawer.
    if (isWishlistPage(extraInfo)) {
      firstChild = getFirstChildWithWishlistData(sku, productData) || firstChild;
    }

    this.state = {
      selectedVariant: firstChild,
    };
  }

  /**
   * Sets the currently selected variant's sku to the state.
   *
   * @param {string} variant
   *   The SKU value.
   */
  setSelectedVariant = (variant) => this.setState({ selectedVariant: variant });

  /**
   * Take action when add to cart is performed.
   *
   * @param {boolean} status
   *   If add to cart failed then send false, else true.
   */
  onItemAddedToCart = (status) => {
    if (status) {
      const { onDrawerClose } = this.props;
      onDrawerClose();
    }
  }

  render() {
    const {
      status,
      onDrawerClose,
      sku,
      productData,
      url,
      extraInfo,
      wishListButtonRef,
    } = this.props;
    const { selectedVariant } = this.state;

    // Early return.
    if (status === 'closed') {
      return (null);
    }

    // The variant data for the variant which is currently on display.
    let selectedVariantData = null;

    // Find the variant data from the props data.
    for (let i = 0; i < productData.variants.length; i++) {
      if (productData.variants[i].sku.toString() === selectedVariant.toString()) {
        selectedVariantData = productData.variants[i];
        break;
      }
    }

    // Gallery images.
    const { images } = selectedVariantData.media;
    const { original_price: originalPrice, final_price: finalPrice } = selectedVariantData;
    const vatText = getVatText();
    const parentSku = selectedVariantData.parent_sku;

    return (
      <ProductDrawer
        status={status}
        direction="right"
        onDrawerClose={onDrawerClose}
      >
        <div className="configurable-product-form-wrapper">
          <ConditionalView condition={isWishlistPage(extraInfo) && !isUserAuthenticated()}>
            <LoginMessage />
          </ConditionalView>
          <div className="gallery-wrapper">
            <Slider
              dots
              infinite={false}
              arrows
              appendDots={(dots) => (
                <MagicSliderDots
                  dots={dots}
                  numDotsToShow={5}
                  dotWidth={25}
                />
              )}
            >
              {images.map((image) => (
                <img src={image.url} key={image.url} loading="lazy" />
              ))}
            </Slider>
            <Lozenges
              labels={selectedVariantData.product_labels}
              sku={sku}
            />
          </div>
          <div className="product-details-wrapper">
            <div className="product-title">{productData.title}</div>
            <Price price={originalPrice} finalPrice={finalPrice} />
            <ConditionalView condition={vatText !== ''}>
              <div className="vat-text">{vatText}</div>
            </ConditionalView>
            <ConditionalView condition={parentSku !== null}>
              <div className="content--item-code">
                <span className="field__label">{`${getStringMessage('item_code')}:`}</span>
                <span className="field__value">{parentSku}</span>
              </div>
            </ConditionalView>
            <Promotions promotions={productData.promotions} />
            <ConfigurableForm
              sku={sku}
              productData={productData}
              setSelectedVariant={this.setSelectedVariant}
              selectedVariant={selectedVariant}
              parentSku={parentSku}
              onItemAddedToCart={this.onItemAddedToCart}
              extraInfo={extraInfo}
              wishListButtonRef={wishListButtonRef}
            />
            <div className="pdp-link">
              <a href={url}>{getStringMessage('view_full_product_details')}</a>
            </div>
          </div>
        </div>
      </ProductDrawer>
    );
  }
}

export default ConfigurableProductDrawer;
