import Cleave from 'cleave.js/react';
import React from 'react';
import AuraAppLinks from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/aura-app-links';
import { isMobile } from '../../../../../../js/utilities/display';
import AuraLogo from '../../../../svg-component/aura-logo';
import { isMyAuraContext } from '../../../../utilities/aura_utils';
import AuraAppDownload from '../../../aura-app-download';
import AuraProgressWrapper from '../../../aura-progress';
import MyAuraBanner from '../my-aura-banner';

const MyAuraPendingFullEnrollmentRegistered = (props) => {
  const {
    cardNumber,
    tier,
    points,
    pointsOnHold,
    firstName,
    lastName,
    loyaltyStatusInt,
    upgradeMsg,
    expiringPoints,
    expiryDate,
  } = props;

  const tierClass = tier || 'no-tier';

  if (isMyAuraContext()) {
    return (
      <>
        <MyAuraBanner
          tier={tier}
          points={points}
          pointsOnHold={pointsOnHold}
          cardNumber={cardNumber}
          firstName={firstName}
          lastName={lastName}
          loyaltyStatusInt={loyaltyStatusInt}
        />
        <AuraProgressWrapper
          upgradeMsg={upgradeMsg}
          expiringPoints={expiringPoints}
          expiryDate={expiryDate}
          tier={tier}
        />
      </>
    );
  }

  return (
    <div className={`aura-pending-full-enrollment-wrapper fadeInUp aura-level-${tierClass}`}>
      <div className="aura-logo">
        <AuraLogo />
      </div>
      <div className="card-number-wrapper">
        <div className="card-number-label">
          {Drupal.t('Aura membership number', {}, { context: 'aura' })}
        </div>
        <Cleave
          name="aura-my-account-link-card"
          className="aura-my-account-link-card"
          disabled
          value={cardNumber}
          options={{ blocks: [4, 4, 4, 4] }}
        />
      </div>
      <div className="pending-full-enrollment-description">
        <AuraAppDownload />

        {isMobile() && (
          <AuraAppLinks />
        )}
      </div>
    </div>
  );
};

export default MyAuraPendingFullEnrollmentRegistered;
