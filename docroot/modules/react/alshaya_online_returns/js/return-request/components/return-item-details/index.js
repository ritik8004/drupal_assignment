import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
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
      returnReasons: getReturnReasons(),
      qtyOptions: getQuantityOptions(qtyOrdered),
    };
  }

  /**
   * This handles click on item return checkbox.
   */
  handleItemReturn = () => {
    const { processSelectedItems, item } = this.props;
    const { isChecked } = item;

    processSelectedItems(!isChecked, item);
  };

  render() {
    const {
      returnReasons, qtyOptions,
    } = this.state;
    const {
      item,
      handleSelectedReason,
      handleSelectedQuantity,
    } = this.props;

    const checkedClass = item.isChecked ? 'is-checked' : '';
    return (
      <div className="items-table">
        <div className="order-item-row">
          <div className="order-item-checkbox">
            <ConditionalView condition={addCheckboxToReturnItem(item)}>
              <span
                id={item.sku}
                className={`return_item_checkbox ${checkedClass}`}
                onClick={this.handleItemReturn}
              />
            </ConditionalView>
          </div>
          <ReturnIndividualItem item={item} />
          <ConditionalView condition={item.isChecked}>
            <ReturnReasonsSelect
              returnReasons={returnReasons}
              handleSelectedReason={handleSelectedReason}
              sku={item.sku}
            />
            <ReturnQuantitySelect
              qtyOptions={qtyOptions}
              handleSelectedQuantity={handleSelectedQuantity}
              disableQtyBtn={hasValue(item.disableQtyBtn)}
              sku={item.sku}
            />
          </ConditionalView>
        </div>
      </div>
    );
  }
}

export default ReturnItemDetails;
