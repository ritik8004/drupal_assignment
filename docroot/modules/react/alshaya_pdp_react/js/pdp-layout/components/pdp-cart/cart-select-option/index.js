import React from 'react';
import GroupSelectOption from '../group-select-option';
import NonGroupSelectOption from '../non-group-select-option';
import SwatchSelectOption from '../swatch-select-option';

class CartSelectOption extends React.Component {
  constructor(props) {
    super(props);
    const {
      isGroup, configurables, isSwatch,
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
      selected: null,
    };
  }

  componentDidMount() {
    const {
      configurableCombinations, skuCode, configurables, context,
    } = this.props;
    const { firstChild } = configurableCombinations[skuCode];
    const { code } = configurables;
    const value = configurableCombinations[skuCode].bySku[firstChild][code];
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
    const { context } = this.props;
    this.setState({
      selected: e.currentTarget.parentElement.value,
    });
    // Remove the previous active class.
    const activeElem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${code} li.active`);
    if (activeElem) {
      activeElem.classList.remove('active');
      activeElem.classList.toggle('in-active');
    }
    // Set active class on the current element.
    const elem = document.querySelector(`#pdp-add-to-cart-form-${context} ul#${code} li#value${e.currentTarget.parentElement.value}`);
    if (elem.classList.contains('in-active')) {
      elem.classList.remove('in-active');
    }
    elem.classList.toggle('active');
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

    const selectOption = (!swatchStatus) ? (
      <div className="non-grouped-attr">
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
    ) : swatchSelectOption;

    return (groupStatus) ? (
      <div className="grouped-attr">
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
