import React from 'react';
import Cleave from 'cleave.js/react';
import parse from 'html-react-parser';
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
      openLinkCardModal,
    } = this.props;
    return (
      <div className="spc-aura-unlink-card-wrapper link-card-wrapper">
        <div className="aura-modal-header">
          <SectionTitle>
            {getStringMessage('aura_link_aura')}
          </SectionTitle>
          <button
            type="button"
            className="close"
            onClick={() => closeLinkOldCardModal()}
          />
        </div>
        <div className="aura-modal-body">
          <div className="description">
            {parse(getStringMessage('aura_link_unlinked_card_body_title', {
              '@firstName': firstName,
            }))}
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
            </div>
            <div className="no-link-message">
              <a
                href="#"
                onClick={() => {
                  openLinkCardModal();
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
                }}
              >
                {Drupal.t('Not you?')}
              </a>
            </div>
          </div>
        </div>

        <div className="aura-modal-form-actions">
          <div
            className="spc-aura-link-card-submit spc-aura-button"
            onClick={() => {
              Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
              this.linkYourCard(cardNumber);
            }}
          >
            {Drupal.t('Submit')}
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormUnlinkedCard;
