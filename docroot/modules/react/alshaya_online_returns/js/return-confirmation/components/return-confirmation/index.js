import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import getOrderDetailsForReturnConfirmation from '../../../utilities/return_confirmation_util';
import OrderDetailsButton from '../order-details-button';
import ReturnSuccessMessage from '../return-success-message';

class ReturnConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      // @todo to add state variables.
    };
  }

  componentDidMount = () => {
    // @todo logic to trigger get RMA info api.
  };

  render() {
    const orderDetails = getOrderDetailsForReturnConfirmation();
    if (!hasValue(orderDetails)) {
      return null;
    }
    return (
      <div className="return-confirmation-wrapper">
        <OrderDetailsButton
          orderId={orderDetails['#order'].orderId}
        />
        <ReturnSuccessMessage />

      </div>
    );
  }
}

export default ReturnConfirmation;
