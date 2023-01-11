import React from 'react';
import GroupSelectOption from '../group-select-option';
import GroupSwatchSelectOption from '../group-swatch-select-option';
import NonGroupSelectOption from '../non-group-select-option';
import SwatchSelectOption from '../swatch-select-option';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

class CartSelectOption extends React.Component {
  constructor(props) {
    super(props);
    const {
      isGroup, configurables, isSwatch, isSwatchGroup,
    } = this.props;
    let defaultGroup = null;


    if (isGroup) {
      const { alternates } = configurables;
      const { code } = configurables;
      defaultGroup = alternates[code];
    }

    this.state = {
      groupName: isGroup ? defaultGroup : null,
      groupStatus: isGroup,
      swatchStatus: isSwatch,
      groupSwatchStatus: isSwatchGroup,
      selected: null,
    };
  }

  componentDidMount() {
    const {
      configurableCombinations, skuCode, configurables, context,
    } = this.props;

    const { firstChild, combinations } = configurableCombinations[skuCode];
    const { code, values } = configurables;
    let value = configurableCombinations[skuCode].bySku[firstChild][code];
    const { swatchStatus } = this.state;
    let availableAttributeValues = null;

    // Update the default value if needed.
    if (!swatchStatus) {
      const mainCode = Object.keys(combinations)[0];

      // Get a list of available values for the current attribute combination.
      if (code === mainCode) {
        availableAttributeValues = Object.keys(combinations[code]);
      } else {
        const mainCodeValue = Object.keys(combinations[mainCode])[0];
        availableAttributeValues = hasValue(combinations[mainCode][mainCodeValue][code])
          ? Object.keys(combinations[mainCode][mainCodeValue][code])
          : null;
      }
      Object.values(values).some((attrValue) => {
        const currentAttributeValue = Object.keys(attrValue)[0];

        if (hasValue(availableAttributeValues)
          && availableAttributeValues.includes(currentAttributeValue)) {
          value = currentAttributeValue;
          return true;
        }
        return false;
      });
    }

    // Setting active class for the
    // default variant.
    const elem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${code} li#value${value}`);
    if (!(elem == null)) {
      if (elem.classList.contains('in-active')) {
        elem.classList.remove('in-active');
      }
      elem.classList.toggle('active');
      this.setState({
        selected: value,
      });
    }
  }

  // To get the option values of the
  // selected group.
  groupSelect = (e, group) => {
    e.preventDefault();
    this.setState({
      groupName: group,
    });
  }

  handleSelectionChanged = (e, code) => {
    e.preventDefault();
    const codeValue = e.currentTarget.parentElement.value;
    const {
      configurableCombinations,
      skuCode,
      selectedValues,
      refreshConfigurables,
      pdpRefresh,
      productInfo,
      context,
    } = this.props;
    const selectedValuesArray = selectedValues();
    let selectedCombination = '';
    Object.keys(selectedValuesArray).forEach((key) => {
      selectedCombination += `${key}|${selectedValuesArray[key]}||`;
    });
    const variantSelected = configurableCombinations[skuCode].byAttribute[selectedCombination];
    const parentSkuSelected = (productInfo[skuCode].variants[variantSelected] !== undefined)
      ? productInfo[skuCode].variants[variantSelected].parent_sku
      : skuCode;

    if (context === 'main') {
      const { currentLanguage } = drupalSettings.path;
      const variantInfo = productInfo[skuCode].variants[variantSelected];
      if (variantInfo !== undefined) {
        const variantUrl = variantInfo.url[currentLanguage];
        if (window.location.pathname !== variantUrl) {
          window.history.replaceState(variantInfo, variantInfo.title, variantUrl);
          // Language switcher.
          let i;
          const langSwitcherElem = document.querySelectorAll('.language-switcher-language-url .language-link');
          for (i = 0; i < langSwitcherElem.length; i++) {
            const hrefLang = langSwitcherElem[i].getAttribute('hreflang');
            langSwitcherElem[i].setAttribute('href', variantInfo.url[hrefLang]);
          }
        }
        // Trigger an event on variant select.
        // Only considers variant when url is changed.
        const currentSelectedVariantEvent = new CustomEvent('onSkuVariantSelect', {
          bubbles: true,
          detail: {
            data: {
              sku: parentSkuSelected,
              viewMode: 'full',
              eligibleForReturn: variantInfo.eligibleForReturn,
            },
          },
        });
        document.dispatchEvent(currentSelectedVariantEvent);
      }
    }

    // Refresh the PDP page on new variant selection.
    pdpRefresh(variantSelected, parentSkuSelected);

    // Get available values for the selected variables.
    refreshConfigurables(code, codeValue, variantSelected);
  }

  /**
   * Handle click on <li>.
   */
  handleLiClick = (e, code) => {
    const { context, configurableCombinations, skuCode } = this.props;
    const { configurables } = configurableCombinations[skuCode];
    const codeValue = e.currentTarget.parentElement.value;
    this.setState({
      selected: codeValue,
    });
    // Remove the previous active class.
    const activeElem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${code} li.active`);
    if (activeElem) {
      activeElem.classList.remove('active');
      activeElem.classList.toggle('in-active');
    }
    // Set active class on the current element.
    const elem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${code} li#value${codeValue}`);
    if (elem.classList.contains('in-active')) {
      elem.classList.remove('in-active');
    }
    elem.classList.toggle('active');
    // Refresh the active class of other attributes as
    // active class on the previous element might be
    // of a disabled value.
    if (configurableCombinations[skuCode].combinations[code] !== undefined) {
      const combination = configurableCombinations[skuCode].combinations[code][codeValue];
      Object.keys(configurables).forEach((key) => {
        // Condition to get the non-selected attribute.
        if (key !== code) {
          // Remove the previous active class.
          const nonselectedElem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${key} li.active`);
          if (nonselectedElem) {
            nonselectedElem.classList.remove('active');
            nonselectedElem.classList.toggle('in-active');
          }
          const firstAvailableVal = Object.keys(combination[key])[0];
          // Set active class on the current element.
          const availableElem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${key} li#value${firstAvailableVal}`);
          if (availableElem.classList.contains('in-active')) {
            availableElem.classList.remove('in-active');
          }
          availableElem.classList.toggle('active');
        }
      });
    }

    // Push product color and size click event to GTM.
    if (code === 'color' || code === 'size') {
      const eventLabel = e.currentTarget.parentElement.dataset.attributeLabel;
      Drupal.alshayaSeoGtmPushEcommerceEvents({
        eventAction: `pdp ${code} click`,
        eventLabel,
      });
    }
    this.handleSelectionChanged(e, code);
  };

  closeModal = (e) => {
    e.preventDefault();
    const { context } = this.props;
    if (context === 'main') {
      document.querySelector('body').classList.remove('overlay-select');
    } else {
      document.querySelector('body').classList.remove('overlay-related-select');
    }
  };

  render() {
    const {
      configurables,
      nextCode,
      nextValues,
      attributeKey,
      context,
    } = this.props;

    const { code } = configurables;
    const {
      groupName,
      groupStatus,
      swatchStatus,
      selected,
      groupSwatchStatus,
    } = this.state;

    const swatchSelectOption = (
      <SwatchSelectOption
        handleSelectionChanged={this.handleSelectionChanged}
        configurables={configurables}
        code={code}
        nextCode={nextCode}
        nextValues={nextValues}
        selected={selected}
        handleLiClick={this.handleLiClick}
      />
    );

    const groupSwatchSelectOption = (
      <GroupSwatchSelectOption
        handleSelectionChanged={this.handleSelectionChanged}
        configurables={configurables}
        code={code}
        nextCode={nextCode}
        nextValues={nextValues}
        selected={selected}
        handleLiClick={this.handleLiClick}
      />
    );
    // Set the current swatch option.
    const currentSwatchSelectOption = (typeof groupSwatchStatus === 'undefined' && !groupSwatchStatus) ? swatchSelectOption : groupSwatchSelectOption;

    const selectOption = (!swatchStatus) ? (
      <div className="non-grouped-attr" onClick={(e) => (e.target.classList.contains('non-grouped-attr') ? this.closeModal(e) : null)}>
        <NonGroupSelectOption
          key={attributeKey}
          keyId={attributeKey}
          attributeKey={attributeKey}
          handleSelectionChanged={this.handleSelectionChanged}
          configurables={configurables}
          code={code}
          nextCode={nextCode}
          nextValues={nextValues}
          selected={selected}
          handleLiClick={this.handleLiClick}
          closeModal={this.closeModal}
          context={context}
        />
      </div>
    ) : currentSwatchSelectOption;

    return (groupStatus) ? (
      <div className="grouped-attr" onClick={(e) => (e.target.classList.contains('grouped-attr') ? this.closeModal(e) : null)}>
        <GroupSelectOption
          key={attributeKey}
          keyId={attributeKey}
          groupSelect={this.groupSelect}
          handleSelectionChanged={this.handleSelectionChanged}
          configurables={configurables}
          groupName={groupName}
          code={code}
          nextCode={nextCode}
          nextValues={nextValues}
          selected={selected}
          handleLiClick={this.handleLiClick}
          closeModal={this.closeModal}
          context={context}
        />
      </div>
    ) : selectOption;
  }
}

export default CartSelectOption;
