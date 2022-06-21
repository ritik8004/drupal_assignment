import React from 'react';
import _debounce from 'lodash/debounce';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import FormElement from '../form-element';
import {
  getAllowedAttributeValues,
  isMaxSaleQtyReached,
  getSelectedOptionsForCart,
  triggerUpdateCart,
  pushSeoGtmData,
  getSelectedOptionsForGtm,
} from '../../utilities/sofasectional';
import QuantitySelector from '../../../../js/utilities/components/quantity-selector';
import ErrorMessage from '../../../../js/utilities/components/error-message';
import {
  getHiddenFormAttributes,
  getQuantityDropdownValues,
  isHideMaxSaleMsg,
} from '../../../../js/utilities/display';
import SelectionSummary from '../selection-summary';
import ClearOptions from '../clear-options';

export default class SofaSectionalForm extends React.Component {
  constructor(props) {
    super(props);
    const { productInfo, configurableCombinations } = drupalSettings;
    const { sku } = props;
    let selectedVariant = configurableCombinations[sku].firstChild;

    // Set the default attributes.
    const firstChildAttributes = configurableCombinations[sku].bySku[selectedVariant];

    // Reset selectedVariant to null.
    selectedVariant = null;

    // Store reference to the config form.
    this.formRef = React.createRef();

    // Add debounce for the form Add button handler.
    this.onAddClicked = _debounce(this.onAddClicked, 300);

    // Check max sale quantity limit for the display Sku.
    const qtyLimitMessage = (isMaxSaleQtyReached(selectedVariant, productInfo[sku])
      && !isHideMaxSaleMsg())
      ? Drupal.t('Purchase limit has been reached')
      : null;

    // Set the default values.
    this.state = {
      formAttributeValues: firstChildAttributes,
      quantity: 1,
      errorMessage: qtyLimitMessage,
      groupCode: null,
      sku,
      selectedVariant,
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
    const { sku, formAttributeValues } = this.state;
    const { productInfo, configurableCombinations } = drupalSettings;
    const combinationsHierarchy = configurableCombinations[sku].combinations;
    const { configurables } = configurableCombinations[sku];

    // Update the attribute value in this temporary array.
    formAttributeValues[attributeName] = attributeValue.toString();

    // Get the allowed attributes and values for the given combination.
    let attributesAndValues = getAllowedAttributeValues(
      combinationsHierarchy,
      attributeName,
      [],
      formAttributeValues,
    );

    // Update the next level selection if current selection is no longer valid.
    // Example:
    // color: blue, band_size: 30, cup_size: c
    // color: red, band_size: 32, cup_size: d
    // For above, when user changes from 30 to 32 for band_size,
    // cup_size still says selected as c. But it's not available
    // for 32 and hence we need to update the selected combination.
    Object.keys(formAttributeValues).forEach((attribute) => {
      // Assign the first available child selection to respective filter.
      // Example:
      // parent = size:13  child = color=[red, blue]
      // Blue will be selected.
      // parents of the changed filters will remain as same.
      if (typeof attributesAndValues[attribute] !== 'undefined'
        && attribute !== attributeName) {
        formAttributeValues[attribute] = configurables[attribute].values[0].value_id;
      }

      if (typeof attributesAndValues[attribute] !== 'undefined'
        && !attributesAndValues[attribute].includes(formAttributeValues[attribute])) {
        [formAttributeValues[attribute]] = attributesAndValues[attribute];

        // Get fresh list of allowed values after changing to the available one.
        attributesAndValues = getAllowedAttributeValues(
          combinationsHierarchy,
          attributeName,
          [],
          formAttributeValues,
        );
      }
    });

    // Get the selected variant based on the attributes combination.
    const selectedVariant = this.getSelectedVariant(formAttributeValues);
    if (typeof selectedVariant !== 'undefined') {
      // Set selected variant sku in hidden input required by jquery.
      document.getElementById('selected_variant_sku').value = selectedVariant;

      // Dispatch custom event with selected variant to trigger jquery event variant-selected,
      // which will update gallery, price block, limits etc.
      const customEvent = new CustomEvent('react-variant-select', { detail: { variant: selectedVariant } });
      document.dispatchEvent(customEvent);
    }

    // Check max sale quantity limit for the selected Sku.
    const qtyLimitMessage = (isMaxSaleQtyReached(selectedVariant, productInfo[sku])
      && !isHideMaxSaleMsg())
      ? Drupal.t('Purchase limit has been reached')
      : null;

    // Update the state with the new attribute combination and update the
    // selected sku variant on the parent.
    this.setState({
      formAttributeValues,
      errorMessage: qtyLimitMessage,
      selectedVariant,
    });
  }

  /**
   * Set the selected groupCode in the state.
   *
   * @param {string} groupCode
   *  The name of the group combination.
   */
  setGroupCode = (e, groupCode) => {
    e.preventDefault();
    this.setState({ groupCode });
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
    const { sku } = this.state;
    const { configurableCombinations } = drupalSettings;
    const configurableCombinationsByAttribute = configurableCombinations[sku].byAttribute;
    let selectedCombination = '';

    // Build the selected combination string.
    Object.keys(data).forEach((attributeName) => {
      selectedCombination += `${attributeName}|${data[attributeName]}||`;
    });

    return (typeof configurableCombinationsByAttribute[selectedCombination] !== 'undefined'
      || configurableCombinationsByAttribute[selectedCombination] !== null)
      ? configurableCombinationsByAttribute[selectedCombination]
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
    const { productInfo, configurableCombinations } = drupalSettings;
    const {
      sku,
      quantity,
      formAttributeValues,
      selectedVariant,
    } = this.state;
    const configurableAttributes = configurableCombinations[sku].configurables;
    const productData = productInfo[sku];

    const options = getSelectedOptionsForCart(
      configurableAttributes,
      formAttributeValues,
      false,
    );
    // Prepare options for GTM.
    const optionsForGtm = getSelectedOptionsForGtm(
      configurableAttributes,
      formAttributeValues,
    );

    // Retrieve the title and image to show in the minicart notification.
    let cartTitle = null;
    let cartImage = null;
    const variants = Object.values(productData.variants);
    for (let index = 0; index < variants.length; index++) {
      if (variants[index].sku === selectedVariant) {
        cartTitle = variants[index].cart_title;
        cartImage = variants[index].cart_image;
        break;
      }
    }

    // Get the cart id.
    const cartData = Drupal.alshayaSpc.getCartData();
    const cartId = (cartData !== null) ? cartData.cart_id : null;

    // Start the full screen spinner.
    Drupal.cartNotification.spinner_start();

    triggerUpdateCart({
      action: 'add item',
      sku,
      qty: quantity,
      variant: selectedVariant,
      options,
      productImage: cartImage,
      productCartTitle: cartTitle,
      cartId,
      optionsForGtm,
    }).then((response) => {
      if (response.error) {
        // Set the error message to display on re-render.
        this.setState({ errorMessage: response.error_message });
        return;
      }

      // Push product values to GTM.
      pushSeoGtmData({
        sku,
        qty: quantity,
        element: this.formRef.current,
        variant: selectedVariant,
      });
    });
  }

  /**
   * Clears selected options.
   */
  handleClearOptions = () => {
    const { sku } = this.state;
    const { elementSelector } = this.props;

    this.setState({
      selectedVariant: null,
    });

    // Dispatch custom event with selected variant to trigger jquery event variant-selected,
    // which will update gallery, price block, limits etc.
    const customEvent = new CustomEvent('react-variant-select', {
      detail: {
        sku,
        elementSelector,
      },
    });
    document.dispatchEvent(customEvent);
  };

  render() {
    const { productInfo, configurableCombinations } = drupalSettings;
    const {
      sku,
      selectedVariant,
      formAttributeValues,
      quantity,
      errorMessage,
    } = this.state;
    const productData = productInfo[sku];
    const configurableAttributes = configurableCombinations[sku].configurables;
    const hiddenAttributes = getHiddenFormAttributes();
    const combinationsHierarchy = configurableCombinations[sku].combinations;

    const mainAttribute = Object.keys(combinationsHierarchy)[0];
    const allowedAttributeValues = getAllowedAttributeValues(
      combinationsHierarchy,
      mainAttribute,
      [],
      formAttributeValues,
    );

    let isSwatch = null;
    let defaultValue = null;
    let isHidden = null;
    let allowedValues = null;

    const groupData = {};
    let { groupCode } = this.state;

    const qty = getQuantityDropdownValues();
    const options = [];
    const variantInfo = (selectedVariant !== null)
      ? productInfo[sku].variants[selectedVariant]
      : undefined;

    // Set quantity option disabled if option is greater
    // than stock quantity or max sale quantity.
    qty.forEach((val) => {
      let option = {};
      if (typeof variantInfo !== 'undefined'
        && (val > variantInfo.stock.qty
        || (variantInfo.stock.maxSaleQty !== 0
        && val > variantInfo.stock.maxSaleQty))) {
        option = {
          label: val,
          value: val,
          disabled: true,
        };
      } else {
        option = {
          label: val,
          value: val,
        };
      }
      options.push(option);
    });

    return (
      <>
        <ClearOptions
          handleClearOptions={this.handleClearOptions}
          noOfOptions={Object.keys(configurableAttributes).length}
          selectedVariant={selectedVariant}
        />
        {Object.entries(configurableAttributes).map((attribute, index) => {
          isSwatch = typeof attribute[1].is_swatch !== 'undefined'
            ? attribute[1].is_swatch
            : false;
          defaultValue = (selectedVariant !== null) ? formAttributeValues[attribute[0]] : null;
          isHidden = typeof hiddenAttributes !== 'undefined'
            ? hiddenAttributes.includes(attribute[0])
            : false;
          allowedValues = typeof allowedAttributeValues[attribute[0]] !== 'undefined'
            ? allowedAttributeValues[attribute[0]]
            : [];

          // Prepare grouped filters data.
          groupData.isGroup = attribute[1].is_group;
          if (groupData.isGroup) {
            groupCode = groupCode || attribute[0];
            groupData.defaultGroup = attribute[1].alternates[groupCode];
            groupData.setGroupCode = this.setGroupCode;
            groupData.groupAlternates = attribute[1].alternates;
          }
          return (
            <div key={attribute[0]} className={`sofa-section-card sofa-section-accordion attribute-wrapper attribute-wrapper_${attribute[0]}`} ref={this.formRef}>
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
                  allowedValues={allowedValues}
                  index={parseInt(index + 1, 10)}
                />
              </ConditionalView>
              <ConditionalView condition={!isSwatch}>
                <FormElement
                  type="unordered"
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
                  groupData={groupData}
                  index={parseInt(index + 1, 10)}
                />
              </ConditionalView>
            </div>
          );
        })}
        <ConditionalView condition={selectedVariant !== null}>
          <SelectionSummary
            selectedAttributes={formAttributeValues}
            configurableAttributes={configurableAttributes}
            selectedVariant={selectedVariant}
            productInfo={productData}
          />
        </ConditionalView>
        <ErrorMessage message={errorMessage} />
        <QuantitySelector
          options={options}
          onChange={this.onQuantityChanged}
          quantity={quantity}
          label={Drupal.t('Quantity')}
        />
        <div className="sofa-sectional-addtobag-button-wrapper">
          <input
            type="hidden"
            name="selected_variant_sku"
            id="selected_variant_sku"
            value={(selectedVariant !== null ? selectedVariant : '')}
          />
          <button
            className="sofa-sectional-addtobag-button"
            id={`sofa-sectional-addtobag-button-${sku}`}
            type="submit"
            onClick={this.handleAddToBagClick}
            // Disable add to cart button if max sale limit has reached.
            disabled={(isMaxSaleQtyReached(selectedVariant, productData)
              || selectedVariant === null)}
          >
            {Drupal.t('add to cart')}
          </button>
        </div>
      </>
    );
  }
}
