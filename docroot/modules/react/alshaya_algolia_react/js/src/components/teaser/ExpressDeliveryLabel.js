import React, { useEffect, useState } from 'react';

/**
 * Express delivery label component for product teaser.
 */
const ExpressDeliveryLabel = () => {
  // Set default state to show hide express delivery label.
  const [expressDeliveryFlag, setExpressDeliveryFlag] = useState(window.expressDeliveryLabel);

  // Set express delivery label state with event from API call to MDC for
  // express delivery settings.
  const expressDeliveryLabelsDisplay = (e) => {
    const expressDeliveryStatus = e.detail;
    if (!expressDeliveryStatus) {
      setExpressDeliveryFlag(expressDeliveryStatus);
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
  if (!expressDeliveryFlag) {
    return (null);
  }

  return (expressDeliveryFlag
    && (
    <div className="express_delivery">
      {Drupal.t('Express Delivery', {}, { context: 'Express Delivery Tag' })}
    </div>
    )
  );
};

export default ExpressDeliveryLabel;
