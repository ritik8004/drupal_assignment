import React, { createRef } from 'react';
import { addToCartSimple } from '../../../../utilities/pdp_layout';
import CartUnavailability from '../cart-unavailability';
import QuantityDropdown from '../quantity-dropdown';


class SimpleProductForm extends React.Component {
  constructor(props) {
    super(props);
    this.button = createRef();
  }

  componentDidMount() {
    const { refCartButton } = this.props;

    // Condition to check if add to cart
    // button is available.
    if (document.getElementById('add-to-cart-main')) {
      window.addEventListener('load', () => {
        this.button.current.setAttribute('data-top-offset', this.button.current.offsetTop);
        refCartButton(this.button);
        this.addToBagButtonClass(this.button.current.offsetTop);
      });

      window.addEventListener('scroll', () => {
        let buttonOffset = null;
        if (!(this.button.current === null)) {
          buttonOffset = this.button.current.getAttribute('data-top-offset');
        }

        if (buttonOffset === null) {
          return;
        }

        this.addToBagButtonClass(buttonOffset);
      });
    }
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

  render() {
    const {
      skuCode, productInfo, pdpLabelRefresh, stockQty, context, closeModal,
    } = this.props;
    const { checkoutFeatureStatus } = drupalSettings;
    const variantSelected = skuCode;
    const id = `add-to-cart-${context}`;

    return (
      <form action="#" className="sku-base-form" method="post" id={`pdp-add-to-cart-form-${context}`} parentsku={skuCode} variantselected={variantSelected}>
        <div id="add-to-cart-error" className="error" />
        <div id="product-quantity-dropdown" className="magv2-qty-wrapper">
          <QuantityDropdown
            variantSelected={variantSelected}
            productInfo={productInfo}
            skuCode={skuCode}
            stockQty={stockQty}
          />
        </div>
        {(checkoutFeatureStatus === 'enabled') ? (
          <>
            <div id="add-to-cart-error" className="error" />
            <div className="magv2-add-to-basket-container" ref={this.button}>
              <button
                className="magv2-button"
                id={id}
                type="submit"
                onClick={(e) => addToCartSimple(
                  e,
                  id,
                  skuCode,
                  productInfo,
                  pdpLabelRefresh,
                  context,
                  closeModal,
                )}
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
