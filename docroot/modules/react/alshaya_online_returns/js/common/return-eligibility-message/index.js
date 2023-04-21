import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import {
  getReturnWindowClosedMessage,
  getReturnWindowOpenMessage,
  isReturnWindowClosed,
  getReturnRequestUrl,
  hasActiveReturns,
  getReturnWindowEligibleDateMessage,
  getCustomerServiceNumber,
} from '../../utilities/online_returns_util';
import ReturnAction from '../return-action';
import ReturnAtStore from '../return-at-store';
import ReturnWindow from '../return-window';

class ReturnEligibilityMessage extends React.Component {
  handleOnClick = () => {
    const { orderId } = this.props;
    window.location.href = getReturnRequestUrl(orderId);
  }

  render() {
    const {
      orderId,
      isReturnEligible,
      returnExpiration,
      paymentMethod,
      orderType,
      returns,
      isBigTicketOrder,
    } = this.props;

    // Get customer service number, used as point of contact to return the orders
    // not available for online returns like orders that have big ticket or
    // white glove delivery items.
    const customerServiceNumber = getCustomerServiceNumber();

    if (!hasValue(orderId) || isReturnEligible === null || !hasValue(returnExpiration)) {
      return null;
    }

    // If the order contains atleast one big ticket item and return window is not
    // closed don't render ReturnAction and ReturnAtStore components.
    if (hasValue(isBigTicketOrder) && !isReturnWindowClosed(returnExpiration)) {
      return (
        <div className="eligibility-window-container">
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowEligibleDateMessage(returnExpiration)} />
            <div className="big-ticket-order">
              {`(${Drupal.t('To return this order, please contact our customer service on @contact_number', { '@contact_number': customerServiceNumber }, { context: 'online_returns' })})`}
            </div>
          </div>
        </div>
      );
    }

    if (hasValue(returns) && hasActiveReturns(returns.returns)) {
      return (
        <div className="eligibility-window-container">
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowEligibleDateMessage(returnExpiration)} />
            <div className="return-inactive">
              {Drupal.t('Online returns can be placed once existing returns are processed.', {}, { context: 'online_returns' })}
            </div>
          </div>
          <ReturnAtStore returnButtonclass="return-button-enabled" />
        </div>
      );
    }

    if (isReturnWindowClosed(returnExpiration)) {
      return <ReturnWindow message={getReturnWindowClosedMessage(returnExpiration)} closed />;
    }

    // isReturnEligible checks if the order is eligible for ONLINE returns.
    if (isReturnEligible) {
      return (
        <div className="eligibility-window-container">
          <div className="eligibility-message-wrapper eligibility-button">
            <span className="return-icon" />
            <div className="return-button-message-wrapper">
              <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
              <ReturnAction handleOnClick={this.handleOnClick} />
            </div>
          </div>
          <ReturnAtStore returnButtonclass="return-button-enabled" />
        </div>
      );
    }

    if (orderType === 'cc') {
      return (
        <div className="eligibility-window-container">
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
            <ReturnAction returnType="Click and Collect" />
          </div>
          <ReturnAtStore returnType="Click and Collect" />
        </div>
      );
    }

    return (
      <div className="eligibility-window-container">
        <div className="eligibility-message-wrapper">
          <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
          <ReturnAction returnType={paymentMethod} />
        </div>
        <ReturnAtStore returnType={paymentMethod} />
      </div>
    );
  }
}

export default ReturnEligibilityMessage;
