import React from 'react';

const IctDeliveryInformation = (props) => {
  const { deliveryMethod, date } = props;

  // Check if the delivery method is HD.
  if (deliveryMethod === 'home_delivery') {
    return (
      <>
        <div id="ict-delivery-info" className="ict-delivery-info">
          <label className="radio-sim radio-label">
            <span className="carrier-title">
              {/** @todo Use date from MDC */}
              {`${Drupal.t('Expected Delivery on', {}, { context: 'ict' })} ${date}`}
            </span>
          </label>
        </div>
      </>
    );
  }

  // Check if the delivery method is CNC.
  if (deliveryMethod === 'click_and_collect') {
    return (
      <>
        <div className="spc-delivery-ict-info">
          <div className="ict-info-label">
            {/** @todo Use date from MDC */}
            {`${Drupal.t('Available in store from', {}, { context: 'ict' })} ${date}`}
          </div>
        </div>
      </>
    );
  }
  return null;
};
export default IctDeliveryInformation;
