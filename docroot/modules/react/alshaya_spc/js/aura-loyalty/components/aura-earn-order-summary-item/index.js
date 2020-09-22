import React from 'react';
import PendingEnrollmentMessage from '../utilities/pending-enrollment-message';
import ToolTip from '../../../utilities/tooltip';
import ConditionalView from '../../../common/components/conditional-view';

const AuraEarnOrderSummaryItem = (props) => {
  const {
    pointsEarned,
    fullEnrollment,
    animationDelay: animationDelayValue,
  } = props;

  const label = Drupal.t('Aura points earned');
  const tooltip = Drupal.t('Your points will be credited to your account but will be on-hold status until the return period of 14 days. After that you will be able to redeem  the points.');

  if (pointsEarned > 0) {
    return (
      <>
        <div className="spc-order-summary-item aura-order-summary-item fadeInUp earn" style={{ animationDelay: animationDelayValue }}>
          <span className="spc-aura-label">{`${label}:`}</span>
          <span className="spc-aura-value">
            {`+${pointsEarned}`}
            <ToolTip enable question>{ tooltip }</ToolTip>
          </span>
        </div>
        <ConditionalView condition={fullEnrollment === 'no'}>
          <PendingEnrollmentMessage />
        </ConditionalView>
      </>
    );
  }

  return null;
};

export default AuraEarnOrderSummaryItem;
