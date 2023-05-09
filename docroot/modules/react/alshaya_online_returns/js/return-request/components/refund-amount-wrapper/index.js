import React from 'react';

const ReturnAmountWrapper = () => (
  <>
    <div className="refund-amount-wrapper">
      <div className="refund-amount-title">
        { Drupal.t('Refund Amount', {}, { context: 'online_returns' }) }
      </div>
      <div className="refund-method-listing">
        <div className="refund-amount-message">
          { Drupal.t('Your refund amount will be notified to you through mail once we receive the items in warehouse', {}, { context: 'online_returns' }) }
        </div>
      </div>
    </div>
  </>
);

export default ReturnAmountWrapper;
