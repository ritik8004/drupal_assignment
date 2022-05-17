import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const CancelledItems = (props) => {
  const { order } = props;

  return (
    <>
      <ConditionalView condition={order.cancelled_items_count > 0}>
        <div className="cancel-item">
          <a href="#cancelled-items" className="cancel-link">
            {Drupal.t('@count Cancelled', { '@count': order.cancelled_items_count }, {})}
          </a>
        </div>
      </ConditionalView>
    </>
  );
};

export default CancelledItems;
