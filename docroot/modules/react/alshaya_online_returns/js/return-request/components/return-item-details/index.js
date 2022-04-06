import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import {
  getReturnReasons,
  getQuantityOptions,
  addCheckboxToReturnItem,
} from '../../../utilities/return_request_util';
import ReturnIndividualItem from '../return-individual-item';
import ReturnQuantitySelect from '../return-quantity-select';
import ReturnReasonsSelect from '../return-reasons-select';

class ReturnItemDetails extends React.Component {
  constructor(props) {
    // Adding default reason and default quantity values.
    super(props);
    const { item: { qty_ordered: qtyOrdered } } = props;
    this.state = {
      isChecked: false,
      returnReasons: getReturnReasons(),
      qtyOptions: getQuantityOptions(qtyOrdered),
    };
  }

  /**
   * This handles click on item return checkbox.
   */
  handleItemReturn = () => {
    const { processSelectedItems, item } = this.props;
    const { isChecked } = this.state;

    processSelectedItems(!isChecked, item);
    // If checkbox is checked, we show reason and quantity dropdowns.
    this.setState({
      isChecked: !isChecked,
    });
  };

  render() {
    const {
      isChecked, returnReasons, qtyOptions,
    } = this.state;
    const { item, handleSelectedReason } = this.props;
    const checkedClass = isChecked ? 'is-checked' : '';
    return (
      <div className="items-table">
        <div className="order-item-row">
          <div className="order-item-checkbox">
            <ConditionalView condition={addCheckboxToReturnItem(item)}>
              <span
                className={`return_item_checkbox ${checkedClass}`}
                onClick={this.handleItemReturn}
              />
            </ConditionalView>
          </div>
          <ReturnIndividualItem
            item={item}
          />
          <ConditionalView condition={isChecked}>
            <ReturnReasonsSelect
              returnReasons={returnReasons}
              handleSelectedReason={handleSelectedReason}
            />
            <ReturnQuantitySelect
              qtyOptions={qtyOptions}
            />
          </ConditionalView>
        </div>
      </div>
    );
  }
}

export default ReturnItemDetails;
