import React from 'react';
import ReturnItemDetails from '../return-item-details';
import { createReturnRequest } from '../../../utilities/return_api_helper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

class ReturnItemsListing extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      btnDisabled: true,
      itemsSelected: [],
    };
    this.handleSelectedReason = this.handleSelectedReason.bind(this);
    this.processSelectedItems = this.processSelectedItems.bind(this);
  }

  /**
   * If any reason is selected by customer
   * continue button will be enabled.
   */
  handleSelectedReason = (selectedReason) => {
    if (selectedReason) {
      this.setState({
        btnDisabled: selectedReason.value === 0,
      });
    }
  }

  /**
   * Capturing selected items and enabling/disabling button
   * as per item selection.
   */
  processSelectedItems = (checked, item) => {
    if (checked) {
      this.setState((prevState) => ({
        itemsSelected: [...prevState.itemsSelected, item],
        btnDisabled: true,
      }));
    } else {
      this.setState((prevState) => ({
        itemsSelected: prevState.itemsSelected.filter((product) => product.sku !== item.sku),
      }));
    }
  }

  /**
   * Process return request submit.
   */
  handleReturnSubmit = () => {
    const { btnDisabled } = this.state;

    if (!btnDisabled) {
      this.createReturnRequest();
    }
  }

  /**
   * Create return request.
   */
  createReturnRequest = async () => {
    const { itemsSelected } = this.state;

    // @todo: Hard coding selected reasons and quantity for now.
    itemsSelected.map((item) => {
      const data = { ...item };
      data.qty_requested = 2;
      data.resolution = 2009;
      data.reason = 2014;
      return data;
    });

    const requestData = {
      uid: drupalSettings.user.uid,
      itemsSelected,
    };
    const returnRequest = await createReturnRequest(requestData);

    if (hasValue(returnRequest.error)) {
      // @todo: Handle error display.
      return;
    }

    // On success, redirect to return confirmation page.
    // @todo: Update return confirmation URL.
    window.location.href = Drupal.url('/');
  }

  render() {
    const { btnDisabled, itemsSelected } = this.state;
    const { products } = this.props;
    // If no item is selected, button remains disabled.
    const btnState = !!((itemsSelected.length === 0 || btnDisabled));
    return (
      <div className="products-list-wrapper">
        <div className="select-items-label">
          <div className="select-items-header">{ Drupal.t('1. Select items to return', {}, { context: 'online_returns' }) }</div>
        </div>
        {products.map((item) => (
          <div key={item.sku} className="item-list-wrapper">
            <ReturnItemDetails
              item={item}
              handleSelectedReason={this.handleSelectedReason}
              processSelectedItems={this.processSelectedItems}
            />
          </div>
        ))}
        <div className="continue-button-wrapper">
          <button
            type="button"
            disabled={btnState}
            onClick={this.handleReturnSubmit}
          >
            <span className="continue-button-label">{Drupal.t('Continue', {}, { context: 'online_returns' })}</span>
          </button>
        </div>
      </div>
    );
  }
}

export default ReturnItemsListing;
