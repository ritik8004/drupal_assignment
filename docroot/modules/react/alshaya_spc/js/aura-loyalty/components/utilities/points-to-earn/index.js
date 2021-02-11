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
    const toEarnMessageP1 = `${getStringMessage('earn')} `;
    const pointsHighlight = `${points} ${getStringMessage('aura')}`;
    const toEarnMessageP2 = ` ${getStringMessage('reward_points_with_purchase')}`;

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
    const pointsHighlight = `${points} ${getStringMessage('pts')}`;
    return (
      <span className="spc-aura-points-to-earn">
        <span>{ toEarnMessage }</span>
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
      </span>
    );
  }

  // Registered User & UnLinked card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
    const toEarnMessageP1 = `${getStringMessage('our_members_will_earn')} `;
    const pointsHighlight = `${points} ${getStringMessage('points')}`;
    const toEarnMessageP2 = ` ${getStringMessage('checkout_with_this_purchase')}`;

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
