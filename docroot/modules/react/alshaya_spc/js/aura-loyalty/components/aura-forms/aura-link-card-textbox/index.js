import React from 'react';

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
  };

  render() {
    return (
      <div className="spc-aura-link-card-wrapper">
        <div className="form-items">
          <input
            placeholder={Drupal.t('Email, loyalty or mobile number')}
            name="spc-aura-link-card-input"
            className="spc-aura-link-card-input"
            type="text"
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
