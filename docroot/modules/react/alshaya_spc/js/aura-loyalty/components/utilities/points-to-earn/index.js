import React from 'react';
import ToolTip from '../../../../utilities/tooltip';
import { getAllAuraStatus } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import getStringMessage from '../../../../utilities/strings';
import Loading from '../../../../utilities/loading';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import { getTooltipPointsOnHoldMsg } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';

const PointsToEarnMessage = (props) => {
  const { pointsToEarn, loyaltyStatus, wait } = props;
  const allAuraStatus = getAllAuraStatus();
  console.log(loyaltyStatus)
  // Guest User & No card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
    || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
    return (
      <span className="spc-aura-points-to-earn">
        {getStringMessage(
          'cart_to_earn_with_points',
          { '@pts': pointsToEarn },
        )}
      </span>
    );
  }

  // Guest user & Linked card.
  if (!isUserAuthenticated()
    && (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED
    || loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED)) {
    const toEarnMessageP1 = `${getStringMessage('earn')} `;
    const pointsHighlight = `${pointsToEarn} ${getStringMessage('aura')} ${getStringMessage('points')}`;
    const toEarnMessageP2 = ` ${getStringMessage('cart_with_this_purchase')}`;

    return (
      <>
        <AuraHeaderIcon />
        <span className="spc-aura-points-to-earn">
          {toEarnMessageP1}
          <span className="spc-aura-highlight">{wait ? <Loading /> : pointsHighlight}</span>
          {toEarnMessageP2}
          <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
        </span>
      </>
    );
  }

  // Registered User & Linked card.
  if (isUserAuthenticated()
    && (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED
    || loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED)) {
    const toEarnMessage = `${getStringMessage('cart_page_aura_accrual_label')} `;
    const pointsHighlight = `${pointsToEarn} ${getStringMessage('pts')}`;
    return (
      <span className="spc-aura-points-to-earn">
        <span>{ toEarnMessage }</span>
        <span className="spc-aura-highlight">{ wait ? <Loading /> : pointsHighlight }</span>
      </span>
    );
  }

  // Registered User & UnLinked card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
    const toEarnMessageP1 = `${getStringMessage('our_members_will_earn')} `;
    const pointsHighlight = `${pointsToEarn} ${getStringMessage('points')}`;
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
