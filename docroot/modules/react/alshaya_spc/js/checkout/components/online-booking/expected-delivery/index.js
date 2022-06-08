import React from 'react';

const ExpectedDelivery = () => (
  <>
    <div id="ict-expected-delivery" className="ict-expected-delivery">
      <label className="radio-sim radio-label">
        <span className="carrier-title">
          {/** @todo Use date from MDC */}
          { Drupal.t('Expected Delivery on 29th May 2022', {}, { context: 'ict' }) }
        </span>
      </label>
    </div>
  </>
);
export default ExpectedDelivery;
