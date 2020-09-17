import React from 'react';

const CardNotLinkedNoData = () => (
  <div className="aura-card-not-linked-no-data-wrapper">
    <div className="aura-logo">
      AURA logo placeholder
    </div>
    <div className="aura-card-not-linked-no-data-description">
      <div className="link-your-card">
        { Drupal.t('Already in Loyalty Club') }
        <a href="">
          { Drupal.t('LINK YOUR CARD NOW') }
        </a>
      </div>
      <div className="sign-up">
        { Drupal.t('Ready to be Rewarded') }
        <a href="">
          { Drupal.t('SIGN UP') }
        </a>
      </div>
    </div>
  </div>
);

export default CardNotLinkedNoData;
