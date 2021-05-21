import React from 'react';
import { debounce } from 'lodash';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import FormElement from '../form-element';
import {
  getAllowedAttributeValues,
  getHiddenFormAttributes,
  getFirstAttributesAndValues,
  getQuantityDropdownValues,
  triggerUpdateCart,
  pushSeoGtmData,
  getSelectedOptionsForCart,
  getSelectedOptionsForGtm,
} from '../../utilities/addtobag';
import QuantitySelector from '../quantity-selector';
import SizeGuide from '../size-guide';
import ErrorMessage from '../error-message';
import { isDisplayConfigurableBoxes } from '../../../../js/utilities/display';
import getStringMessage from '../../../../alshaya_spc/js/utilities/strings';

export default class ConfigurableForm extends React.Component {
  constructor(props) {
    super(props);
    const { productData } = props;

    // Set the default attributes.
    const firstChild = productData.variants[0].sku;
    const firstChildAttributes = productData.configurable_combinations.by_sku[firstChild];

    // Store reference to the config form.
    this.formRef = React.createRef();

    // Add debounce for the form Add button handler.
    this.onAddClicked = debounce(this.onAddClicked, 300);

    // Set the default values.
    this.state = {
      formAttributeValues: firstChildAttributes,
      quantity: 1,
      errorMessage: null,
    };
  }

  /**
   * Event handler for change of value in the swatch component.
   *
   * @param {string} name
   *  The name of the attribute.
   * @param {string} value
   *  The attribute value.
   */
  onSwatchClick = (name, value) => this.setAttribute(name, value);

  /**
   * Event handler for change of value in the unordered list component.
   *
   * @param {string} name
   *  The name of the attribute.
   * @param {string} value
   *  The attribute value.
   */
  onListItemChange = (name, value) => this.setAttribute(name, value);

  /**
   * Updates the state when user selects a quantity.
   *
   * @param quantity
   *   The quantity selected by the user.
   */
  onQuantityChanged = (quantity) => this.setState({ quantity });

  /**
   * Set the selected attribute data and value in the state.
   *
   * @param {string} attributeName
   *  The name of the attribute.
   * @param {string} attributeValue
   *  The attribute value.
   */
  setAttribute = (attributeName, attributeValue) => {
    const { formAttributeValues } = this.state;
    const { setSelectedVariant, productData } = this.props;
    const combinationsHierarchy = productData
      .configurable_combinations
      .attribute_hierarchy_with_values;

    // Update the attribute value in this temporary array.
    formAttributeValues[attributeName] = attributeValue;

    // Get the allowed attributes and values for the given combination.
    let attributesAndValues = getAllowedAttributeValues(
      combinationsHierarchy,
      attributeName,
      attributeValue,
      [],
      formAttributeValues,
    );

    // Get the first attribute and values from the allowed list for each
    // attribute.
    attributesAndValues = getFirstAttributesAndValues(attributesAndValues);

    // Update the temporary array with the new default values for the
    // attributes.
    Object.keys(formAttributeValues).forEach((attribute) => {
      if (typeof attributesAndValues[attribute] !== 'undefined') {
        formAttributeValues[attribute] = attributesAndValues[attribute];
      }
    });

    // Get the selected variant based on the attributes combination.
    const selectedVariant = this.getSelectedVariant(formAttributeValues);

    // Update the state with the new attribute combination and update the
    // selected sku variant on the parent.
    this.setState({ formAttributeValues, errorMessage: null }, () => {
      setSelectedVariant(selectedVariant);
    });
  }

  /**
   * Get the selected variant based on the provided attribute combination.
   *
   * @param {array} data
   *   Array of attribute names and values in the following format:
   * [{attr_1_name: attr_1_value}, {attr_2_name: attr_2_value}....].
   *
   * @returns {string}
   *   The selected variant sku code or empty string if no such variant found.
   */
  getSelectedVariant = (data) => {
    const { productData } = this.props;
    const configurableCombinations = productData.configurable_combinations.by_attribute;
    let selectedCombination = '';

    // Build the selected combination string.
    Object.keys(data).forEach((attributeName) => {
      selectedCombination += `${attributeName}|${data[attributeName]}||`;
    });

    return (typeof configurableCombinations[selectedCombination] !== 'undefined'
     || configurableCombinations[selectedCombination] !== null)
      ? configurableCombinations[selectedCombination]
      : '';
  }

  /**
   * Event handler for add to bag.
   *
   * @param {object}
   *   The event object.
   */
  handleAddToBagClick = (e) => {
    e.preventDefault();
    this.onAddClicked();
  }

  /**
   * Debounced Event handler for add to bag.
   */
  onAddClicked = () => {
    const {
      productData,
      selectedVariant,
      parentSku,
      onItemAddedToCart,
      sku,
    } = this.props;

    // Get the selected quantity and the values for the attributes.
    const { quantity, formAttributeValues } = this.state;
    const options = getSelectedOptionsForCart(
      productData.configurable_attributes,
      formAttributeValues,
      false,
    );

    // Retrieve the title and image to show in the minicart notification.
    let cartTitle = null;
    let cartImage = null;
    for (let index = 0; index < productData.variants.length; index++) {
      if (productData.variants[index].sku === selectedVariant) {
        cartTitle = productData.variants[index].cart_title;
        cartImage = productData.variants[index].cart_image;
        break;
      }
    }

    // Get the cart id.
    const cartData = Drupal.alshayaSpc.getCartData();
    const cartId = (cartData !== null) ? cartData.cart_id : null;

    // Start the full screen spinner.
    const addToCartBtn = document.getElementById(`config-form-addtobag-button-${sku}`);
    addToCartBtn.classList.toggle('add-to-bag-loader');

    triggerUpdateCart({
      action: 'add item',
      sku: parentSku,
      qty: quantity,
      variant: selectedVariant,
      options,
      productImage: cartImage,
      productCartTitle: cartTitle,
      cartId,
      notify: true,
    }).then((response) => {
      if (response.error) {
        // Stop the full screen loader.
        addToCartBtn.classList.toggle('add-to-bag-loader');

        // Trigger GTM.
        const optionsForGtm = getSelectedOptionsForGtm(
          productData.configurable_attributes,
          formAttributeValues,
        );
        pushSeoGtmData({
          sku: parentSku,
          error: true,
          error_message: response.error_message,
          options: optionsForGtm,
        });

        // Set the error message to display on re-render.
        this.setState({ errorMessage: response.error_message });
        return;
      }

      // Push product values to GTM.
      pushSeoGtmData({
        qty: quantity,
        prevQty: 0,
        element: this.formRef.current,
        variant: selectedVariant,
      });

      // Full screen loader is stopped in success case in
      // Drupal.cartNotification.triggerNotification().
      // Send success status to parent component.
      addToCartBtn.classList.toggle('tick');
      addToCartBtn.innerHTML = `<span> ${Drupal.t('Item added')} </span>`;
      setTimeout(() => {
        onItemAddedToCart(true);
      }, 500);
    });
  }

  render() {
    const { sku, productData } = this.props;
    const configurableAttributes = productData.configurable_attributes;
    const { formAttributeValues, quantity, errorMessage } = this.state;
    const hiddenAttributes = getHiddenFormAttributes();
    const combinationsHierarchy = productData
      .configurable_combinations
      .attribute_hierarchy_with_values;

    const mainAttribute = Object.keys(combinationsHierarchy)[0];
    const allowedAttributeValues = getAllowedAttributeValues(
      combinationsHierarchy,
      mainAttribute,
      formAttributeValues[mainAttribute],
      [],
      formAttributeValues,
    );

    let isSwatch = null;
    let defaultValue = null;
    let isHidden = null;
    let allowedValues = null;
    let showSizeGuide = true;
    let showSizeGuideCond = null;
    const widget = isDisplayConfigurableBoxes() ? 'unordered' : 'select';

    return (
      <>
        <form className="sku-form" data-sku={sku} key={sku} ref={this.formRef}>
          {Object.entries(configurableAttributes).map((attribute) => {
            isSwatch = attribute[1].is_swatch;
            defaultValue = formAttributeValues[attribute[0]];
            isHidden = typeof hiddenAttributes !== 'undefined'
              ? hiddenAttributes.includes(attribute[0])
              : false;
            allowedValues = typeof allowedAttributeValues[attribute[0]] !== 'undefined'
              ? allowedAttributeValues[attribute[0]]
              : [];

            showSizeGuideCond = (typeof productData.size_guide.attributes !== 'undefined'
              && productData.size_guide.attributes.indexOf(attribute[0]) !== -1
              && showSizeGuide);
            if (showSizeGuideCond && showSizeGuide) {
              // We want size guide link to show only once.
              showSizeGuide = false;
            }

            return (
              <div key={attribute[0]} className={`attribute-wrapper attribute-wrapper_${attribute[0]}`}>
                <ConditionalView condition={isSwatch}>
                  <FormElement
                    type="swatch"
                    attributeName={attribute[0]}
                    options={attribute[1].swatches}
                    label={attribute[1].label}
                    defaultValue={defaultValue}
                    activeClass="active"
                    disabledClass="disabled"
                    onChange={this.onSwatchClick}
                    isHidden={isHidden}
                    setAttribute={this.setAttribute}
                    allowedValues={[]}
                  />
                </ConditionalView>
                <ConditionalView condition={showSizeGuideCond}>
                  <SizeGuide
                    sizeGuideData={productData.size_guide}
                  />
                </ConditionalView>
                <ConditionalView condition={!isSwatch}>
                  <FormElement
                    type={widget}
                    attributeName={attribute[0]}
                    options={attribute[1].values}
                    label={attribute[1].label}
                    defaultValue={defaultValue}
                    activeClass="active"
                    disabledClass="disabled"
                    onChange={this.onListItemChange}
                    isHidden={isHidden}
                    setAttribute={this.setAttribute}
                    allowedValues={allowedValues}
                  />
                </ConditionalView>
              </div>
            );
          })}
          <QuantitySelector
            type="dropdown"
            options={getQuantityDropdownValues()}
            onChange={this.onQuantityChanged}
            quantity={quantity}
            label={getStringMessage('quantity')}
          />
          <ErrorMessage message={errorMessage} />
          <div className="config-form-addtobag-button-wrapper">
            <button
              className="config-form-addtobag-button"
              id={`config-form-addtobag-button-${sku}`}
              type="button"
              onClick={this.handleAddToBagClick}
            >
              {Drupal.t('add to cart')}
            </button>
          </div>
        </form>
      </>
    );
  }
}
