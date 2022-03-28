import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import {
  getReturnReasons,
  getQuantityOptions,
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
  handleItemReturn = (e) => {
    this.setState({
      isChecked: e.target.checked,
    });
  };

  render() {
    const {
      isChecked, returnReasons, qtyOptions,
    } = this.state;
    const { item } = this.props;
    return (
      <div className="items-tabel">
        <div className="order-item-row">
          <ConditionalView condition={item.is_returnable}>
            <div className="order-item-checkbox">
              <input
                type="checkbox"
                id="return-item-checkbox"
                name="return_item_checkbox"
                onChange={this.handleItemReturn}
              />
            </div>
          </ConditionalView>
          <ReturnIndividualItem
            item={item}
          />
        </div>
        <ConditionalView condition={isChecked}>
          <ReturnReasonsSelect
            returnReasons={returnReasons}
          />
          <ReturnQuantitySelect
            qtyOptions={qtyOptions}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default ReturnItemDetails;
