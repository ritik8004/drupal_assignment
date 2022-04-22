import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { getReturnInfo } from '../../../utilities/return_api_helper';
import { getDeliveryAddress, getPaymentDetails } from '../../../utilities/return_request_util';
import ReturnedItemsListing from '../returned-items-listing';
import RefundMethods from '../refund-methods';
import ReturnBasicInfo from '../return-basic-info';
import ReturnConfirmationAddress from '../return-confirmation-address';

class ReturnConfirmationDetails extends React.Component {
  constructor(props) {
    const { orderDetails } = props;
    super(props);
    this.state = {
      returnData: null,
      paymentInfo: getPaymentDetails(orderDetails),
      address: getDeliveryAddress(orderDetails),
    };
  }

  componentDidMount = () => {
    const { returnId } = this.props;
    showFullScreenLoader();
    // Get return response data via mdc api call.
    getReturnInfo(returnId).then((response) => {
      if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
        this.setState({
          returnData: response.data,
        });
      } else {
        logger.error('Error while calling return info api for @returnId', {
          '@returnId': returnId,
        });
      }
      removeFullScreenLoader();
    });
  };

  render() {
    const { returnData, paymentInfo, address } = this.state;
    if (!hasValue(returnData)) {
      return null;
    }
    return (
      <>
        <div className="return-details-section">
          <div className="return-details-label">
            <div className="return-details-title">{ Drupal.t('Return Details', {}, { context: 'online_returns' }) }</div>
          </div>
          <div className="return-confirmation-detail-wrapper">
            <ReturnBasicInfo returnData={returnData} />
            <RefundMethods paymentInfo={paymentInfo} />
            <ReturnConfirmationAddress shippingAddress={address} />
          </div>
          <div className="return-note-text">
            <div className="return-note-label">{ Drupal.t('Note - You can cancel this return order before 24hrs from the time of pick-up.', {}, { context: 'online_returns' }) }</div>
          </div>
        </div>
        <ReturnedItemsListing
          returnData={returnData}
        />
      </>
    );
  }
}

export default ReturnConfirmationDetails;
