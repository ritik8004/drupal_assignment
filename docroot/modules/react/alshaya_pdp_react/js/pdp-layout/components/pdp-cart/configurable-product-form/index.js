import React from 'react';
import ReactDOM from 'react-dom';
import CartSelectOption from '../cart-select-option';
import {
  clearCartData,
  getCartData,
  storeProductData,
  updateCart,
} from '../../../../utilities/cart/cart_utils';
import CartNotification from '../cart-notification';

class ConfigurableProductForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      nextCode: null,
      nextValues: null,
    };
    this.handleLoad = this.handleLoad.bind(this);
  }

  componentDidMount() {
    window.addEventListener('load', this.handleLoad);
  }

  componentWillUnmount() {
    window.removeEventListener('load', this.handleLoad);
  }

  handleLoad() {
    const { configurableCombinations, skuCode } = this.props;
    const { combinations } = configurableCombinations[skuCode];
    const code = Object.keys(combinations)[0];
    const codeValue = Object.keys(combinations[code])[0];
    this.refreshConfigurables(code, codeValue, null);
  }

  addToCart = (e) => {
    e.preventDefault();
    const { configurableCombinations, skuCode, productInfo } = this.props;
    const options = [];
    const attributes = configurableCombinations[skuCode].configurables;
    Object.keys(attributes).forEach((key) => {
      const option = {
        option_id: attributes[key].attribute_id,
        option_value: document.getElementById(key).value,
      };

      // Skipping the psudo attributes.
      if (drupalSettings.psudo_attribute === undefined
        || drupalSettings.psudo_attribute !== option.option_id) {
        options.push(option);
      }
    });
    const cartAction = 'add item';
    const cartData = getCartData();
    const cartId = (cartData) ? cartData.cart_id : null;
    const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');
    const qty = document.getElementById('qty') ? document.getElementById('qty').value : 1;

    const postData = {
      action: cartAction,
      sku: variantSelected,
      quantity: qty,
      cart_id: cartId,
      options,
    };

    const productData = {
      quantity: qty,
      parentSku: skuCode,
      sku: variantSelected,
      variant: variantSelected,
    };

    productData.productName = productInfo[skuCode].variants[variantSelected].cart_title;
    productData.image = productInfo[skuCode].variants[variantSelected].cart_image;

    const cartEndpoint = productInfo[skuCode].cart_update_endpoint;

    updateCart(cartEndpoint, postData).then(
      (response) => {
        // If there any error we throw from middleware.
        if (response.data.error === true) {
          if (response.data.error_code === '400') {
            clearCartData();
          }
        } else if (response.data.cart_id) {
          if (response.data.response_message.status === 'success'
              && (typeof response.data.items[productData.variant] !== 'undefined'
                || typeof response.data.items[productData.parentSku] !== 'undefined')) {
            const cartItem = typeof response.data.items[productData.variant] !== 'undefined' ? response.data.items[productData.variant] : response.data.items[productData.parentSku];
            productData.totalQty = cartItem.qty;
          }

          ReactDOM.render(
            <CartNotification
              productInfo={productInfo}
              productData={productData}
            />,
            document.getElementById('cart_notification'),
          );

          let productUrl = productInfo[skuCode].url;
          const productVariantInfo = productInfo[skuCode].variants[productData.variant];
          const productDataSKU = productData.variant;
          const price = productVariantInfo.priceRaw;
          const parentSKU = productVariantInfo.parent_sku;
          const promotions = productVariantInfo.promotionsRaw;
          const configurables = productVariantInfo.configurableOptions;
          const { maxSaleQty } = productVariantInfo;
          const maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;
          const gtmAttributes = productInfo[skuCode].gtm_attributes;

          if (productVariantInfo.url !== undefined) {
            const langcode = document.getElementsByTagName('html')[0].getAttribute('lang');
            productUrl = productVariantInfo.url[langcode];
          }

          storeProductData({
            sku: productDataSKU,
            parentSKU,
            title: productData.product_name,
            url: productUrl,
            image: productData.image,
            price,
            configurables,
            promotions,
            maxSaleQty,
            maxSaleQtyParent,
            gtmAttributes,
          });

          // Triggering event to notify react component.
          const refreshMiniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data() { return response.data; }, productData } });
          document.dispatchEvent(refreshMiniCartEvent);

          const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data() { return response.data; } } });
          document.dispatchEvent(refreshCartEvent);
        }
      },
    )
      .catch((error) => {
        console.log(error.response);
      });
  }

  refreshConfigurables = (code, codeValue, variantSelected) => {
    const { configurableCombinations, skuCode } = this.props;
    const selectedValues = this.selectedValues();
    // Refresh configurables.
    let { combinations } = configurableCombinations[skuCode];

    selectedValues.forEach((key) => {
      if (key !== code) {
        combinations = combinations[key][selectedValues[key]];
      }
    });

    if (typeof combinations[code] === 'undefined') {
      return;
    }

    if (combinations[code][codeValue] === 1) {
      return;
    }

    if (combinations[code][codeValue]) {
      const nextCode = Object.keys(combinations[code][codeValue])[0];
      const nextValues = Object.keys(combinations[code][codeValue][nextCode]);
      this.setState({
        nextCode,
        nextValues,
        variant: variantSelected,
      });
      const nextVal = document.getElementById(nextCode).value;
      this.refreshConfigurables(nextCode, nextVal);
    }
  }

  selectedValues = () => {
    const { configurableCombinations, skuCode } = this.props;
    const attributes = configurableCombinations[skuCode].configurables;
    const selectedValues = [];
    Object.keys(attributes).map((id) => {
      const selectedVal = document.getElementById(id).value;
      if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
        selectedValues[id] = selectedVal;
      }
      return selectedValues;
    });
    return selectedValues;
  }


  render() {
    const { configurableCombinations, skuCode, productInfo } = this.props;
    const { cartMaxQty, checkoutFeatureStatus } = productInfo[skuCode];

    const { configurables } = configurableCombinations[skuCode];
    const { byAttribute } = configurableCombinations[skuCode];

    const { nextCode, nextValues, variant } = this.state;
    const variantSelected = variant || drupalSettings.configurableCombinations[skuCode].firstChild;
    const stockQty = productInfo[skuCode].variants[variantSelected].stock.qty;

    const cartUnavailability = (
      <p className="not-buyable-message">{Drupal.t('Add to bag is temporarily unavailable')}</p>
    );


    // Quantity component created separately.
    const options = [];
    for (let i = 1; i <= cartMaxQty; i++) {
      if (i <= stockQty) {
        options.push(
          <option key={i} value={i}>{i}</option>,
        );
      } else {
        options.push(
          <option key={i} value={i} disabled>{i}</option>,
        );
      }
    }

    return (
      <>
        <form action="#" method="post" id="pdp-add-to-cart-form" parentsku={skuCode} variantselected={variantSelected}>
          {Object.keys(configurables).map((key) => (
            <div key={key}>
              <label htmlFor={key}>{configurables[key].label}</label>
              <CartSelectOption
                configurables={configurables[key]}
                byAttribute={byAttribute}
                productInfo={productInfo}
                skuCode={skuCode}
                configurableCombinations={configurableCombinations}
                key={key}
                isGroup={configurables[key].isGroup}
                nextCode={nextCode}
                nextValues={nextValues}
                refreshConfigurables={this.refreshConfigurables}
                selectedValues={this.selectedValues}
              />
            </div>
          ))}
          <p>{Drupal.t('Quantity')}</p>
          <div id="product-quantity-dropdown">
            <select id="qty">
              {options}
            </select>
          </div>
          {(checkoutFeatureStatus === 'enabled') ? (
            <button
              type="submit"
              value="Add to basket"
              onClick={this.addToCart}
            >
              {Drupal.t('Add To Basket')}
            </button>
          ) : cartUnavailability }
        </form>
      </>
    );
  }
}

export default ConfigurableProductForm;
