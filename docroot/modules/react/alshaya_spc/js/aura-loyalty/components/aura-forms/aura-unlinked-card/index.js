import React from 'react';
import Cleave from 'cleave.js/react';
import { handleLinkYourCard } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SectionTitle from '../../../../utilities/section-title';
import getStringMessage from '../../../../../../js/utilities/strings';
import { showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';

class AuraFormUnlinkedCard extends React.Component {
  /**
   * Helper function to link the unlinked card.
   */
  linkYourCard = (cardNumber) => {
    // Show full screen loader.
    showFullScreenLoader();
    // Call handleLinkYourCard method to process further.
    handleLinkYourCard(cardNumber);
  }

  render() {
    const {
      cardNumber,
      closeLinkOldCardModal,
      firstName,
      openOTPModal,
      openLinkCardModal,
    } = this.props;
    return (
      <div className="spc-aura-unlink-card-wrapper link-card-wrapper">
        <div className="aura-modal-header">
          <SectionTitle>
            {getStringMessage('link_card_header_logged_in')}
          </SectionTitle>
          <button type="button" className="close" onClick={() => closeLinkOldCardModal()} />
        </div>
        <div className="description">
          {getStringMessage('aura_link_unlinked_card_body_title', {
            '!firstName': firstName,
          })}
          <b>{getStringMessage('aura_link_unlinked_card_body_sub_title')}</b>
        </div>
        <div className="spc-aura-unlink-card-form-content">
          <div className="form-items">
            <label>
              {getStringMessage('aura_link_unlinked_card_field_title')}
            </label>
            <Cleave
              name="spc-aura-link-card-input"
              className="spc-aura-link-card-input"
              options={{ creditCard: true }}
              value={cardNumber}
              disabled
            />
            <button
              type="submit"
              className="spc-aura-link-card-submit spc-aura-button"
              onClick={() => this.linkYourCard(cardNumber)}
            >
              {Drupal.t('Submit')}
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
  }
}

export default AuraFormUnlinkedCard;
