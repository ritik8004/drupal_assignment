import React from 'react';
import AuraFormLinkCardOptions from '../aura-form-link-card-options';
import ConditionalView from '../../../../common/components/conditional-view';
import LinkCardOptionEmail from './components/link-card-option-email';
import LinkCardOptionCard from './components/link-card-option-card';
import LinkCardOptionMobile from './components/link-card-option-mobile';
import { handleSignUp, handleSearch } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import { getUserAuraDetailsDefaultState, getUserInput } from '../../../../../../alshaya_aura_react/js/utilities/checkout_helper';
import { removeError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import {
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';

class AuraFormLinkCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'card',
      isOTPModalOpen: false,
      // chosenCountryCode: null,
      ...getUserAuraDetailsDefaultState(),
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.updateStates, false);
  }

  updateStates = (data) => {
    const { stateValues } = data.detail;

    if (Object.keys(stateValues).length === 0) {
      this.setState({
        ...getUserAuraDetailsDefaultState(),
      });
      this.showResponse({
        type: 'failure',
        message: Drupal.t('No data found. Please try again.'),
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
    if (data.type === 'success') {
      submitButton.classList.add('success');
    } else {
      submitButton.classList.remove('success');
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

  setChosenCountryCode = () => {
    // this.setState({
    //   chosenCountryCode: code,
    // });
  };

  linkCard = () => {
    removeError('spc-aura-link-api-response-message');

    const {
      linkCardOption,
    } = this.state;

    const userInput = getUserInput(linkCardOption);

    if (Object.keys(userInput).length !== 0) {
      showFullScreenLoader();
      handleSearch(userInput);
    }
  };

  selectOption = (option) => {
    removeError('spc-aura-link-api-response-message');
    this.setState({
      linkCardOption: option,
    });
  };

  render() {
    const {
      linkCardOption,
      isOTPModalOpen,
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
            <span>{ Drupal.t('Not a member yet?') }</span>
            <a
              onClick={() => this.openOTPModal()}
            >
              {Drupal.t('Sign up now')}
            </a>
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
