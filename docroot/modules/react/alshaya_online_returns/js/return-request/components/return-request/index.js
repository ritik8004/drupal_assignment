import React from 'react';
import ReturnOrderSummary from '../return-order-summary';
import ReturnItemsListing from '../return-items-listing';
import { getOrderDetailsForReturnRequest } from '../../../utilities/return_request_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnRefundDetails from '../return-refund-details';

class ReturnRequest extends React.Component {
  componentDidMount() {
    window.addEventListener('beforeunload', this.warnUser, false);
    window.addEventListener('pagehide', this.warnUser, false);
  }

  componentWillUnmount() {
    window.removeEventListener('beforeunload', this.warnUser, false);
    window.removeEventListener('pagehide', this.warnUser, false);
  }

  warnUser = (e) => {
    e.preventDefault();
    const confirmationMessage = Drupal.t(
      "If you're trying to leave the Online Returns page, please note, any changes made will be lost.",
      {},
      { context: 'online_returns' },
    );

    e.returnValue = confirmationMessage;
    return confirmationMessage;
  };

  render() {
    const orderDetails = getOrderDetailsForReturnRequest();
    if (!hasValue(orderDetails)) {
      return null;
    }

    return (
      <div className="return-requests-wrapper">
        <ReturnOrderSummary
          orderDetails={orderDetails}
        />
        <ReturnItemsListing
          products={orderDetails['#products']}
        />
        <ReturnRefundDetails
          orderDetails={orderDetails}
        />
      </div>
    );
  }
}

export default ReturnRequest;
