import React from 'react';
import Cleave from 'cleave.js/react';
import { handleLinkYourCard } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SectionTitle from '../../../../utilities/section-title';
import getStringMessage from '../../../../../../js/utilities/strings';

const AuraFormUnlinkedCard = (props) => {
  const {
    cardNumber,
    closeLinkOldCardModal,
    firstName,
    openOTPModal,
    openLinkCardModal,
  } = props;

  return (
    <div className="spc-aura-unlink-card-wrapper link-card-wrapper">
      <div className="aura-modal-header">
        <SectionTitle>
          {getStringMessage('link_card_header_logged_in')}
        </SectionTitle>
        <button type="button" className="close" onClick={() => closeLinkOldCardModal()} />
      </div>
      <div className="description">
        {Drupal.t('!firstName an Aura loyalty card is associate with your email adress. It just a takes one click to link.', {
          '!firstName': firstName,
        },
        { context: 'aura' })}
        <b>{Drupal.t('Do you want to link now?', {}, { context: 'aura' })}</b>
      </div>
      <div className="spc-aura-unlink-card-form-content">
        <div className="form-items">
          <label>
            {Drupal.t('Aura Card Number', {}, { context: 'aura' })}
          </label>
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
          <a onClick={() => openLinkCardModal()}>
            {Drupal.t('Not you?')}
          </a>
        </div>
      </div>
      <div className="spc-aura-link-api-response-message" />
      <div className="aura-modal-footer">
        <div className="join-aura" onClick={() => openOTPModal()}>
          {getStringMessage('aura_join_aura')}
        </div>
      </div>
    </div>
  );
};

export default AuraFormUnlinkedCard;
