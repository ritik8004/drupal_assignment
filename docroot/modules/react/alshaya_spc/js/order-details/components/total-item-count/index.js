import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const TotalItemCount = (props) => {
  const { order } = props;

  return (
    <>
      <ConditionalView condition={order.quantity === 1}>
        {Drupal.t('Total: @count item', { '@count': order.quantity }, {})}
      </ConditionalView>
      <ConditionalView condition={order.quantity > 1}>
        {Drupal.t('Total: @count items', { '@count': order.quantity }, {})}
      </ConditionalView>
    </>
  );
};

export default TotalItemCount;
