import React, { useState } from 'react';
import Cleave from 'cleave.js/react';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraLogo from '../../../../svg-component/aura-logo';
import { handleNotYou, handleLinkYourCard } from '../../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../../utilities/aura_utils';

const AuraMyAccountOldCardFound = (props) => {
  const { cardNumber, notYouFailed } = props;

  const [wrapperClass, setWrapperClass] = useState('hide');

  const onClickArrowHead = () => {
    if (wrapperClass === 'hide') {
      setWrapperClass('show');
    } else {
      setWrapperClass('hide');
    }
  };

  if (drupalSettings.aura.context === 'my_aura') {
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
            <span className="bold">{ drupalSettings.userDetails.userName }</span>
            { Drupal.t('an Aura loyalty account no. @card_number is associated with your email adress. It just takes one click to link.', {
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
  }

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
          { Drupal.t('An Aura loyalty card is associated with your email adress. It just takes one click to link.', {}, { context: 'aura' })}
          <span className="bold">{Drupal.t('Do you want to link now?')}</span>
          <button type="button" onClick={onClickArrowHead}>
            <span className={`arrowHead ${wrapperClass}`}>&nbsp;</span>
          </button>
        </div>
        <div className={`card-number-wrapper ${wrapperClass}`}>
          <div className="card-number-label">
            { Drupal.t('Aura membership number', {}, { context: 'aura' }) }
          </div>
          <Cleave
            name="aura-my-account-link-card"
            className="aura-my-account-link-card"
            disabled
            value={cardNumber}
            options={{ blocks: [4, 4, 4, 4] }}
          />
          <div className="not-you-wrapper">
            <div className="not-you-loader-placeholder" />
            <div className="error-placeholder" />
            <div
              className="not-you"
            >
              { getNotYouLabel(notYouFailed) }
            </div>
            <div
              className="link-your-card"
              onClick={() => {
                handleNotYou(cardNumber);
                Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
              }}
            >
              { Drupal.t('Link your card', {}, { context: 'aura' }) }
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AuraMyAccountOldCardFound;
