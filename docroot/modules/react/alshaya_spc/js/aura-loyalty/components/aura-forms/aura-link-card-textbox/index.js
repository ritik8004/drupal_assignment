import React from 'react';
import Cleave from 'cleave.js/react';

class AuraFormLinkCard extends React.Component {
  /**
   * Placeholder for callback to call link card API.
   */
  linkCard = () => {
    // Assume all success and return the success message.
    // @todo: Aura - SPC - Call Link API.
    // Add some loaders during API call in progress.
    // Return success failure message.
    const element = document.querySelector('.spc-aura-link-card-wrapper .spc-aura-link-api-response-message');
    element.innerHTML = Drupal.t('Your loyalty points will be credited to this account.');
    const submitButton = document.querySelector('.spc-aura-link-card-wrapper .form-items');
    submitButton.classList.add('success');
  };

  render() {
    return (
      <div className="spc-aura-link-card-wrapper">
        <div className="form-items">
          <Cleave
            placeholder={Drupal.t('Enter Aura Card Number')}
            name="spc-aura-link-card-input"
            className="spc-aura-link-card-input"
            options={{ creditCard: true }}
          />
          <button
            type="submit"
            className="spc-aura-link-card-submit spc-aura-button"
            onClick={() => this.linkCard()}
          >
            { Drupal.t('Apply') }
          </button>
        </div>
        <div className="spc-aura-link-api-response-message" />
      </div>
    );
  }
}

export default AuraFormLinkCard;
