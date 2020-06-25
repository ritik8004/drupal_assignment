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

  // To get the option values of the
  // selected group.
  groupSelect = (e, group) => {
    e.preventDefault();
    this.setState({
      groupName: group,
    });
  }

  handleSelectionChanged = (e, code) => {
    const codeValue = e.currentTarget.parentElement.value;
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
    // Refresh the PDP page on new variant selection.
    pdpRefresh(variantSelected);

    // Get available values for the selected variables.
    refreshConfigurables(code, codeValue, variantSelected);
  }

  /**
   * Handle click on <li>.
   */
  handleLiClick = (e, code) => {
    this.setState({
      selected: e.currentTarget.parentElement.value,
    });
    this.handleSelectionChanged(e, code);
  };

  closeModal = () => {
    document.querySelector('body').classList.remove('select-overlay');
  };

  render() {
    const {
      configurables,
      nextCode,
      nextValues,
      key,
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
          key={key}
          handleSelectionChanged={this.handleSelectionChanged}
          configurables={configurables}
          code={code}
          nextCode={nextCode}
          nextValues={nextValues}
          selected={selected}
          handleLiClick={this.handleLiClick}
          closeModal={this.closeModal}
        />
      </div>
    ) : swatchSelectOption;

    return (groupStatus) ? (
      <div className="grouped-attr">
        <GroupSelectOption
          key={key}
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
        />
      </div>
    ) : selectOption;
  }
}

export default CartSelectOption;
