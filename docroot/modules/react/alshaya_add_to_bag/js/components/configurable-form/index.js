import React from 'react';
import _debounce from 'lodash/debounce';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import FormElement from '../form-element';
import {
  getAllowedAttributeValues,
  getHiddenFormAttributes,
  getQuantityDropdownValues,
  triggerUpdateCart,
  pushSeoGtmData,
  getSelectedOptionsForCart,
  getSelectedOptionsForGtm,
  isMaxSaleQtyReached,
  isHideMaxSaleMsg,
  getDefaultAttributeValues,
} from '../../utilities/addtobag';
import QuantitySelector from '../quantity-selector';
import SizeGuide from '../size-guide';
import ErrorMessage from '../error-message';
import { isDisplayConfigurableBoxes } from '../../../../js/utilities/display';
import getStringMessage from '../../../../alshaya_spc/js/utilities/strings';
import WishlistContainer from '../../../../js/utilities/components/wishlist-container';
import { isWishlistEnabled, isWishlistPage } from '../../../../js/utilities/wishlistHelper';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

export default class ConfigurableForm extends React.Component {
  constructor(props) {
    super(props);
    const { selectedVariant, productData, extraInfo } = props;

    // Set the default attributes.
    const firstChildAttributes = productData.configurable_combinations.by_sku[selectedVariant];

    // If the current page is not a wishlist page, then we will choose the first
    // available options, else will use the user chosen variant.
    if (!isWishlistPage(extraInfo)) {
      const combinationsHierarchy = productData
        .configurable_combinations
        .attribute_hierarchy_with_values;
      let activeSwatchKey = null;
      let activeSwatchValue = null;

      // Get the active swatch key and value.
      Object.keys(firstChildAttributes).some((key) => {
        if (productData.configurable_attributes[key].is_swatch) {
          activeSwatchKey = key;
          activeSwatchValue = firstChildAttributes[key];
          return true;
        }
        return false;
      });

      // Get the first available values for other attributes based on the active
      // swatch value.
      if (activeSwatchKey && activeSwatchValue) {
        // Get the next attribute data from the hierarchy.
        const activeSwatchData = combinationsHierarchy[activeSwatchKey][activeSwatchValue];

        // Create clones to allow modification.
        const firstChildAttributesClone = JSON.parse(JSON.stringify(firstChildAttributes));
        delete firstChildAttributesClone[activeSwatchKey];

        Object.keys(firstChildAttributesClone).forEach((attribute) => {
          const defaultAttributeValue = getDefaultAttributeValues(
            activeSwatchData,
            attribute,
            firstChildAttributesClone,
            productData.configurable_attributes,
          );

          // Update to default value.
          if (defaultAttributeValue.length !== 0) {
            firstChildAttributes[attribute] = defaultAttributeValue;
            firstChildAttributesClone[attribute] = defaultAttributeValue;
          }
        });
      }
    }

    // Store reference to the config form.
    this.formRef = React.createRef();

    // Add debounce for the form Add button handler.
    this.onAddClicked = _debounce(this.onAddClicked, 300);

    // Check max sale quantity limit for the display Sku.
    const qtyLimitMessage = (isMaxSaleQtyReached(selectedVariant, productData)
      && !isHideMaxSaleMsg())
      ? Drupal.t('Purchase limit has been reached')
      : null;

    // Set the default values.
    this.state = {
      formAttributeValues: firstChildAttributes,
      quantity: 1,
      errorMessage: qtyLimitMessage,
      groupCode: null,
    };
  }

  /**
   * Event handler for change of value in the swatch component.
   *
   * @param {string} name
   *  The name of the attribute.
   * @param {string} value
   *  The attribute value.
   * @param {string} swatchLabel
   *  Label of the selected option.
   */
  onSwatchClick = (name, value, swatchLabel) => {
    this.setAttribute(name, value);

    // Push quick add color click event to GTM.
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'plp color click',
      eventLabel: hasValue(swatchLabel) ? swatchLabel : '',
      eventLabel2: 'plp_quickview',
      product_view_type: 'quick_view',
    });
  }

  /**
   * Event handler for change of value in the unordered list component.
   *
   * @param {string} name
   *  The name of the attribute.
   * @param {string} value
   *  The attribute value.
   * @param {string} optionLabel
   *  Label of the selected option.
   */
  onListItemChange = (name, value, optionLabel) => {
    this.setAttribute(name, value);

    // Push quick add size click event to GTM.
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'plp size click',
      eventLabel: hasValue(optionLabel) ? optionLabel : '',
      eventLabel2: 'plp_quickview',
      product_view_type: 'quick_view',
    });
  };

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

    // Check max sale quantity limit for the selected Sku.
    const qtyLimitMessage = (isMaxSaleQtyReached(selectedVariant, productData)
      && !isHideMaxSaleMsg())
      ? Drupal.t('Purchase limit has been reached')
      : null;

    // Update the state with the new attribute combination and update the
    // selected sku variant on the parent.
    this.setState({ formAttributeValues, errorMessage: qtyLimitMessage }, () => {
      setSelectedVariant(selectedVariant);
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
      // Convert both product data variant SKU and selectedVariant SKU values
      // to string for better comparison, or it will failed if SKU code only
      // contains numeric values.
      if (productData.variants[index].sku.toString()
        === selectedVariant.toString()) {
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
      skuType: 'config',
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
        product_view_type: 'quick_view',
      });

      // Full screen loader is stopped in success case in
      // Drupal.cartNotification.triggerNotification().
      // Send success status to parent component.
      addToCartBtn.classList.toggle('tick');
      addToCartBtn.innerHTML = `<span> ${getStringMessage('item_added')} </span>`;
      setTimeout(() => {
        onItemAddedToCart(true);
      }, 500);

      // Dispatch add to cart event for product drawer components.
      dispatchCustomEvent('product-add-to-cart-success', { sku: parentSku });
    });
  }

  render() {
    const {
      sku,
      selectedVariant,
      productData,
      extraInfo,
      parentSku,
      wishListButtonRef,
    } = this.props;
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

    const groupData = {};
    let { groupCode } = this.state;

    let addToCartText = getStringMessage('add_to_cart');
    // Check if button text is available in extraInfo.
    if (typeof extraInfo.addToCartButtonText !== 'undefined') {
      addToCartText = extraInfo.addToCartButtonText;
    }

    const options = [];
    // Add configurable options only for configurable product.
    if (isWishlistEnabled() && configurableAttributes && formAttributeValues) {
      Object.keys(formAttributeValues).forEach((key) => {
        const option = {
          option_id: configurableAttributes[key].id,
          option_value: formAttributeValues[key],
        };
        options.push(option);
      });
    }

    let hasGroups = false;
    const attributesContainer = Object.entries(configurableAttributes).map((attribute) => {
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

      // Prepare grouped filters data.
      groupData.isGroup = attribute[1].is_group;
      if (groupData.isGroup) {
        groupCode = groupCode || attribute[0];
        groupData.defaultGroup = attribute[1].alternates[groupCode];
        groupData.setGroupCode = this.setGroupCode;
        groupData.groupAlternates = attribute[1].alternates;
        hasGroups = true;
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
              context="quick_view"
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
              groupData={groupData}
            />
          </ConditionalView>
        </div>
      );
    });
    let formClass = 'sku-form';
    if (hasGroups) {
      formClass += ' has-groups';
    }

    return (
      <>
        <form className={formClass} data-sku={sku} key={sku} ref={this.formRef}>
          {attributesContainer}
          <QuantitySelector
            type="dropdown"
            options={getQuantityDropdownValues(selectedVariant, productData)}
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
              // Disable add to bag button if max sale limit has reached.
              disabled={isMaxSaleQtyReached(selectedVariant, productData)}
            >
              {addToCartText}
            </button>
          </div>
          <ConditionalView condition={!isWishlistPage(extraInfo)}>
            {/* Here skuMainCode is parent sku of variant selected */}
            <WishlistContainer
              sku={sku}
              skuCode={parentSku}
              context="productDrawer"
              position="top"
              format="icon"
              title={productData.title}
              options={options}
              wishListButtonRef={wishListButtonRef}
            />
          </ConditionalView>
        </form>
      </>
    );
  }
}
