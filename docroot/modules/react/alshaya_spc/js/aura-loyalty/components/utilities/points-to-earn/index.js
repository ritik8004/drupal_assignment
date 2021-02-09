import React from 'react';
import { getAllAuraStatus } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { getPriceToPoint } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';

const PointsToEarnMessage = (props) => {
  const { price, loyaltyStatus } = props;
  const allAuraStatus = getAllAuraStatus();
  const points = getPriceToPoint(price);

  // Guest User & No card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
    || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
    const toEarnMessageP1 = `${Drupal.t('Earn')} `;
    const pointsHighlight = `${points} ${Drupal.t('Aura')}`;
    const toEarnMessageP2 = ` ${Drupal.t('reward points with this purchase')}`;

    return (
      <span className="spc-aura-points-to-earn">
        { toEarnMessageP1 }
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
        { toEarnMessageP2 }
      </span>
    );
  }

  // Registered User & Linked card.
  if (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED
    || loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED) {
    const toEarnMessage = `${getStringMessage('cart_page_aura_accrual_label')} `;
    const pointsHighlight = `${points} ${Drupal.t('pts')}`;
    return (
      <span className="spc-aura-points-to-earn">
        <span>{ toEarnMessage }</span>
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
      </span>
    );
  }

  // Registered User & UnLinked card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
    const toEarnMessageP1 = `${Drupal.t('Our members will earn')} `;
    const pointsHighlight = `${points} ${Drupal.t('points')}`;
    const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;

    return (
      <span className="spc-aura-points-to-earn">
        { toEarnMessageP1 }
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
        { toEarnMessageP2 }
      </span>
    );
  }

  return (
    <span className="spc-aura-points-to-earn" />
  );
};

export default PointsToEarnMessage;
