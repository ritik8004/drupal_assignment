import React from 'react';
import GroupSelectOption from '../group-select-option';
import NonGroupSelectOption from '../non-group-select-option';

class CartSelectOption extends React.Component {
  constructor(props) {
    super(props);
    const {
      isGroup, configurables,
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
    };
  }

  groupSelect = (e, group) => {
    e.preventDefault();
    this.setState({
      groupName: group,
    });
  }

  handleSelectionChanged = (e, code) => {
    e.preventDefault();
    const codeValue = e.target.value;
    const {
      configurableCombinations,
      skuCode,
      selectedValues,
      refreshConfigurables,
      pdpRefresh,
    } = this.props;
    const selectedValuesArray = selectedValues();
    let selectedCombination = '';
    Object.keys(selectedValuesArray).forEach((key) => {
      selectedCombination += `${key}|${selectedValuesArray[key]}||`;
    });
    const variantSelected = configurableCombinations[skuCode].byAttribute[selectedCombination];

    pdpRefresh(variantSelected);
    refreshConfigurables(code, codeValue, variantSelected);
  }

  render() {
    const {
      configurables,
      nextCode,
      nextValues,
    } = this.props;

    const { code } = configurables;
    const {
      groupName,
      groupStatus,
    } = this.state;

    const selectOption = (
      <div className="non-grouped-attr">
        <NonGroupSelectOption
          handleSelectionChanged={this.handleSelectionChanged}
          configurables={configurables}
          code={code}
          nextCode={nextCode}
          nextValues={nextValues}
        />
      </div>
    );

    return (groupStatus) ? (
      <div className="grouped-attr">
        <GroupSelectOption
          groupSelect={this.groupSelect}
          handleSelectionChanged={this.handleSelectionChanged}
          configurables={configurables}
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
