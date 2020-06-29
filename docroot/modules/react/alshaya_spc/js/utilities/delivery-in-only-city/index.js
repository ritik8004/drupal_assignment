import React from 'react';

const DeliveryInOnlyCity = () => {
  const { delivery_in_only_city_text: DeliveryInOnlyCityText } = window.drupalSettings.alshaya_spc;
  if (DeliveryInOnlyCityText !== undefined) {
    return <div className="delivery-in-only-city-text fadeInUp" style={{ animationDelay: '0.4s' }}>{DeliveryInOnlyCityText}</div>;
  }

  return (null);
};

export default DeliveryInOnlyCity;
