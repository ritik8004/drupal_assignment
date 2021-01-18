import React from 'react';
import Cleave from 'cleave.js/react';
import { handleNotYou, handleLinkYourCard } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';

const AuraFormUnlinkedCard = (props) => {
  const { cardNumber } = props;

  return (
    <div className="spc-aura-unlink-card-wrapper">
      <div className="description">
        {Drupal.t('An AURA card is already associated with your email address. Link your card in just one click.')}
        <b>{Drupal.t('Do you want to link now?')}</b>
      </div>
      <div className="spc-aura-unlink-card-form-content">
        <div className="form-items">
          <Cleave
            placeholder={Drupal.t('Enter Aura Card Number')}
            name="spc-aura-link-card-input"
            className="spc-aura-link-card-input"
            options={{ creditCard: true }}
            value={cardNumber}
            disabled
          />
          <button
            type="submit"
            className="spc-aura-link-card-submit spc-aura-button"
            onClick={() => handleLinkYourCard(cardNumber)}
          >
            { Drupal.t('Submit') }
          </button>
        </div>
        <div className="no-link-message">
          <a onClick={() => handleNotYou(cardNumber)}>
            {Drupal.t('Not you?')}
          </a>
        </div>
      </div>
      <div className="spc-aura-link-api-response-message" />
    </div>
  );
};

export default AuraFormUnlinkedCard;
