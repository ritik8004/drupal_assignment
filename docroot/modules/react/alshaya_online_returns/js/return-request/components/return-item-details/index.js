import React from 'react';
import parse from 'html-react-parser';
import Select from 'react-select';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { getReturnsConfigurationDetails } from '../../../utilities/online_returns_util';

class ReturnItemDetails extends React.Component {
  constructor(props) {
    // Adding default reason and default quantity values.
    super(props);
    this.state = {
      isChecked: false,
      returnReasons: null,
      reasonsDefault: [{
        value: 0,
        label: Drupal.t('Choose a reason'),
      }],
      defaultQtyOptions: [{
        value: 1,
        label: 1,
      }],
    };
  }

  componentDidMount() {
    // Get returns configurations to fetch return reasons.
    const options = [];
    const response = getReturnsConfigurationDetails();
    if (response !== null && response.return_reasons) {
      // Populate options list for return reasons.
      response.return_reasons.forEach((reason) => {
        options.push({
          value: reason.id,
          label: reason.label,
        });
      });
      this.setState({
        returnReasons: options,
      });
    }
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
    if (selectedOption.value) {
      this.setState({
        reasonsDefault: [{
          value: selectedOption.value,
          label: selectedOption.label,
        }],
      });
    }
  };

  /**
   * To handle on change activity for quantity select list.
   */
  handleQtySelect = (selectedOption) => {
    if (selectedOption.value) {
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
      isChecked, returnReasons, reasonsDefault, defaultQtyOptions,
    } = this.state;
    const { item } = this.props;
    const qtyOptions = [];
    // Populate quanntity options for item quantities.
    for (let index = 1; index <= item.qty_ordered; index++) {
      qtyOptions.push({
        value: index,
        label: index,
      });
    }

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
          {item.image_data
              && (
              <div className="order-item-image">
                <div className="image-data-wrapper">
                  <img src={`${item.image_data.url}`} alt={`${item.image_data.alt}`} title={`${item.image_data.title}`} />
                </div>
              </div>
              )}
          <div className="order__details--summary order__details--description">
            <div className="item-name">{ item.name }</div>
            {item.attributes && Object.keys(item.attributes).map((attribute) => (
              <div key={item.attributes[attribute].label} className="attribute-detail">
                { item.attributes[attribute].label }
                :
                { item.attributes[attribute].value }
              </div>
            ))}
            <div className="item-code">
              {Drupal.t('Item code')}
              :
              { item.sku }
            </div>
            <div className="item-quantity">
              {Drupal.t('Quantity')}
              :
              { item.ordered }
            </div>
          </div>
          <div className="item-price">
            <div className="light">{Drupal.t('Unit Price')}</div>
            <span className="currency-code dark prefix">{ parse(item.price) }</span>
          </div>
        </div>
        <ConditionalView condition={isChecked}>
          <div className="return-reasons-row">
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
