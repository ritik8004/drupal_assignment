import React from 'react';
import ReactDOM from 'react-dom';
import QuantityDropdown from '../quantity-dropdown';
import PdpGallery from '../../pdp-gallery';
import PdpInfo from '../../pdp-info';
import GroupSelectOption from '../group-select-option';
import SwatchSelectOption from '../swatch-select-option';

class CartSelectOption extends React.Component {
  constructor(props) {
    super(props);
    const {
      skuCode, configurableCombinations, isGroup, isSwatch,
    } = this.props;
    const { firstChild } = configurableCombinations[skuCode];
    this.state = {
      showGroup: false,
      groupName: null,
      pdpRefresh: false,
      variantSelected: firstChild || skuCode,
      groupStatus: isGroup,
      swatchStatus: isSwatch,
    };
  }

  groupSelect = (e, group) => {
    e.preventDefault();
    this.setState({
      showGroup: true,
      groupName: group,
    });
  }

  handleSelectionChanged = (e, code) => {
    e.preventDefault();
    const codeValue = e.target.value;
    const {
      configurableCombinations, skuCode, selectedValues, refreshConfigurables,
    } = this.props;
    const selectedValuesArray = selectedValues();
    let selectedCombination = '';
    Object.keys(selectedValuesArray).forEach((key) => {
      selectedCombination += `${key}|${selectedValuesArray[key]}||`;
    });
    const variantSelected = configurableCombinations[skuCode].byAttribute[selectedCombination];
    this.setState({
      pdpRefresh: true,
      variantSelected,
    });

    refreshConfigurables(code, codeValue, variantSelected);
  }

  render() {
    const {
      configurables,
      productInfo,
      skuCode,
      nextCode,
      nextValues,
    } = this.props;

    const { code } = configurables;
    const {
      showGroup,
      groupName,
      pdpRefresh,
      variantSelected,
      groupStatus,
      swatchStatus,
    } = this.state;

    const swatchSelectOption = (
      <SwatchSelectOption
        handleSelectionChanged={this.handleSelectionChanged}
        configurables={configurables}
        code={code}
        nextCode={nextCode}
        nextValues={nextValues}
      />
    );

    const selectOption = (!swatchStatus) ? (
      <>
        <div className="non-groupped-attr">
          <select id={code} className="select-attribute" onChange={(e) => this.handleSelectionChanged(e, code)}>
            {Object.keys(configurables.values).map((attr) => {
              if (code === nextCode) {
                if (nextValues.indexOf(attr) !== -1) {
                  return (
                    <option
                      value={configurables.values[attr].value_id}
                      key={attr}
                    >
                      {configurables.values[attr].label}
                    </option>
                  );
                }
                return (
                  <option
                    value={configurables.values[attr].value_id}
                    key={attr}
                    disabled
                  >
                    {configurables.values[attr].label}
                  </option>
                );
              }
              return (
                <option
                  value={configurables.values[attr].value_id}
                  key={attr}
                >
                  {configurables.values[attr].label}
                </option>
              );
            })}
          </select>
        </div>
      </>
    ) : swatchSelectOption;

    if (pdpRefresh && variantSelected !== undefined) {
      ReactDOM.render(
        <QuantityDropdown
          variantSelected={variantSelected}
          skuCode={skuCode}
          productInfo={productInfo}
        />,
        document.getElementById('product-quantity-dropdown'),
      );
      ReactDOM.render(
        <PdpGallery
          skuCode={variantSelected}
          pdpGallery={productInfo[skuCode].variants[variantSelected].rawGallery}
        />,
        document.getElementById('pdp-gallery-refresh'),
      );
      ReactDOM.render(
        <PdpInfo
          title={productInfo[skuCode].variants[variantSelected].title}
          pdpProductPrice={productInfo[skuCode].variants[variantSelected].priceRaw}
          finalPrice={productInfo[skuCode].variants[variantSelected].finalPrice}
        />,
        document.getElementById('pdp-info'),
      );
      const addToCart = document.querySelector('#pdp-add-to-cart-form');
      addToCart.setAttribute('variantselected', variantSelected);
    }

    return (groupStatus) ? (
      <div className="grouped-attr">
        <GroupSelectOption
          groupSelect={this.groupSelect}
          handleSelectionChanged={this.handleSelectionChanged}
          configurables={configurables}
          showGroup={showGroup}
          groupName={groupName}
          code={code}
          nextCode={nextCode}
          nextValues={nextValues}
        />
      </div>
    ) : selectOption;
  }
}

export default CartSelectOption;
