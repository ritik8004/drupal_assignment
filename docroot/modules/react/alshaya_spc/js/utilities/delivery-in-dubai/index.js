import React from 'react';

const DeliveryInDubai = () => {
  const { delivery_in_dubai_text: DeliveryInDubaiText } = window.drupalSettings.alshaya_spc;
  if (DeliveryInDubaiText !== undefined) {
    return <span className="delivery-in-dubai-text fadeInUp" style={{ animationDelay: '0.4s' }}>{DeliveryInDubaiText}</span>;
  }

  return (null);
};

export default DeliveryInDubai;
