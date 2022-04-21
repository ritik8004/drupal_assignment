import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getOrderDetailsForReturnConfirmation, getReturnIdFromUrl } from '../../../utilities/return_confirmation_util';
import OrderDetailsButton from '../order-details-button';
import ReturnSuccessMessage from '../return-success-message';
import WhatsNextSection from '../whats-next-section';

class ReturnConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returnId: getReturnIdFromUrl(),
    };
  }

  componentDidMount = () => {
    // @todo: Write logic to trigger get RMA info api.
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
        <ReturnSuccessMessage />
        <WhatsNextSection />
      </div>
    );
  }
}

export default ReturnConfirmation;
