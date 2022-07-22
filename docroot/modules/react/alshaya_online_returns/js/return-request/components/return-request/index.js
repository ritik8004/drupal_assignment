import React from 'react';
import parse from 'html-react-parser';
import ReturnOrderSummary from '../return-order-summary';
import ReturnItemsListing from '../return-items-listing';
import { getOrderDetails, getOrderDetailsUrl } from '../../../utilities/online_returns_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnRefundDetails from '../return-refund-details';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import ErrorMessage from '../../../../../js/utilities/components/error-message';
import smoothScrollTo from '../../../../../js/utilities/components/smooth-scroll';
import { validateReturnRequest } from '../../../utilities/return_api_helper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';

class ReturnRequest extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isReturnRequestSubmit: false,
      itemsSelected: [],
      errorMessage: '',
      orderDetails: getOrderDetails(),
      wait: true,
    };
  }

  componentDidMount() {
    const { orderDetails } = this.state;
    showFullScreenLoader();
    // Validating if current order applicable for return.
    validateReturnRequest(orderDetails).then((validateRequest) => {
      // If return request invalid, redirect to order details page.
      if (!validateRequest) {
        const orderDetailsUrl = getOrderDetailsUrl(orderDetails['#order'].orderId);
        if (hasValue(orderDetailsUrl)) {
          window.location.href = orderDetailsUrl;
        }
        return;
      }
      this.setState({
        wait: false,
      });

      removeFullScreenLoader();
    });

    window.addEventListener('beforeunload', this.warnUser, false);
    window.addEventListener('pagehide', this.warnUser, false);
  }

  componentWillUnmount() {
    window.removeEventListener('beforeunload', this.warnUser, false);
    window.removeEventListener('pagehide', this.warnUser, false);
  }

  handleReturnRequestSubmit = () => {
    this.setState({
      isReturnRequestSubmit: true,
      errorMessage: '',
    });
  }

  handleSelectedItems = (itemsSelected) => {
    this.setState({
      itemsSelected,
    });
  }

  warnUser = (e) => {
    const { isReturnRequestSubmit, wait } = this.state;
    if (isReturnRequestSubmit || wait) {
      return null;
    }

    e.preventDefault();
    const confirmationMessage = Drupal.t(
      "If you're trying to leave the Online Returns page, please note, any changes made will be lost.",
      {},
      { context: 'online_returns' },
    );

    e.returnValue = confirmationMessage;
    return confirmationMessage;
  };

  handleErrorMessage = (errorMessage) => {
    if (hasValue(errorMessage)) {
      this.setState({ errorMessage });
      if (document.getElementsByClassName('return-requests-wrapper').length > 0) {
        smoothScrollTo('.return-requests-wrapper');
      }
    }
  };

  render() {
    const {
      itemsSelected, errorMessage, orderDetails, wait,
    } = this.state;
    const { helperBlock } = drupalSettings.returnInfo;
    const { orderId } = orderDetails['#order'];
    if (!hasValue(orderDetails) || wait) {
      return null;
    }

    return (
      <div className="return-requests-wrapper">
        <ReturnOrderSummary
          orderDetails={orderDetails}
        />
        <ConditionalView condition={hasValue(errorMessage)}>
          <ErrorMessage message={errorMessage} />
        </ConditionalView>
        <ReturnItemsListing
          products={orderDetails['#products']}
          handleSelectedItems={this.handleSelectedItems}
          itemsSelected={itemsSelected}
        />
        <ReturnRefundDetails
          orderDetails={orderDetails}
          handleReturnRequestSubmit={this.handleReturnRequestSubmit}
          itemsSelected={itemsSelected}
          handleErrorMessage={this.handleErrorMessage}
        />
        <div className="return-request-bottom-page-link">
          <span>{ Drupal.t("Don't want to return?", {}, { context: 'online_returns' }) }</span>
          <span>
            <a href={Drupal.url(`user/${drupalSettings.user.uid}/order/${orderId}`)}>
              { Drupal.t('Go back to Order Details', {}, { context: 'online_returns' }) }
            </a>
          </span>
        </div>
        { helperBlock && (
          <div className="helper-block-wrapper">{ parse(helperBlock) }</div>
        )}
      </div>
    );
  }
}

export default ReturnRequest;
