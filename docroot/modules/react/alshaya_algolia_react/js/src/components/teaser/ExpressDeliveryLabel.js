import React, { useEffect, useState } from 'react';

const ExpressDeliveryLabel = () => {
  const [expressDeliveryFlag, setExpressDeliveryFlag] = useState(window.expressDeliveryLabel);
  const expressDeliveryLabelsDisplay = (e) => {
    const expressDeliveryStatus = e.detail;
    if (!expressDeliveryStatus) {
      setExpressDeliveryFlag(expressDeliveryStatus);
    }
  };

  useEffect(() => {
    document.addEventListener('expressDeliveryLabelsDisplay', expressDeliveryLabelsDisplay, false);
    return () => {
      document.removeEventListener('expressDeliveryLabelsDisplay', expressDeliveryLabelsDisplay, false);
    };
  }, []);

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
