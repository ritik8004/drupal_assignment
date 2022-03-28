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

    if (!hasValue(orderId)) {
      return null;
    }

    if (isReturnWindowClosed(returnExpiration)) {
      return <ReturnWindow message={getReturnWindowClosedMessage(returnExpiration)} />;
    }

    if (isReturnEligible) {
      return (
        <>
          <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
          <ReturnAction handleOnClick={this.handleOnClick} />
          <ReturnAtStore />
        </>
      );
    }

    if (orderType === 'ship_to_store') {
      return (
        <>
          <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
          <ReturnAction returnType="Click and Collect" />
          <ReturnAtStore returnType="Click and Collect" />
        </>
      );
    }

    return (
      <>
        <ReturnWindow message={getReturnWindowOpenMessage(returnExpiration)} />
        <ReturnAction returnType={paymentMethod} />
        <ReturnAtStore returnType={paymentMethod} />
      </>
    );
  }
}

export default ReturnEligibilityMessage;
