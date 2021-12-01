import React from 'react';
import Cleave from 'cleave.js/react';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraLogo from '../../../../svg-component/aura-logo';
import { handleNotYou, handleLinkYourCard } from '../../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../../utilities/aura_utils';

const AuraMyAccountOldCardFound = (props) => {
  const { cardNumber, notYouFailed } = props;

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
          { Drupal.t('An Aura card is already associated with your email address. Link your card in just one click.') }
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
          <div className="link-card-wrapper">
            <div className="link-card-loader-placeholder" />
            <div
              className="link-your-card"
              onClick={() => handleLinkYourCard(cardNumber)}
            >
              { Drupal.t('Link your account') }
            </div>
          </div>
          <div className="not-you-wrapper">
            <div className="not-you-loader-placeholder" />
            <div className="error-placeholder" />
            <div
              className="not-you"
              onClick={() => handleNotYou(cardNumber)}
            >
              { getNotYouLabel(notYouFailed) }
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AuraMyAccountOldCardFound;
