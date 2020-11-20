import React from 'react';
import AuraFormLinkCardOptions from '../aura-form-link-card-options';
import ConditionalView from '../../../../common/components/conditional-view';
import LinkCardOptionEmail from './components/link-card-option-email';
import LinkCardOptionCard from './components/link-card-option-card';
import LinkCardOptionMobile from './components/link-card-option-mobile';
import { handleSignUp, handleSearch, handleNotYou } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import { getAuraDetailsDefaultState } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { getUserInput } from '../../utilities/checkout_helper';
import {
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';

class AuraFormLinkCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'card',
      isOTPModalOpen: false,
      chosenCountryCode: null,
      ...getAuraDetailsDefaultState(),
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.updateStates, false);
    document.addEventListener('loyaltyStatusUpdated', this.updateStates, false);
  }

  updateStates = (data) => {
    const { stateValues } = data.detail;

    if (Object.keys(stateValues).length === 0) {
      this.setState({
        ...getAuraDetailsDefaultState(),
      });
      this.showResponse({
        type: 'failure',
        message: Drupal.t('No card found. Please try again.'),
      });
      return;
    }

    this.showResponse({
      type: 'success',
      message: Drupal.t('Your loyalty points will be credited to this account.'),
    });

    this.setState({
      ...stateValues,
    });
  };

  showResponse = (data) => {
    const element = document.querySelector('.spc-aura-link-card-wrapper .spc-aura-link-api-response-message');
    element.innerHTML = data.message;
    const submitButton = document.querySelector('.spc-aura-link-card-wrapper .form-items');
    const cardOptions = document.querySelector('.spc-aura-link-card-form .aura-form-items-link-card-options');
    if (data.type === 'success') {
      submitButton.classList.add('success');
      cardOptions.classList.add('success');
    } else {
      submitButton.classList.remove('success');
      cardOptions.classList.remove('success');
    }
  };

  openOTPModal = () => {
    this.setState({
      isOTPModalOpen: true,
    });
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
    });
  };

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  linkCard = () => {
    this.showResponse({
      type: 'failure',
      message: '',
    });

    const {
      linkCardOption,
      chosenCountryCode,
    } = this.state;

    const userInput = getUserInput(linkCardOption, chosenCountryCode);

    if (Object.keys(userInput).length !== 0) {
      showFullScreenLoader();
      handleSearch(userInput);
    }
  };

  selectOption = (option) => {
    this.showResponse({
      type: 'failure',
      message: '',
    });

    this.setState({
      linkCardOption: option,
    });
  };

  render() {
    const {
      linkCardOption,
      isOTPModalOpen,
      cardNumber,
    } = this.state;

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
                <LinkCardOptionEmail />
              </ConditionalView>
              <ConditionalView condition={linkCardOption === 'card'}>
                <LinkCardOptionCard />
              </ConditionalView>
              <ConditionalView condition={linkCardOption === 'mobile'}>
                <LinkCardOptionMobile
                  setChosenCountryCode={this.setChosenCountryCode}
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
            <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
          </div>
          <div className="sub-text">
            { cardNumber
              ? (
                <a onClick={() => handleNotYou(cardNumber)}>
                  {Drupal.t('Not you?')}
                </a>
              )
              : (
                <>
                  <span>{ Drupal.t('Not a member yet?') }</span>
                  <a
                    onClick={() => this.openOTPModal()}
                  >
                    {Drupal.t('Sign up now')}
                  </a>
                </>
              )}
          </div>
        </div>
        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={this.closeOTPModal}
          handleSignUp={handleSignUp}
        />
      </>
    );
  }
}

export default AuraFormLinkCard;
