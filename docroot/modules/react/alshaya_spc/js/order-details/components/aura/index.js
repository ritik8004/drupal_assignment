import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const Aura = (props) => {
  const { order } = props;

  return (
    <>
      <ConditionalView condition={order.auraEnabled === true}>
        <div className="above-mobile aura-points">
          <div className="dark points-earned">
            <span>{Drupal.t('Points Earned')}</span>
            <span>{order.apcAccruedPoints}</span>
          </div>
          <div className="dark points-redeemed">
            <span>{Drupal.t('Points Redeemed')}</span>
            <span>{order.apcRedeemedPoints}</span>
          </div>
        </div>
      </ConditionalView>
      <ConditionalView condition={order.auraEnabled === false}>
        <div className="above-mobile empty--cell">&nbsp;</div>
      </ConditionalView>
    </>
  );
};

export default Aura;
