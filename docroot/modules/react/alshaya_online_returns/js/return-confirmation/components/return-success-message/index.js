import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ReturnSuccessMessage = () => {
  const returnInStorage = Drupal.getItemFromLocalStorage('online_return_id');
  if (hasValue(returnInStorage)) {
    Drupal.removeItemFromLocalStorage('online_return_id');
    return (
      <div className="refund-success-message">
        <div className="message-text">
          { Drupal.t('Return request has been successfully placed.', {}, { context: 'online_returns' }) }
        </div>
      </div>
    );
  }
  return null;
};

export default ReturnSuccessMessage;
