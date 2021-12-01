import React from 'react';

const AuraRedeemOrderSummaryItem = (props) => {
  const {
    pointsRedeemed,
    animationDelay: animationDelayValue,
  } = props;
  const label = Drupal.t('Aura points redeemed');

  if (Math.abs(pointsRedeemed) > 0) {
    return (
      <div className="spc-order-summary-item aura-order-summary-item fadeInUp redeem" style={{ animationDelay: animationDelayValue }}>
        <span className="spc-aura-label">{`${label}:`}</span>
        <span className="spc-aura-value always-ltr">
          {pointsRedeemed}
        </span>
      </div>
    );
  }
  return null;
};

export default AuraRedeemOrderSummaryItem;
