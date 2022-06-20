import React from 'react';

const IctDeliveryInformation = (props) => {
  const { deliveryMethod, date } = props;

  if (deliveryMethod !== 'home_delivery'
    && deliveryMethod !== 'click_and_collect') {
    return null;
  }

  // Create text based on delivery method.
  const deliveryText = deliveryMethod === 'home_delivery'
    ? Drupal.t('Expected Delivery on', {}, { context: 'ict' })
    : Drupal.t('Available in store from', {}, { context: 'ict' });

  // return the common markup.
  return (
    <>
      <div className={`ict-delivery-info_${deliveryMethod}`}>
        {/** @todo Check if date formatting is required */}
        {`${deliveryText} ${date}`}
      </div>
    </>
  );
};
export default IctDeliveryInformation;
