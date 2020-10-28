import React from 'react';
import Cleave from 'cleave.js/react';

class AuraFormUnlinkedCard extends React.Component {
  /**
   * Placeholder for callback to call link card API.
   */
  linkCard = () => {
    // @todo: Aura - SPC - Call Link API.
  };

  notYou = () => {
    // @todo: Aura - SPC - Not you link API.
  };

  render() {
    return (
      <div className="spc-aura-unlink-card-wrapper">
        <div className="description">
          {Drupal.t('An Aura loyalty card is associate with your email adress. It just a takes one click to link.')}
          <b>{Drupal.t('Do you want to link now?')}</b>
        </div>
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
            { Drupal.t('Submit') }
          </button>
        </div>
        <div className="no-link-message">
          <a href="#" onClick={() => this.notYou()}>
            {Drupal.t('Not you?')}
          </a>
        </div>
        <div className="spc-aura-link-api-response-message" />
      </div>
    );
  }
}

export default AuraFormUnlinkedCard;
