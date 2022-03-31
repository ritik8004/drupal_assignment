import React from 'react';
import ReturnRefundMethod from '../return-refund-method';
import ReturnAmountWrapper from '../refund-amount-wrapper';
import ReturnCollectionDetails from '../return-collection-details';
import ReturnCollectionAddress from '../return-collection-address';
import { getDeliveryAddress, getPaymentDetails } from '../../../utilities/return_request_util';

class ReturnRefundDetails extends React.Component {
  constructor(props) {
    const { orderDetails } = props;
    super(props);
    this.state = {
      address: getDeliveryAddress(orderDetails),
      paymentInfo: getPaymentDetails(orderDetails),
    };
  }

  render() {
    const { paymentInfo, address } = this.state;
    return (
      <div className="refund-details-wrapper">
        <div className="refund-detail-label">{ Drupal.t('Return and refund details') }</div>
        <ReturnRefundMethod
          paymentDetails={paymentInfo}
        />
        <ReturnAmountWrapper />
        <ReturnCollectionDetails />
        <ReturnCollectionAddress
          shippingAddress={address}
        />
      </div>
    );
  }
}

export default ReturnRefundDetails;
