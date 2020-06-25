import React from 'react';
import {
  updateCart,
  getPostData,
  triggerAddToCart,
} from '../../../../utilities/pdp_layout';
import CartUnavailability from '../cart-unavailability';
import QuantityDropdown from '../quantity-dropdown';

class SimpleProductForm extends React.Component {
  addToCart = (e) => {
    e.preventDefault();
    const { skuCode, productInfo } = this.props;
    const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');

    const getPost = getPostData(skuCode, variantSelected);

    const postData = getPost[0];
    const productData = getPost[1];

    productData.productName = productInfo[skuCode].cart_title;
    productData.image = productInfo[skuCode].cart_image;

    const cartEndpoint = drupalSettings.cart_update_endpoint;

    updateCart(cartEndpoint, postData).then(
      (response) => {
        triggerAddToCart(response, productData, productInfo, skuCode);
      },
    )
      .catch((error) => {
        console.log(error.response);
      });
  }

  openModal = () => {
    document.querySelector('body').classList.add('select-overlay');
  };

  render() {
    const { skuCode, productInfo } = this.props;
    const { checkoutFeatureStatus } = productInfo[skuCode];
    const variantSelected = skuCode;

    const cartUnavailability = (
      <CartUnavailability />
    );

    return (
      <>
        <form action="#" className="sku-base-form" method="post" id="pdp-add-to-cart-form" parentsku={skuCode} variantselected={variantSelected}>
          <div className="magv2-size-btn-wrapper" onClick={() => this.openModal()}>{Drupal.t('Select size')}</div>
          <div id="product-quantity-dropdown" className="magv2-qty-wrapper">
            <QuantityDropdown
              variantSelected={variantSelected}
              productInfo={productInfo}
              skuCode={skuCode}
            />
          </div>
          {(checkoutFeatureStatus === 'enabled') ? (
            <button
              className="magv2-button"
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
