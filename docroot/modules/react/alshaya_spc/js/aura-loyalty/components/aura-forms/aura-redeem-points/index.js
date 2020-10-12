import React from 'react';
import AuraFormTextField from '../aura-textfield';

class AuraFormRedeemPoints extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      enableSubmit: false,
    };
  }

  enableSubmitButton = (e) => {
    // @todo: Run some proper validations, for now just checking length.
    if (e.target.value.length >= 1) {
      this.setState({
        enableSubmit: true,
      });
    } else {
      this.setState({
        enableSubmit: false,
      });
    }
  };

  render() {
    const { enableSubmit } = this.state;

    return (
      <div className="spc-aura-redeem-points-form-wrapper">
        <span className="label">{ Drupal.t('Use your points') }</span>
        <div className="form-items">
          <div className="inputs">
            <AuraFormTextField
              name="spc-aura-redeem-field-points"
              placeholder="0"
              onChangeCallback={this.enableSubmitButton}
            />
            <span className="spc-aura-redeem-points-separator">=</span>
            <AuraFormTextField
              name="spc-aura-redeem-field-amount"
              placeholder={Drupal.t('KWD 0.000')}
            />
          </div>
          <button
            type="submit"
            className="spc-aura-redeem-form-submit spc-aura-button"
            onClick={() => this.linkCard()}
            disabled={!enableSubmit}
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
