import React, { createRef } from 'react';
import {
  updateCart,
  getPostData,
  triggerAddToCart,
} from '../../../../utilities/pdp_layout';
import CartUnavailability from '../cart-unavailability';
import QuantityDropdown from '../quantity-dropdown';

class SimpleProductForm extends React.Component {
  constructor(props) {
    super(props);
    this.button = createRef();
  }

  componentDidMount() {
    window.addEventListener('load', () => {
      this.button.current.setAttribute('data-top-offset', this.button.current.offsetTop);

      this.addToBagButtonClass(this.button.current.offsetTop);
    });

    window.addEventListener('scroll', () => {
      const buttonOffset = this.button.current.getAttribute('data-top-offset');

      if (buttonOffset === null) {
        return;
      }

      this.addToBagButtonClass(buttonOffset);
    });
  }

  addToBagButtonClass = (buttonOffset) => {
    const buttonHeight = this.button.current.offsetHeight;
    const windowHeight = window.innerHeight;

    if ((window.pageYOffset + windowHeight) >= (parseInt(buttonOffset, 10) + buttonHeight)) {
      this.button.current.classList.remove('fix-bag-button');
    } else {
      this.button.current.classList.add('fix-bag-button');
    }
  }

  addToCart = (e, id) => {
    e.preventDefault();
    // Adding add to cart loading.
    const addToCartBtn = document.getElementById(id);
    addToCartBtn.classList.toggle('magv2-add-to-basket-loader');

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
        triggerAddToCart(response, productData, productInfo, skuCode, addToCartBtn);
      },
    )
      .catch((error) => {
        console.log(error.response);
      });
  }

  render() {
    const { skuCode, productInfo } = this.props;
    const { checkoutFeatureStatus } = drupalSettings;
    const variantSelected = skuCode;

    return (
      <form action="#" className="sku-base-form" method="post" id="pdp-add-to-cart-form" parentsku={skuCode} variantselected={variantSelected}>
        <div id="add-to-cart-error" className="error" />
        <div id="product-quantity-dropdown" className="magv2-qty-wrapper">
          <QuantityDropdown
            variantSelected={variantSelected}
            productInfo={productInfo}
            skuCode={skuCode}
          />
        </div>
        {(checkoutFeatureStatus === 'enabled') ? (
          <>
            <div id="add-to-cart-error" className="error" />
            <div className="magv2-add-to-basket-container" ref={this.button}>
              <button
                className="magv2-button"
                id="add-to-cart-main"
                type="submit"
                onClick={(e) => this.addToCart(e, 'add-to-cart-main')}
              >
                {Drupal.t('Add To Bag')}
              </button>
            </div>
          </>
        ) : <CartUnavailability /> }
      </form>
    );
  }
}

export default SimpleProductForm;
