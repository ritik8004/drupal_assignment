import React from 'react';
import ReactDOM from 'react-dom';
import QuantityDropdown from '../quantity-dropdown';
import PdpGallery from '../../pdp-gallery';
import PdpInfo from '../../pdp-info';
import GroupSelectOption from '../group-select-option';

class CartSelectOption extends React.Component {
  constructor(props) {
    super(props);
    const { skuCode, configurableCombinations, isGroup } = this.props;
    // const attributes = configurableCombinations[skuCode].configurables;
    // const code = Object.keys(attributes)[0];
    const firstChild = configurableCombinations[skuCode].firstChild;
    // const codeValue = configurableCombinations[skuCode].bySku[firstChild][code];
    // this.refreshConfigurables(code, codeValue);
    this.state = {
      showGroup: false,
      groupName: null,
      pdpRefresh: false,
      nextCode: null,
      nextValues: null,
      variantSelected: firstChild ? firstChild : skuCode,
      groupStatus: isGroup,
    };
  }

  selectedValues = () => {
    const { configurableCombinations, skuCode } = this.props;
    const attributes = configurableCombinations[skuCode].configurables;
    let selectedValues = [];
    Object.keys(attributes).map((id) => {
      const selectedVal = document.getElementById(id).value;
      if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
        selectedValues[id] = selectedVal;
      }
      return selectedValues;
    });
    return selectedValues;
  }

  refreshConfigurables = (code, codeValue) => {
    const { configurableCombinations, skuCode } = this.props;
    const selectedValues = this.selectedValues();
    // Refresh configurables.
    let combinations = configurableCombinations[skuCode]['combinations'];
    for (let key in selectedValues) {
      if (key == code) {
        break;
      }

      combinations = combinations[key][selectedValues[key]];
    }

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
        nextCode: nextCode,
        nextValues: nextValues,
      });
      const nextVal = document.getElementById(nextCode).value;
      this.refreshConfigurables(nextCode, nextVal);
    }
  }

  render() {
    const {
      configurables, productInfo, skuCode
    } = this.props;
    const { code } = configurables;
    const {
      showGroup,
      groupName,
      pdpRefresh,
      variantSelected,
      nextCode,
      nextValues,
      groupStatus
    } = this.state;

    const groupSelect = (e, group) => {
      e.preventDefault();
      this.setState({
        showGroup: true,
        groupName: group,
      });
    }

    const handleSelectionChanged = (e, code) => {
      e.preventDefault();
      const codeValue = e.target.value;
      const { configurableCombinations, skuCode } = this.props;
      const selectedValues = this.selectedValues();
      let selectedCombination = '';
      Object.keys(selectedValues).forEach((key) => {
        selectedCombination += `${key}|${selectedValues[key]}||`;
      });
      this.setState({
        pdpRefresh: true,
        variantSelected: configurableCombinations[skuCode].byAttribute[selectedCombination],
      });

      this.refreshConfigurables(code, codeValue);

    }

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

    const selectOption = (
      <>
        <select id={code} className="select-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
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

    console.log(configurables);

    return (groupStatus) ? (
      <div className="grouped-attr">
        <GroupSelectOption
          groupSelect={groupSelect}
          handleSelectionChanged={handleSelectionChanged}
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
