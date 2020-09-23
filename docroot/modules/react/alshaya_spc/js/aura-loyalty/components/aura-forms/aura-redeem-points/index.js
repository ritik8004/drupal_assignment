import React from 'react';
import AuraFormTextField from '../aura-textfield';

class AuraFormRedeemPoints extends React.Component {
  render() {
    return (
      <div className="spc-aura-redeem-points-form-wrapper">
        <span className="label">{ Drupal.t('Use your points') }</span>
        <div className="form-items">
          <div className="inputs">
            <AuraFormTextField
              name="spc-aura-redeem-field-points"
              placeholder={Drupal.t('Points')}
            />
            <span className="spc-aura-redeem-points-separator">=</span>
            <AuraFormTextField
              name="spc-aura-redeem-field-amount"
              placeholder={Drupal.t('Amount')}
            />
          </div>
          <button
            type="submit"
            className="spc-aura-redeem-form-submit spc-aura-button"
            onClick={() => this.linkCard()}
          >
            { Drupal.t('Use points') }
          </button>
        </div>
        <div className="spc-aura-link-api-response-message" />
      </div>
    );
  }
}

export default AuraFormRedeemPoints;
