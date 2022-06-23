import React from 'react';
import moment from 'moment';

const IctDeliveryInformation = (props) => {
  const { deliveryMethod, date } = props;

  // Check if delivery method in HD and CNC.
  if (deliveryMethod !== 'home_delivery'
    && deliveryMethod !== 'click_and_collect') {
    return null;
  }

  // Check if the date is empty.
  if (!date) {
    return null;
  }

  // Create text based on delivery method.
  const deliveryText = deliveryMethod === 'home_delivery'
    ? Drupal.t('Expected Delivery on', {}, { context: 'ict' })
    : Drupal.t('Available in store from', {}, { context: 'ict' });

  // return the common markup.
  return (
    <div className={`ict-delivery-info_${deliveryMethod}`}>
      {`${deliveryText}
       ${moment(date).format('Do')} 
       ${moment(date).locale(drupalSettings.path.currentLanguage).format('MMM')} 
       ${moment().format('YYYY')}`}
    </div>
  );
};
export default IctDeliveryInformation;
