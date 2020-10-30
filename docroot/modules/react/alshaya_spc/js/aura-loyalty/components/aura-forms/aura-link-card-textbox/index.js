import React from 'react';
import Cleave from 'cleave.js/react';
import AuraFormLinkCardOptions from '../aura-form-link-card-options';
import ConditionalView from '../../../../common/components/conditional-view';
import AuraMobileNumberField from '../aura-mobile-number-field';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';

class AuraFormLinkCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'card',
    };
  }

  setChosenCountryCode = () => {
    // @TODO: Set the chose country code in state.
  };

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

  selectOption = (option) => {
    this.setState({
      linkCardOption: option,
    });
  };

  render() {
    const {
      linkCardOption,
    } = this.state;

    const {
      country_mobile_code: countryMobileCode,
      mobile_maxlength: countryMobileCodeMaxLength,
    } = getAuraConfig();

    return (
      <>
        <AuraFormLinkCardOptions
          selectedOption={linkCardOption}
          selectOptionCallback={this.selectOption}
        />
        <div className="spc-aura-link-card-form-content">
          <div className="spc-aura-link-card-wrapper">
            <div className="form-items">
              <ConditionalView condition={linkCardOption === 'email'}>
                <input
                  type="email"
                  name="spc-aura-link-card-input-email"
                  className="spc-aura-link-card-input-email spc-aura-link-card-input"
                  placeholder={Drupal.t('Email address')}
                />
              </ConditionalView>
              <ConditionalView condition={linkCardOption === 'card'}>
                <Cleave
                  placeholder={Drupal.t('Loyalty card number')}
                  name="spc-aura-link-card-input-card"
                  className="spc-aura-link-card-input-card spc-aura-link-card-input"
                  options={{ blocks: [4, 4, 4, 4] }}
                />
              </ConditionalView>
              <ConditionalView condition={linkCardOption === 'mobile'}>
                <AuraMobileNumberField
                  isDisabled={false}
                  name="spc-aura-link-card-input-mobile"
                  countryMobileCode={countryMobileCode}
                  maxLength={countryMobileCodeMaxLength}
                  setCountryCode={this.setChosenCountryCode}
                  onlyMobileFieldPlaceholder={Drupal.t('Mobile number')}
                />
              </ConditionalView>
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
          <div className="sub-text">
            <span>{ Drupal.t('Not a member yet?') }</span>
            <a href="#">{Drupal.t('Sign up now')}</a>
          </div>
        </div>
      </>
    );
  }
}

export default AuraFormLinkCard;
