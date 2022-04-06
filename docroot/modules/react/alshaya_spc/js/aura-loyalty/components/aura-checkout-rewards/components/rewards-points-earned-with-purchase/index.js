import React from 'react';
import ToolTip from '../../../../../utilities/tooltip';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraHeaderIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';

const AuraPointsToEarnedWithPurchase = (props) => {
  const {
    pointsToEarn,
  } = props;

  return (
    <>
      <div className="block-content points-to-earn-with-the-purchase">
        <div className="points-to-earn-text">
          <span>{ getStringMessage('checkout_you_will_earn') }</span>
          <span>{pointsToEarn}</span>
          <span className="join-aura"><AuraHeaderIcon /></span>
          <span>
            { getStringMessage('points_to_earn_with_purchase') }
          </span>
          <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
        </div>
      </div>
    </>
  );
};

export default AuraPointsToEarnedWithPurchase;
