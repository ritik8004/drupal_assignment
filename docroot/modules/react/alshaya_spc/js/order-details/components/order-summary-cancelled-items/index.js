import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const CancelledItems = (props) => {
  const { order } = props;
  if (!hasValue(order.cancelled_items_count)) {
    return null;
  }

  return (
    <div className="cancel-item">
      <a href="#cancelled-items" className="cancel-link">
        {Drupal.t('@count Cancelled', { '@count': order.cancelled_items_count }, {})}
      </a>
    </div>
  );
};

export default CancelledItems;
