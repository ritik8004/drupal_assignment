import React from 'react';

const PendingFullEnrollment = () => (
  <div className="pending-full-enrollment-wrapper">
    <div className="aura-logo">
      AURA logo placeholder
    </div>
    <div className="pending-full-enrollment-description">
      <div className="title">
        { Drupal.t('Over 70 of the worlds best loved brands') }
      </div>
      <div className="description">
        <p>
          { Drupal.t('Congrats! You are now part of exclusive Aura rewards club. You will now earn points as you purchase AEO retail & online shops.') }
        </p>
        <p>
          { Drupal.t('To redeem your points online, we need you to provide us more details. Please download Aura app to complete your full enrollment.') }
        </p>
        <div className="app-store-links">
          <span> APP Store logo placeholder </span>
          <span> Google Store logo placeholder </span>
        </div>
      </div>
    </div>
  </div>
);

export default PendingFullEnrollment;
