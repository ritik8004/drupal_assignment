import React from 'react';
import PendingEnrollmentMessage from '../utilities/pending-enrollment-message';
import ToolTip from '../../../utilities/tooltip';
import ConditionalView from '../../../common/components/conditional-view';
import { getAllAuraStatus } from '../../../../../alshaya_aura_react/js/utilities/helper';
import { getTooltipPointsOnHoldMsg } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';

const AuraEarnOrderSummaryItem = (props) => {
  const {
    pointsEarned,
    animationDelay: animationDelayValue,
    context,
    loyaltyStatus,
  } = props;

  const label = Drupal.t('Aura points earned');

  const userFullyEnrolled = (loyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED);

  if (pointsEarned > 0) {
    return (
      <>
        <div className="spc-order-summary-item aura-order-summary-item fadeInUp earn" style={{ animationDelay: animationDelayValue }}>
          <span className="spc-aura-label">{`${label}:`}</span>
          <span className="spc-aura-value">
            <span className="always-ltr">{`+${pointsEarned}`}</span>
            {context !== 'print'
            && (
            <ToolTip enable question>
              {getTooltipPointsOnHoldMsg()}
            </ToolTip>
            )}
          </span>
        </div>
        <ConditionalView condition={userFullyEnrolled}>
          <PendingEnrollmentMessage />
        </ConditionalView>
      </>
    );
  }

  return null;
};

export default AuraEarnOrderSummaryItem;
