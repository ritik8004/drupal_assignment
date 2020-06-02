import React from 'react';
import ReactDOM from 'react-dom';
import {
  clearCartData,
  getCartData,
  storeProductData,
  updateCart,
} from '../../../../utilities/cart/cart_utils';
import CartNotification from '../cart-notification';

class SimpleProductForm extends React.Component {
  addToCart = (e) => {
    e.preventDefault();
    const { skuCode, productInfo } = this.props;

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
    };

    const productData = {
      quantity: qty,
      parentSku: skuCode,
      sku: variantSelected,
      variant: variantSelected,
    };

    productData.productName = productInfo[skuCode].cart_title;
    productData.image = productInfo[skuCode].cart_image;

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

          const productUrl = productInfo[skuCode].url;
          const price = productInfo[skuCode].priceRaw;
          const promotions = productInfo[skuCode].promotionsRaw;
          const productDataSKU = productInfo[skuCode].sku;
          const parentSKU = productInfo[skuCode].sku;
          const { maxSaleQty } = productInfo[skuCode];
          const maxSaleQtyParent = productInfo[skuCode].max_sale_qty_parent;
          const gtmAttributes = productInfo[skuCode].gtm_attributes;

          storeProductData({
            sku: productDataSKU,
            parentSKU,
            title: productData.product_name,
            url: productUrl,
            image: productData.image,
            price,
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
    const { skuCode, productInfo } = this.props;
    const { cartMaxQty, checkoutFeatureStatus } = productInfo[skuCode];
    const { stockQty } = productInfo[skuCode];
    const variantSelected = skuCode;

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

export default SimpleProductForm;
