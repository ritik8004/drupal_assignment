import React, { useEffect, useState } from 'react';

/**
 * Express delivery label component for product teaser.
 */
const ExpressDeliveryLabel = () => {
  // Set default state to show hide express delivery label.
  const [eDFlag, setEdFlag] = useState(window.sddEdStatus.expressDelivery);

  // Set express delivery label state with event from API call to MDC for
  // express delivery settings.
  const expressDeliveryLabelsDisplay = (e) => {
    const expressDeliveryStatus = e.detail;
    if (typeof expressDeliveryStatus !== 'undefined') {
      setEdFlag(expressDeliveryStatus.expressDelivery);
    }
  };

  useEffect(() => {
    // This event is dispatched from expressdeliveryHelper through SearchApp
    // The event detail has response from API call to magento to get express
    // delivery settings to show hide label on teaser.
    document.addEventListener('expressDeliveryLabelsDisplay', expressDeliveryLabelsDisplay, false);
    return () => {
      document.removeEventListener('expressDeliveryLabelsDisplay', expressDeliveryLabelsDisplay, false);
    };
  }, []);

  // If the express delivery flag is set to false then return null and
  // don't show the label on teaser.
  if (!eDFlag) {
    return (null);
  }

  return (
    <div className="express_delivery">
      {Drupal.t('Express Delivery', {}, { context: 'Express Delivery Tag' })}
    </div>
  );
};

export default ExpressDeliveryLabel;
