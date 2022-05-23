import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import {
  getReturnWindowClosedMessage,
  getReturnWindowOpenMessage,
  isReturnWindowClosed,
  getReturnRequestUrl,
  ifOrderHasActiveReturns,
  getReturnWindowEligibleDateMessage,
  getReturnWindowNotActiveMessage,
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
    } = this.props;

    if (!hasValue(orderId) || isReturnEligible === null) {
      return null;
    }

    const returnInactiveMessage = getReturnWindowNotActiveMessage();
    if (hasValue(returns) && ifOrderHasActiveReturns(returns.returns)) {
      document.querySelector('#online-returns-eligibility-window').classList.add('return-window-closed');
      return (
        <div className="eligibility-window-container">
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowEligibleDateMessage(returnExpiration)} />
            <div className="return-inactive">{returnInactiveMessage}</div>
          </div>
          <ReturnAtStore returnButtonclass="return-button-enabled" />
        </div>
      );
    }

    if (isReturnWindowClosed(returnExpiration)) {
      document.querySelector('#online-returns-eligibility-window').classList.add('return-window-closed');
      return <ReturnWindow message={getReturnWindowClosedMessage(returnExpiration)} />;
    }

    // isReturnEligible checks if the order is eligible for ONLINE returns.
    if (isReturnEligible) {
      return (
        <div className="eligibility-window-container">
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
            <ReturnAction handleOnClick={this.handleOnClick} />
          </div>
          <ReturnAtStore returnButtonclass="return-button-enabled" />
        </div>
      );
    }

    if (orderType === 'ship_to_store') {
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
