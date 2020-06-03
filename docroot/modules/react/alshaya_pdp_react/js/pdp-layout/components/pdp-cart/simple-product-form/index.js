import React from 'react';
import ReactDOM from 'react-dom';
import {
  updateCart,
  getPostData,
  triggerAddToCart,
} from '../../../../utilities/cart/cart_utils';
import CartUnavailability from '../cart-unavailability';
import CartNotification from '../cart-notification';

class SimpleProductForm extends React.Component {
  addToCart = (e) => {
    e.preventDefault();
    const { skuCode, productInfo } = this.props;
    const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');

    const getPost = getPostData(skuCode, variantSelected);

    const postData = getPost[0];
    const productData = getPost[1];

    productData.productName = productInfo[skuCode].cart_title;
    productData.image = productInfo[skuCode].cart_update_endpoint;

    const { cartEndpoint } = drupalSettings.cart;

    updateCart(cartEndpoint, postData).then(
      (response) => {
        triggerAddToCart(response, productData, productInfo, skuCode);
        ReactDOM.render(
          <CartNotification
            productInfo={productInfo}
            productData={productData}
          />,
          document.getElementById('cart_notification'),
        );
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
      <CartUnavailability />
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
