import React from 'react';

const ReturnAmountWrapper = () => (
  <>
    <div className="refund-amount-wrapper">
      <div className="refund-amount-title">
        { Drupal.t('Refund Amount') }
      </div>
      <div className="refund-method-listing">
        { Drupal.t('You will be notified of your refund details once we receive the items in the warehouse.') }
      </div>
    </div>
  </>
);

export default ReturnAmountWrapper;
