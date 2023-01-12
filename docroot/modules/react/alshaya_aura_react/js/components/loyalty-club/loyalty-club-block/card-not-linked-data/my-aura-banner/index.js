import React from 'react';
import AuraLogo from '../../../../../svg-component/aura-logo';
import {
  handleLinkYourCard,
  handleNotYou,
} from '../../../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../../../utilities/aura_utils';
import ConditionalView
  from '../../../../../../../js/utilities/components/conditional-view';

const MyAuraBanner = (props) => {
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
          <span className="bold">{ `${drupalSettings.userDetails.userName} `}</span>
          {Drupal.t('an Aura loyalty account no. @card_number is associated with your email adress. It just takes one click to link.', {
            '@card_number': cardNumber,
          }, { context: 'aura' })}
          <span className="bold">{Drupal.t('Do you want to link now?')}</span>
        </div>
        <div className="card-number-wrapper">
          <div className="link-card-wrapper">
            <div className="link-card-loader-placeholder" />
            <div
              className="link-your-card"
              onClick={() => {
                Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                handleLinkYourCard(cardNumber);
              }}
            >
              { Drupal.t('Link your card', {}, { context: 'aura' }) }
            </div>
          </div>
          <div className="not-you-wrapper">
            <div className="not-you-loader-placeholder" />
            <div className="error-placeholder" />
            <div
              className="not-you"
              onClick={() => {
                handleNotYou(cardNumber);
                Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
              }}
            >
              { getNotYouLabel(notYouFailed) }
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MyAuraBanner;
