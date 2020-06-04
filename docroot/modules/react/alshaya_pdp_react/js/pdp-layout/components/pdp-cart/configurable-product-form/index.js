import React from 'react';
import ReactDOM from 'react-dom';
import CartSelectOption from '../cart-select-option';
import {
  updateCart,
  getPostData,
  triggerAddToCart,
} from '../../../../utilities/cart/cart_utils';
import CartNotification from '../cart-notification';
import CartUnavailability from '../cart-unavailability';

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

    const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');
    const getPost = getPostData(skuCode, variantSelected);

    const postData = getPost[0];
    const productData = getPost[1];

    postData.options = options;
    productData.productName = productInfo[skuCode].variants[variantSelected].cart_title;
    productData.image = productInfo[skuCode].variants[variantSelected].cart_image;

    const { cartEndpoint } = drupalSettings.cart_update_endpoint;

    updateCart(cartEndpoint, postData).then(
      (response) => {
        triggerAddToCart(response, productData, productInfo, configurableCombinations, skuCode);
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
    const { cartMaxQty, checkoutFeatureStatus } = drupalSettings;

    const { configurables } = configurableCombinations[skuCode];
    const { byAttribute } = configurableCombinations[skuCode];

    const { nextCode, nextValues, variant } = this.state;
    const variantSelected = variant || drupalSettings.configurableCombinations[skuCode].firstChild;
    const stockQty = productInfo[skuCode].variants[variantSelected].stock.qty;

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
                isSwatch={configurables[key].isSwatch}
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
