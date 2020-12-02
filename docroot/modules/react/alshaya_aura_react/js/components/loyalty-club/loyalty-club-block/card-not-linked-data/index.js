import React from 'react';
import Cleave from 'cleave.js/react';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraLogo from '../../../../svg-component/aura-logo';
import { handleNotYou, handleLinkYourCard } from '../../../../utilities/cta_helper';

const AuraMyAccountOldCardFound = (props) => {
  const { cardNumber } = props;

  return (
    <div className="aura-myaccount-no-linked-card-wrapper old-card-found fadeInUp">
      <div className="aura-logo">
        <ConditionalView condition={window.innerWidth > 1024}>
          <AuraLogo stacked="vertical" />
        </ConditionalView>
        <ConditionalView condition={window.innerWidth < 1025}>
          <AuraLogo stacked="horizontal" />
        </ConditionalView>
      </div>
      <div className="aura-myaccount-no-linked-card-description old-card-found">
        <div className="header">
          { Drupal.t('An Aura loyalty card is associated with your email address. It just takes one click to link.') }
          <span className="bold">{Drupal.t('Do you want to link now?')}</span>
        </div>
        <div className="card-number-wrapper">
          <Cleave
            name="aura-my-account-link-card"
            className="aura-my-account-link-card"
            disabled
            value={cardNumber}
            options={{ blocks: [4, 4, 4, 4] }}
          />
          <div
            className="link-your-card"
            onClick={() => handleLinkYourCard(cardNumber)}
          >
            { Drupal.t('Link your card') }
          </div>
          <div
            className="not-you"
            onClick={() => handleNotYou(cardNumber)}
          >
            { Drupal.t('Not you?') }
          </div>
        </div>
      </div>
    </div>
  );
};

export default AuraMyAccountOldCardFound;
