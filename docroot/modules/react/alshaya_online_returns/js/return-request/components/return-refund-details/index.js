import React from 'react';
import Collapsible from 'react-collapsible';
import ReturnRefundMethod from '../return-refund-method';
import ReturnAmountWrapper from '../refund-amount-wrapper';
import ReturnCollectionDetails from '../return-collection-details';
import ReturnCollectionAddress from '../return-collection-address';
import {
  getDeliveryAddress,
  getPaymentDetails,
} from '../../../utilities/return_request_util';
import { createReturnRequest } from '../../../utilities/return_api_helper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getReturnConfirmationUrl, getOrderDetails, isReturnWindowClosed } from '../../../utilities/online_returns_util';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { getPreparedOrderGtm, getProductGtmInfo } from '../../../utilities/online_returns_gtm_util';

class ReturnRefundDetails extends React.Component {
  constructor(props) {
    const { orderDetails } = props;
    super(props);
    this.state = {
      address: getDeliveryAddress(orderDetails),
      paymentInfo: getPaymentDetails(orderDetails),
      open: false,
    };
  }

  componentDidMount = () => {
    document.addEventListener('updateRefundAccordionState', this.updateRefundAccordionState, false);
  };

  /**
   * Method to update react state of refund accordion.
   */
  updateRefundAccordionState = (event) => {
    this.setState({
      open: event.detail,
    });
  };

  /**
   * Display the refund details accordion trigger component.
   * On click of this component, refund details div will open.
   */
  refundDetailsHeader = () => (
    <div className="refund-detail-label">
      <div className="refund-detail-header">{ Drupal.t('2. Return and refund details', {}, { context: 'online_returns' }) }</div>
    </div>
  );

  /**
   * Create return request.
   */
  createReturnRequest = async () => {
    const { itemsSelected, handleErrorMessage, orderDetails } = this.props;

    showFullScreenLoader();

    // Return with error message if current order has expired for return.
    if (hasValue(orderDetails['#order'].returnExpiration)
      && isReturnWindowClosed(orderDetails['#order'].returnExpiration)) {
      handleErrorMessage('Sorry, something went wrong. Please try again later.');
      removeFullScreenLoader();
      return;
    }

    const returnRequest = await createReturnRequest(itemsSelected);
    removeFullScreenLoader();

    if (hasValue(returnRequest.error)) {
      handleErrorMessage(returnRequest.error_message);
      return;
    }

    if (hasValue(returnRequest.data) && hasValue(returnRequest.data.increment_id)) {
      const returnId = returnRequest.data.entity_id;
      Drupal.addItemInLocalStorage('online_return_id', returnId);
      // Push the required info to GTM.
      Drupal.alshayaSeoGtmPushReturn(
        getProductGtmInfo(itemsSelected),
        getPreparedOrderGtm('refunddetails_confirmed', returnRequest.data),
        'refunddetails_confirmed',
      );
      // On success, redirect to return confirmation page.
      const { orderId } = getOrderDetails()['#order'];
      const returnUrl = getReturnConfirmationUrl(orderId, returnId);
      if (hasValue(returnUrl)) {
        window.location.href = returnUrl;
      }
    }
  }

  /**
   * Process return request confirmation.
   */
  handleReturnConfirmation = () => {
    const { handleReturnRequestSubmit } = this.props;

    handleReturnRequestSubmit();
    this.createReturnRequest();
  }

  render() {
    const { paymentInfo, address, open } = this.state;
    return (
      <div className="refund-details-wrapper">
        <Collapsible trigger={this.refundDetailsHeader()} open={open} triggerDisabled={!open}>
          <ReturnRefundMethod paymentDetails={paymentInfo} />
          <ReturnAmountWrapper />
          <ReturnCollectionDetails />
          <ReturnCollectionAddress shippingAddress={address} />
          <div className="confirm-return-button-wrapper">
            <button
              type="button"
              onClick={this.handleReturnConfirmation}
            >
              <span className="continue-button-label">{Drupal.t('Confirm Your Return', {}, { context: 'online_returns' })}</span>
            </button>
          </div>
        </Collapsible>
      </div>
    );
  }
}

export default ReturnRefundDetails;
