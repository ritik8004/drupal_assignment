import React from 'react';
import AuraFormLinkCardOptions from '../aura-form-link-card-options';
import ConditionalView from '../../../../common/components/conditional-view';
import LinkCardOptionEmail from './components/link-card-option-email';
import LinkCardOptionCard from './components/link-card-option-card';
import LinkCardOptionMobile from './components/link-card-option-mobile';
import { handleSignUp, handleNotYou } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import { getAuraDetailsDefaultState, getAuraLocalStorageKey } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { getUserInput, processCheckoutCart } from '../../utilities/checkout_helper';
import {
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import {
  setStorageInfo,
  getStorageInfo,
  removeStorageInfo,
} from '../../../../../../js/utilities/storage';

class AuraFormLinkCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'cardNumber',
      isOTPModalOpen: false,
      chosenCountryCode: null,
      loyaltyCardLinkedToCart: false,
      ...getAuraDetailsDefaultState(),
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
    document.addEventListener('loyaltyStatusUpdated', this.handleLoyaltyUpdateEvent, false);
    document.addEventListener('orderPlaced', this.handlePlaceOrderEvent, false);

    // Get data from localStorage.
    const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

    if (localStorageValues === null) {
      return;
    }

    const { cartId } = this.props;

    if (cartId === localStorageValues.cartId) {
      const data = {
        detail: {
          stateValues: {
            linkCardOption: localStorageValues.key,
            [localStorageValues.key]: localStorageValues.value,
          },
        },
      };
      this.handleSearchEvent(data);
    }
  }

  handleSearchEvent = (data) => {
    const { enableShowLinkCardMessage } = this.props;
    const { stateValues, searchData } = data.detail;

    if (Object.keys(stateValues).length === 0) {
      this.setState({
        ...getAuraDetailsDefaultState(),
        loyaltyCardLinkedToCart: false,
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

    if (searchData) {
      const { cartId } = this.props;
      const dataForStorage = { cartId, ...searchData };

      // Get mobile number without country code to set in storage.
      if (searchData.key === 'mobile') {
        dataForStorage.value = searchData.value.substring(3);
      }

      setStorageInfo(dataForStorage, getAuraLocalStorageKey());
    }

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: true,
    });
    // Set state in parent to show link card component.
    enableShowLinkCardMessage();
  };

  handleLoyaltyUpdateEvent = (data) => {
    this.showResponse({
      type: 'failure',
      message: '',
    });

    removeStorageInfo(getAuraLocalStorageKey());

    const { stateValues } = data.detail;

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: false,
    });
  };

  handlePlaceOrderEvent = () => {
    removeStorageInfo(getAuraLocalStorageKey());
  };

  showResponse = (data) => {
    const element = document.querySelector('.spc-aura-link-card-form .spc-aura-link-api-response-message');
    if (element) {
      element.innerHTML = data.message;
    }
    const submitButton = document.querySelector('.spc-aura-link-card-wrapper .form-items');
    const cardOptions = document.querySelector('.spc-aura-link-card-form .aura-form-items-link-card-options');

    if (data.type === 'success') {
      submitButton.classList.add('success');
      cardOptions.classList.add('success');
      element.classList.remove('error');
    } else {
      submitButton.classList.remove('success');
      cardOptions.classList.remove('success');
      element.classList.add('error');
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

  resetStorage = () => {
    this.showResponse({
      type: 'failure',
      message: '',
    });
    removeStorageInfo(getAuraLocalStorageKey());
  };

  linkCard = () => {
    this.resetStorage();

    const {
      linkCardOption,
      chosenCountryCode,
    } = this.state;

    const userInput = getUserInput(linkCardOption, chosenCountryCode);

    if (Object.keys(userInput).length !== 0) {
      showFullScreenLoader();
      processCheckoutCart(userInput);
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
      loyaltyCardLinkedToCart,
      cardNumber,
      email,
      mobile,
    } = this.state;

    return (
      <>
        <AuraFormLinkCardOptions
          selectedOption={linkCardOption}
          selectOptionCallback={this.selectOption}
          cardNumber={cardNumber}
        />
        <div className="spc-aura-link-card-form-content">
          <div className="spc-aura-link-card-wrapper">
            <div className="form-items">
              <ConditionalView condition={linkCardOption === 'email'}>
                <LinkCardOptionEmail email={email} />
              </ConditionalView>
              <ConditionalView condition={linkCardOption === 'cardNumber'}>
                <LinkCardOptionCard cardNumber={cardNumber} />
              </ConditionalView>
              <ConditionalView condition={linkCardOption === 'mobile'}>
                <LinkCardOptionMobile
                  setChosenCountryCode={this.setChosenCountryCode}
                  mobile={mobile}
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
            <ConditionalView condition={window.innerWidth < 768}>
              <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
            </ConditionalView>
          </div>
          <div className="sub-text">
            { loyaltyCardLinkedToCart === true
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
        <ConditionalView condition={window.innerWidth >= 768}>
          <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
        </ConditionalView>
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
