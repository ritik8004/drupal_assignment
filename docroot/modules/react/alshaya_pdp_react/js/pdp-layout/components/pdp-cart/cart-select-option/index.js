import React from 'react';
import ReactDOM from 'react-dom';
import QuantityDropdown from '../quantity-dropdown';
import PdpGallery from '../../pdp-gallery';

class CartSelectOption extends React.Component {
  constructor(props) {
    super(props);
    const { skuCode, configurableCombinations } = this.props;
    this.state = {
      showGroup: false,
      groupName: null,
      changeQty: false,
      variantSelected: configurableCombinations[skuCode].firstChild
        ? configurableCombinations[skuCode].firstChild : skuCode,
    };
  }

  groupSelect = (e, group) => {
    e.preventDefault();
    this.setState({
      showGroup: true,
      groupName: group,
    });
  }

  handleSelectionChanged = (e) => {
    e.preventDefault();
    const { configurableCombinations, skuCode } = this.props;
    const attributes = configurableCombinations[skuCode].configurables;
    const selectedValues = [];
    let selectedCombination = '';
    Object.keys(attributes).map((id) => {
      const selectedVal = document.getElementById(id).value;
      if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
        selectedValues[id] = selectedVal;
      }
      return selectedValues;
    });
    Object.keys(selectedValues).forEach((key) => {
      selectedCombination += `${key}|${selectedValues[key]}||`;
    });
    this.setState({
      changeQty: true,
      variantSelected: configurableCombinations[skuCode].byAttribute[selectedCombination],
    });
  }

  render() {
    const {
      configurables, productInfo, skuCode,
    } = this.props;
    const { code } = configurables;
    const { isGroup } = configurables;
    const {
      showGroup, groupName, changeQty, variantSelected,
    } = this.state;

    if (changeQty) {
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
      const addToCart = document.querySelector('#pdp-add-to-cart-form');
      addToCart.setAttribute('variantselected', variantSelected);
    }

    const selectOption = (
      <>
        <select id={code} className="select-attribute" onChange={(e) => this.handleSelectionChanged(e, code)}>
          {Object.keys(configurables.values).map((attr) => (
            <option
              value={configurables.values[attr].value_id}
            >
              {configurables.values[attr].label}
            </option>
          ))}
        </select>
      </>
    );

    return (isGroup) ? (
      <>
        <div className="group-anchor-wrapper">
          {Object.keys(configurables.alternates).map((alternate) => (
            <a href="#" onClick={(e) => this.groupSelect(e, configurables.alternates[alternate])}>{configurables.alternates[alternate]}</a>
          ))}
        </div>
        <div className="group-option-wrapper">
          {(showGroup && groupName) ? (
            <select id={code} className="select-attribute-group clicked" onChange={(e) => this.handleSelectionChanged(e, code)}>
              {Object.keys(configurables.values).map((attr) => (
                <option
                  value={attr}
                  groupdata={JSON.stringify(configurables.values[attr])}
                >
                  {configurables.values[attr][groupName]}
                </option>
              ))}
            </select>
          ) : (
            <select id={code} className="select-attribute-group" onChange={(e) => this.handleSelectionChanged(e, code)}>
              {Object.keys(configurables.values).map((attr) => (
                <option
                  value={attr}
                  groupdata={JSON.stringify(configurables.values[attr])}
                >
                  {configurables.values[attr].EU}
                </option>
              ))}
            </select>
          )}

        </div>
      </>
    ) : selectOption;
  }
}

export default CartSelectOption;
