import React, { createRef } from 'react';
import CartSelectOption from '../cart-select-option';
import {
  updateCart,
  getPostData,
  triggerAddToCart,
} from '../../../../utilities/pdp_layout';
import CartUnavailability from '../cart-unavailability';
import QuantityDropdown from '../quantity-dropdown';

class ConfigurableProductForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      nextCode: null,
      nextValues: null,
    };

    this.button = createRef();
  }

  componentDidMount() {
    this.handleLoad();

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

    if ((window.pageYOffset + windowHeight)
      >= (parseInt(buttonOffset, 10) + parseInt(buttonHeight, 10))) {
      this.button.current.classList.remove('fix-bag-button');
    } else {
      this.button.current.classList.add('fix-bag-button');
    }
  }

  handleLoad = () => {
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

    const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');
    const getPost = getPostData(skuCode, variantSelected);

    const postData = getPost[0];
    const productData = getPost[1];

    postData.options = options;
    productData.product_name = productInfo[skuCode].variants[variantSelected].cart_title;
    productData.image = productInfo[skuCode].variants[variantSelected].cart_image;
    const cartEndpoint = drupalSettings.cart_update_endpoint;

    updateCart(cartEndpoint, postData).then(
      (response) => {
        triggerAddToCart(response, productData, productInfo, configurableCombinations, skuCode);
      },
    )
      .catch((error) => {
        console.log(error);
      });
  }

  // To get available attribute value based on user selection.
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
      const selectedVal = document.getElementById(id).getElementsByClassName('magv2-select-list-item active');
      if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
        selectedValues[id] = selectedVal;
      }
      return selectedValues;
    });
    return selectedValues;
  }

  openModal = () => {
    document.querySelector('body').classList.add('select-overlay');
  };

  render() {
    const {
      configurableCombinations, skuCode, productInfo, pdpRefresh,
    } = this.props;
    const { checkoutFeatureStatus } = drupalSettings;

    const { configurables } = configurableCombinations[skuCode];
    const { byAttribute } = configurableCombinations[skuCode];

    const {
      nextCode, nextValues, variant,
    } = this.state;
    const variantSelected = variant || drupalSettings.configurableCombinations[skuCode].firstChild;

    const cartUnavailability = (
      <CartUnavailability />
    );

    return (
      <>
        <form action="#" className="sku-base-form" method="post" id="pdp-add-to-cart-form" parentsku={skuCode} variantselected={variantSelected}>
          {Object.keys(configurables).map((key) => (
            <div key={key}>
              <CartSelectOption
                configurables={configurables[key]}
                byAttribute={byAttribute}
                productInfo={productInfo}
                skuCode={skuCode}
                configurableCombinations={configurableCombinations}
                key={key}
                isGroup={configurables[key].isGroup}
                isSwatch={configurables[key].isSwatch}
                nextCode={nextCode}
                nextValues={nextValues}
                refreshConfigurables={this.refreshConfigurables}
                selectedValues={this.selectedValues}
                pdpRefresh={pdpRefresh}
              />
            </div>
          ))}
          <div className="magv2-size-btn-wrapper" onClick={() => this.openModal()}>{Drupal.t('Select size')}</div>
          <div id="product-quantity-dropdown" className="magv2-qty-wrapper">
            <QuantityDropdown
              variantSelected={variantSelected}
              productInfo={productInfo}
              skuCode={skuCode}
            />
          </div>
          {(checkoutFeatureStatus === 'enabled') ? (
            <div className="magv2-add-to-basket-container" ref={this.button}>
              <button
                className="magv2-button"
                type="submit"
                onClick={this.addToCart}
              >
                {Drupal.t('Add To Bag')}
              </button>
            </div>
          ) : cartUnavailability }
        </form>
      </>
    );
  }
}

export default ConfigurableProductForm;
