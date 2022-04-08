import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getOrderDetailsForReturnConfirmation, getReturnIdFromUrl } from '../../../utilities/return_confirmation_util';
import OrderDetailsButton from '../order-details-button';
import ReturnSuccessMessage from '../return-success-message';

class ReturnConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returnId: getReturnIdFromUrl(),
    };
  }

  componentDidMount = () => {
    const { returnId } = this.state;
    // Adding return id in storage for confirmation message.
    if (hasValue(returnId)) {
      Drupal.addItemInLocalStorage('online_return_id', returnId);
    }
    // @todo logic to trigger get RMA info api.
  };

  render() {
    const { returnId } = this.state;
    const orderDetails = getOrderDetailsForReturnConfirmation();
    if (!hasValue(orderDetails) || !hasValue(returnId)) {
      return null;
    }
    return (
      <div className="return-confirmation-wrapper">
        <OrderDetailsButton
          orderId={orderDetails['#order'].orderId}
        />
        <ReturnSuccessMessage
          returnId={returnId}
        />
      </div>
    );
  }
}

export default ReturnConfirmation;
