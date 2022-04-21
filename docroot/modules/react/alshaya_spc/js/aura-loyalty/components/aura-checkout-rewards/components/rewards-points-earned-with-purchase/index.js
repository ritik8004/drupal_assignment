import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import ToolTip from '../../../../../utilities/tooltip';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraHeaderIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import { isDesktop } from '../../../../../../../js/utilities/display';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';

const AuraPointsToEarnedWithPurchase = (props) => {
  const {
    pointsToEarn,
  } = props;

  return (
    <>
      <div className="block-content points-to-earn-with-the-purchase">
        <div className="points-to-earn-text">
          <ConditionalView condition={isDesktop()}>
            {parse(parse(getStringMessage('aura_checkout_reward_points_to_earn_desktop', {
              '@pts': `<span class="points-to-earn-count">${pointsToEarn}</span>`,
              '@aura_icon': `<span class="join-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
            })))}
          </ConditionalView>
          <ConditionalView condition={!isDesktop()}>
            {parse(parse(getStringMessage('aura_checkout_reward_points_to_earn_mobile', {
              '@pts': `<span class="points-to-earn-count">${pointsToEarn}</span>`,
              '@aura_icon': `<span class="join-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
            })))}
          </ConditionalView>
          <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
        </div>
      </div>
    </>
  );
};

export default AuraPointsToEarnedWithPurchase;
