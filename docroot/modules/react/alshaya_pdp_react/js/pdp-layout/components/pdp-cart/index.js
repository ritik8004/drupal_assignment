import React from 'react';
import ReactDOM from 'react-dom';
import CartSelectOption from './cart-select-option';
import {
  clearCartData,
  getCartData,
  storeProductData,
  updateCart,
} from '../../../utilities/cart/cart_utils';
import CartNotification from './cart-notification';

class PdpCart extends React.Component {
  addToCart = (e) => {
    e.preventDefault();
    const { configurableCombinations, skuCode, productInfo } = this.props;
    const options = [];
    let qty = 1;
    if (configurableCombinations) {
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
    }
    const cartAction = 'add item';
    const cartData = getCartData();
    const cartId = (cartData) ? cartData.cart_id : null;
    const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');
    qty = document.getElementById('qty') ? document.getElementById('qty').value : 1;

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

    productData.productName = configurableCombinations
      ? productInfo[skuCode].variants[variantSelected].cart_title
      : productInfo[skuCode].cart_title;
    productData.image = configurableCombinations
      ? productInfo[skuCode].variants[variantSelected].cart_image
      : productInfo[skuCode].cart_image;

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

          let configurables = [];
          let productUrl = productInfo[skuCode].url;
          let price = productInfo[skuCode].priceRaw;
          let promotions = productInfo[skuCode].promotionsRaw;
          let productDataSKU = productInfo[skuCode].sku;
          let parentSKU = productInfo[skuCode].sku;
          let { maxSaleQty } = productInfo[skuCode];
          let maxSaleQtyParent = productInfo[skuCode].max_sale_qty_parent;
          const gtmAttributes = productInfo[skuCode].gtm_attributes;

          if (configurableCombinations) {
            const productVariantInfo = productInfo[skuCode].variants[productData.variant];
            productDataSKU = productData.variant;
            price = productVariantInfo.priceRaw;
            parentSKU = productVariantInfo.parent_sku;
            promotions = productVariantInfo.promotionsRaw;
            configurables = productVariantInfo.configurableOptions;
            maxSaleQty = productVariantInfo.maxSaleQty;
            maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

            if (productVariantInfo.url !== undefined) {
              const langcode = document.getElementsByTagName('html')[0].getAttribute('lang');
              productUrl = productVariantInfo.url[langcode];
            }
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

  render() {
    const { configurableCombinations, skuCode, productInfo } = this.props;
    const { cartMaxQty, checkoutFeatureStatus } = productInfo[skuCode];
    let { stockQty } = productInfo[skuCode];
    let variantSelected = skuCode;
    if (typeof productInfo[skuCode].variants !== 'undefined') {
      variantSelected = drupalSettings.configurableCombinations[skuCode].firstChild;
      stockQty = productInfo[skuCode].variants[variantSelected].stock.qty;
    }

    const cartUnavailability = (
      <>
        <p className="not-buyable-message">{Drupal.t('Add to bag is temporarily unavailable')}</p>
      </>
    );

    const options = [];
    for (let i = 1; i <= cartMaxQty; i++) {
      if (i <= stockQty) {
        options.push(
          <option value={i}>{i}</option>,
        );
      } else {
        options.push(
          <option value={i} disabled>{i}</option>,
        );
      }
    }


    if (configurableCombinations) {
      const { configurables } = configurableCombinations[skuCode];
      const { byAttribute } = configurableCombinations[skuCode];

      return (
        <div className="pdp-cart-form">
          <form action="#" method="post" id="pdp-add-to-cart-form" parentsku={skuCode} variantselected={variantSelected}>
            {Object.keys(configurables).map((key) => (
              <div>
                <label htmlFor={key}>{configurables[key].label}</label>
                <CartSelectOption
                  configurables={configurables[key]}
                  byAttribute={byAttribute}
                  productInfo={productInfo}
                  skuCode={skuCode}
                  configurableCombinations={configurableCombinations}
                  key={key}
                  isGroup={configurables[key].isGroup}
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
        </div>
      );
    }
    return (
      <div className="pdp-cart-form">
        <form action="#" method="post" id="pdp-add-to-cart-form" parentsku={skuCode} variantselected={variantSelected}>
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
      </div>
    );
  }
}
export default PdpCart;
