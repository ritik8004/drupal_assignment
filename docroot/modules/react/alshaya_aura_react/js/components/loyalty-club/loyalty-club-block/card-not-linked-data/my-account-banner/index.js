import React from 'react';
import Collapsible from 'react-collapsible';
import Cleave from 'cleave.js/react';
import AuraLogo from '../../../../../svg-component/aura-logo';
import {
  handleLinkYourCard,
  handleNotYou,
} from '../../../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../../../utilities/aura_utils';
import ConditionalView
  from '../../../../../../../js/utilities/components/conditional-view';

const AuraWrapperHeader = () => (
  <div className="header">
    { Drupal.t('An Aura loyalty card is already associated with your email address. It just takes one click to link.', {}, { context: 'aura' }) }
    <span className="bold">{Drupal.t('Do you want to link now?')}</span>
  </div>
);

const MyAccountBanner = (props) => {
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
        <Collapsible
          trigger={AuraWrapperHeader()}
          open={false}
        >
          <div className="my-account-aura-card-wrapper">
            <div className="card-number-wrapper">
              <div className="card-number-label">
                { Drupal.t('Aura membership number', {}, { context: 'aura' })}
              </div>
              <Cleave
                name="aura-my-account-link-card"
                className="aura-my-account-link-card"
                disabled
                value={cardNumber}
                options={{ blocks: [4, 4, 4, 4] }}
              />
            </div>
            <div className="link-card-wrapper">
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
              <div className="link-card-loader-placeholder" />
              <div
                className="link-your-card"
                onClick={() => {
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                  handleLinkYourCard(cardNumber);
                }}
              >
                { Drupal.t('Link your account') }
              </div>
            </div>
          </div>
        </Collapsible>
      </div>
    </div>
  );
};

export default MyAccountBanner;
