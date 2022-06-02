import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const Aura = (props) => {
  const { order } = props;

  return (
    <>
      { hasValue(order.auraEnabled) && (
        <>
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
        </>
      )}

      { !hasValue(order.auraEnabled) && (
        <div className="above-mobile empty--cell">&nbsp;</div>
      )}
    </>
  );
};

export default Aura;
