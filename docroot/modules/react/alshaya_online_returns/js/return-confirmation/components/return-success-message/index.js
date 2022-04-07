import React from 'react';

const ReturnSuccessMessage = () => (
  <>
    <div className="refund-success-message">
      <span className="message-text">
        { Drupal.t('Return request has been successfully placed.', {}, { context: 'online_returns' }) }
      </span>
    </div>
  </>
);

export default ReturnSuccessMessage;
