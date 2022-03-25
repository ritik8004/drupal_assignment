import React from 'react';
import Select from 'react-select';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import {
  getDefaultValueForReturnReasons,
  getReturnConfigurationDetails,
  getDefaultValueForQtyDropdown,
  populateQtyDropDownList,
} from '../../../utilities/return_request_util';
import ReturnIndividualItem from '../return-individual-item';

class ReturnItemDetails extends React.Component {
  constructor(props) {
    // Adding default reason and default quantity values.
    super(props);
    this.state = {
      isChecked: false,
      returnReasons: getReturnConfigurationDetails(),
      reasonsDefault: getDefaultValueForReturnReasons(),
      defaultQtyOptions: getDefaultValueForQtyDropdown(),
      qtyOptions: populateQtyDropDownList(props.item.qty_ordered),
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

  /**
   * To handle on change activity for reasons select list.
   */
  handleReasonsSelect = (selectedOption) => {
    if (selectedOption.id && selectedOption.label) {
      this.setState({
        reasonsDefault: [{
          value: selectedOption.id,
          label: selectedOption.label,
        }],
      });
    }
  };

  /**
   * To handle on change activity for quantity select list.
   */
  handleQtySelect = (selectedOption) => {
    if (selectedOption.value && selectedOption.label) {
      this.setState({
        defaultQtyOptions: [{
          value: selectedOption.value,
          label: selectedOption.label,
        }],
      });
    }
  };

  render() {
    const {
      isChecked, returnReasons, reasonsDefault, defaultQtyOptions, qtyOptions,
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
          <div className="return-reasons-row">
            <div className="return-reason-label">{ Drupal.t('Reason for Return') }</div>
            <Select
              classNamePrefix="reasonsSelect"
              className="return-reasons-select"
              onChange={this.handleReasonsSelect}
              options={returnReasons}
              defaultValue={reasonsDefault}
              value={reasonsDefault}
            />
          </div>
          <div className="return-qty-row">
            <div className="return-reason-label">{ Drupal.t('Select quantity') }</div>
            <Select
              classNamePrefix="qtySelect"
              className="return-qty-select"
              onChange={this.handleQtySelect}
              options={qtyOptions}
              defaultValue={defaultQtyOptions}
              value={defaultQtyOptions}
            />
          </div>
        </ConditionalView>
      </div>
    );
  }
}

export default ReturnItemDetails;
