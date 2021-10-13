import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import FormElement from '../form-element';
import {
  getAllowedAttributeValues,
  isMaxSaleQtyReached,
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
    const sku = Object.keys(productInfo)[0];
    let selectedVariant = configurableCombinations[sku].firstChild;

    // Set the default attributes.
    const firstChildAttributes = configurableCombinations[sku].bySku[selectedVariant];

    // Reset selectedVariant to null.
    selectedVariant = null;

    // Check max sale quantity limit for the display Sku.
    const qtyLimitMessage = (isMaxSaleQtyReached(selectedVariant, productInfo)
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
    const qtyLimitMessage = (isMaxSaleQtyReached(selectedVariant, productInfo)
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
    // @todo: add click handler for add to cart button.
  }

  /**
   * Clears selected options.
   */
  handleClearOptions = () => {
    const { sku } = this.state;

    this.setState({
      selectedVariant: null,
    });

    // Dispatch custom event with selected variant to trigger jquery event variant-selected,
    // which will update gallery, price block, limits etc.
    const customEvent = new CustomEvent('react-variant-select', { detail: { variant: sku } });
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

    return (
      <>
        <ClearOptions
          handleClearOptions={this.handleClearOptions}
          noOfOptions={Object.keys(configurableAttributes).length}
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
            <div key={attribute[0]} className={`sofa-section-card sofa-section-accordion attribute-wrapper attribute-wrapper_${attribute[0]}`}>
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
        { selectedVariant
          && (
          <SelectionSummary
            selectedAttributes={formAttributeValues}
            configurableAttributes={configurableAttributes}
            selectedVariant={selectedVariant}
            sku={sku}
            productInfo={productInfo}
          />
          )}
        <QuantitySelector
          options={getQuantityDropdownValues()}
          onChange={this.onQuantityChanged}
          quantity={quantity}
          label={Drupal.t('Quantity')}
        />
        <ErrorMessage message={errorMessage} />
        <div className="sofa-sectional-addtobag-button-wrapper">
          <input
            type="hidden"
            name="selected_variant_sku"
            id="selected_variant_sku"
            value={(selectedVariant !== null ? selectedVariant : '')}
          />
          <button
            className="sofa-sectional-addtobag-button edit-add-to-cart"
            id={`sofa-sectional-addtobag-button-${sku}`}
            type="submit"
            onClick={this.handleAddToBagClick}
            // Disable add to cart button if max sale limit has reached.
            disabled={isMaxSaleQtyReached(selectedVariant, productInfo)}
          >
            {Drupal.t('add to cart')}
          </button>
        </div>
      </>
    );
  }
}
