import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import {
  getReturnWindowClosedMessage,
  getReturnWindowOpenMessage,
  isReturnWindowClosed,
  getReturnRequestUrl,
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
    } = this.props;

    if (!hasValue(orderId) || isReturnEligible === null) {
      return null;
    }

    if (isReturnWindowClosed(returnExpiration)) {
      document.querySelector('#online-returns-eligibility-window').classList.add('return-window-closed');
      return <ReturnWindow message={getReturnWindowClosedMessage(returnExpiration)} />;
    }

    // isReturnEligible checks if the order is eligible for ONLINE returns.
    if (isReturnEligible) {
      return (
        <>
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
            <ReturnAction handleOnClick={this.handleOnClick} />
          </div>
          <ReturnAtStore returnButtonclass="return-button-enabled" />
        </>
      );
    }

    if (orderType === 'ship_to_store') {
      return (
        <>
          <div className="eligibility-message-wrapper">
            <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
            <ReturnAction returnType="Click and Collect" />
          </div>
          <ReturnAtStore returnType="Click and Collect" />
        </>
      );
    }

    return (
      <>
        <div className="eligibility-message-wrapper">
          <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
          <ReturnAction returnType={paymentMethod} />
        </div>
        <ReturnAtStore returnType={paymentMethod} />
      </>
    );
  }
}

export default ReturnEligibilityMessage;
