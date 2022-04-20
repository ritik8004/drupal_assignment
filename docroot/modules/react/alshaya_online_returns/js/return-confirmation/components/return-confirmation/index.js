import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getOrderDetailsUrl } from '../../../utilities/online_returns_util';
import { getOrderDetailsForReturnConfirmation, getReturnIdFromUrl } from '../../../utilities/return_confirmation_util';
import OrderDetailsButton from '../order-details-button';
import ReturnDetailsSection from '../return-details-section';
import ReturnSuccessMessage from '../return-success-message';
import WhatsNextSection from '../whats-next-section';

class ReturnConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returnId: getReturnIdFromUrl(),
      orderDetails: getOrderDetailsForReturnConfirmation(),
    };
  }

  componentDidMount = () => {
    const { returnId, orderDetails } = this.state;
    if (!hasValue(returnId)) {
      const orderDetailsUrl = getOrderDetailsUrl(orderDetails['#order'].orderId);
      if (hasValue(orderDetailsUrl)) {
        window.location.href = orderDetailsUrl;
      }
    }
  };

  render() {
    const { returnId, orderDetails } = this.state;
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
        <ReturnDetailsSection
          orderDetails={orderDetails}
          returnId={returnId}
        />
      </div>
    );
  }
}

export default ReturnConfirmation;
