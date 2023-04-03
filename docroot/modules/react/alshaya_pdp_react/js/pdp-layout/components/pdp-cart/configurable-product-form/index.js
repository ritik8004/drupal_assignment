import React, { createRef } from 'react';
import CartSelectOption from '../cart-select-option';
import { addToCartConfigurable } from '../../../../utilities/pdp_layout';
import CartUnavailability from '../cart-unavailability';
import QuantityDropdown from '../quantity-dropdown';
import SelectSizeButton from '../select-size-button';
import { smoothScrollToActiveSwatch } from '../../../../../../js/utilities/smoothScroll';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import isAuraEnabled from '../../../../../../js/utilities/helper';
import { isProductBuyable } from '../../../../../../js/utilities/display';
import SizeGuide from '../size-guide';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import QuickFitAttribute from '../quick-fit-attribute';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getShoeSize, getShoeAiStatus } from '../../../../../../js/utilities/util';

class ConfigurableProductForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      nextCode: null,
      nextValues: null,
      attributeAvailable: false,
    };

    this.button = createRef();
  }

  componentDidMount() {
    this.handleLoad();
    const { context, refCartButton } = this.props;

    // Condition to check if add to cart
    // button is available.
    if (document.getElementById(`add-to-cart-${context}`)) {
      jQuery(document).ready(() => {
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

    this.setState({
      attributeAvailable: true,
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
    const attributes = configurableCombinations[skuCode].configurables;
    const code = Object.keys(combinations)[0];
    let codeValue = Object.keys(combinations[code])[0];
    if (hasValue(configurableCombinations[skuCode].firstChild)
      && hasValue(configurableCombinations[skuCode].bySku)) {
      // Fetch attribute code value of current child if exists.
      const { firstChild } = configurableCombinations[skuCode];
      codeValue = hasValue(configurableCombinations[skuCode].bySku[firstChild])
        ? configurableCombinations[skuCode].bySku[firstChild][code]
        : codeValue;
    }
    this.refreshConfigurables(code, codeValue, null);
    Object.keys(attributes).forEach((key) => {
      if (attributes[key].isSwatch) {
        const elem = document.querySelector('#pdp-add-to-cart-form-main .magv2-swatch-attribute').querySelectorAll('.active')[0];
        if (elem !== undefined && window.innerWidth < 768) {
          smoothScrollToActiveSwatch(elem);
        }
      }
    });
  }

  // To get available attribute value based on user selection.
  refreshConfigurables = (code, codeValue, variantSelected) => {
    const {
      configurableCombinations, skuCode,
    } = this.props;
    const selectedValues = this.selectedValues();
    // Refresh configurables.
    let { combinations } = configurableCombinations[skuCode];

    this.setState({
      variant: variantSelected,
    });

    selectedValues.forEach((key) => {
      if (key !== code) {
        combinations = combinations[key][selectedValues[key]];
      }
    });

    if ((typeof variantSelected !== 'undefined') && (variantSelected !== null)) {
      const event = new CustomEvent('magazinev2-variant-selected', {
        bubbles: true,
        detail: { variant: variantSelected },
      });
      document.querySelector('.sku-base-form').dispatchEvent(event);
    }

    // Dispatch event on variant update id aura enabled.
    if (isAuraEnabled()) {
      this.dispatchUpdateEvent(variantSelected);
    }

    if (typeof combinations[code] === 'undefined') {
      return;
    }

    if (combinations[code][codeValue] === 1) {
      this.setState({
        nextCode: code,
        nextValues: Object.keys(combinations[code]),
      });
      return;
    }

    if (combinations[code][codeValue]) {
      const nextCode = Object.keys(combinations[code][codeValue])[0];
      const nextValues = Object.keys(combinations[code][codeValue][nextCode]);
      this.setState({
        nextCode,
        nextValues,
      });
    }
  }

  dispatchUpdateEvent = (variantSelected) => {
    const {
      skuCode, productInfo, context, firstChild,
    } = this.props;

    // Dispatching event on variant change to listen in react.
    const selectedVariant = variantSelected || firstChild;
    const qty = document.querySelector(`#pdp-add-to-cart-form-${context} input[name="quantity"]`).value;
    const priceKey = (context === 'related') ? 'final_price' : 'finalPrice';
    const price = productInfo[skuCode].variants
      ? productInfo[skuCode].variants[selectedVariant][priceKey]
      : productInfo[skuCode][priceKey];
    const data = {
      amount: price * qty,
    };
    dispatchCustomEvent('auraProductUpdate', { data, context });
  };

  selectedValues = () => {
    const { configurableCombinations, skuCode, context } = this.props;
    const attributes = configurableCombinations[skuCode].configurables;
    const selectedValues = [];
    Object.keys(attributes).map((id) => {
      const elem = document.querySelector(`#pdp-add-to-cart-form-${context} #${id}`).querySelectorAll('.active')[0];
      if (elem !== undefined) {
        const selectedVal = document.querySelector(`#pdp-add-to-cart-form-${context} #${id}`).querySelectorAll('.active')[0].value;
        if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
          selectedValues[id] = selectedVal;
        }
      }
      return selectedValues;
    });
    return selectedValues;
  }

  openModal = () => {
    const { context } = this.props;
    if (context === 'main') {
      document.querySelector('body').classList.add('overlay-select');
    } else {
      document.querySelector('body').classList.add('overlay-related-select');
    }
  };

  buttonLabel = (attr) => {
    const { context, skuCode, configurableCombinations } = this.props;
    const { configurables } = configurableCombinations[skuCode];
    const sizeElem = document.querySelector(`#pdp-add-to-cart-form-${context} #${attr}`).querySelectorAll('.active')[0];
    // Setting default value for size drawer label.
    let label = Drupal.t('Select @title', { '@title': configurables[attr].label });
    if (sizeElem !== undefined) {
      const size = document.querySelector(`#pdp-add-to-cart-form-${context} #${attr}`).querySelectorAll('.active')[0].innerText;
      let group = '';
      label = size;
      // Check if size group is available.
      if (document.querySelector(`#pdp-add-to-cart-form-${context} .group-anchor-links`)) {
        group = document.querySelector(`#pdp-add-to-cart-form-${context} .group-anchor-links`).querySelectorAll('.active')[0].innerText;
        label = `${group}, ${size}`;
        return label;
      }
    }
    return label;
  }

  // Returns true if showSizeGuideOnPdpPage is enabled.
  showSizeGuideOnPdpPage = () => {
    const { showSizeGuideOnPdpPage } = drupalSettings;
    if (showSizeGuideOnPdpPage) {
      return true;
    }
    return false;
  }

  render() {
    const {
      configurableCombinations,
      skuCode,
      productInfo,
      pdpRefresh,
      pdpLabelRefresh,
      stockQty,
      firstChild,
      context,
      closeModal,
    } = this.props;

    const { configurables } = configurableCombinations[skuCode];
    const { byAttribute } = configurableCombinations[skuCode];
    const activateShoeAI = getShoeAiStatus();
    let shoeSize = [];
    // value of shoe_size_eu only used for shoeai.
    if (activateShoeAI && configurables.size_shoe_eu && configurables.size_shoe_eu.values) {
      shoeSize = getShoeSize(configurables.size_shoe_eu.values);
    }

    const {
      nextCode, nextValues, variant, attributeAvailable,
    } = this.state;
    const variantSelected = variant || firstChild;

    const cartUnavailability = (
      <CartUnavailability />
    );
    const id = `add-to-cart-${context}`;
    return (
      <form action="#" className="sku-base-form" method="post" id={`pdp-add-to-cart-form-${context}`} parentsku={skuCode} variantselected={variantSelected} data-sku={skuCode}>
        <div id="add-to-cart-error" className="error" />
        {Object.keys(configurables).map((key) => (
          <div className={`cart-form-attribute ${key}`} key={key} data-attribute-name={configurables[key].label}>
            <CartSelectOption
              configurables={configurables[key]}
              byAttribute={byAttribute}
              productInfo={productInfo}
              skuCode={skuCode}
              configurableCombinations={configurableCombinations}
              key={key}
              attributeKey={key}
              isGroup={configurables[key].isGroup}
              isSwatch={configurables[key].isSwatch}
              isSwatchGroup={configurables[key].isSwatchGroup}
              nextCode={nextCode}
              nextValues={nextValues}
              refreshConfigurables={this.refreshConfigurables}
              selectedValues={this.selectedValues}
              pdpRefresh={pdpRefresh}
              context={context}
            />
          </div>
        ))}
        {activateShoeAI === true ? (
          <div
            className="ShoeSizeMe ssm_pdp"
            data-shoeid={skuCode}
            data-availability={shoeSize}
            data-sizerun={shoeSize}
          />
        ) : null}

        <ConditionalView condition={this.showSizeGuideOnPdpPage()}>
          <div className="size-guide-on-pdp">
            {Object.keys(configurables).map((key) => {
              if (/size/.test(key) && attributeAvailable) {
                return (
                  <SizeGuide attrId={key} key={key} />
                );
              }
              return null;
            })}
          </div>
        </ConditionalView>
        {Object.keys(configurables).map((key) => {
          if (/size/.test(key) && attributeAvailable) {
            const label = this.buttonLabel(key);
            return (
              <SelectSizeButton
                openModal={this.openModal}
                key={key}
                attr={key}
                label={label}
              />
            );
          }
          return null;
        })}
        <div id="product-quantity-dropdown" className="magv2-qty-wrapper">
          <QuantityDropdown
            variantSelected={variantSelected}
            productInfo={productInfo}
            skuCode={skuCode}
            stockQty={stockQty}
            context={context}
          />
        </div>
        <QuickFitAttribute
          productInfo={productInfo}
          skuCode={skuCode}
        />
        {(isProductBuyable(productInfo[skuCode].is_product_buyable)) ? (
          <>
            <div className="magv2-add-to-basket-container" ref={this.button}>
              <button
                className="magv2-button add-to-cart-button"
                id={`add-to-cart-${context}`}
                type="submit"
                onClick={(e) => addToCartConfigurable(
                  e,
                  id,
                  configurableCombinations,
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
        ) : cartUnavailability }
      </form>
    );
  }
}

export default ConfigurableProductForm;
