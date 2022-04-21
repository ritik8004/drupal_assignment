import React from 'react';
import ReturnOrderSummary from '../return-order-summary';
import ReturnItemsListing from '../return-items-listing';
import { getOrderDetailsForReturnRequest } from '../../../utilities/return_request_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnRefundDetails from '../return-refund-details';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import ErrorMessage from '../../../../../js/utilities/components/error-message';
import smoothScrollTo from '../../../../../js/utilities/components/smooth-scroll';

class ReturnRequest extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isReturnRequestSubmit: false,
      itemsSelected: [],
      errorMessage: '',
    };
  }

  componentDidMount() {
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
    const { isReturnRequestSubmit } = this.state;

    if (isReturnRequestSubmit) {
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
    const { itemsSelected, errorMessage } = this.state;
    const orderDetails = getOrderDetailsForReturnRequest();
    const { orderId } = orderDetails['#order'];
    if (!hasValue(orderDetails)) {
      return null;
    }

    return (
      <div className="return-requests-wrapper">
        <ConditionalView condition={hasValue(errorMessage)}>
          <ErrorMessage message={errorMessage} />
        </ConditionalView>
        <ReturnOrderSummary
          orderDetails={orderDetails}
        />
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
      </div>
    );
  }
}

export default ReturnRequest;
