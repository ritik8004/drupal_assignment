import React from 'react';
import AuraFormLinkCardOptions from '../aura-form-link-card-options';
import ConditionalView from '../../../../common/components/conditional-view';
import LinkCardOptionEmail from './components/link-card-option-email';
import LinkCardOptionCard from './components/link-card-option-card';
import LinkCardOptionMobile from './components/link-card-option-mobile';
import { handleSignUp } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import { getAuraDetailsDefaultState, getAuraCheckoutLocalStorageKey } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { getUserInput, processCheckoutCart } from '../../utilities/checkout_helper';
import {
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../utilities/strings';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

class AuraFormLinkCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'cardNumber',
      isOTPModalOpen: false,
      loyaltyCardLinkedToCart: false,
      ...getAuraDetailsDefaultState(),
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
    document.addEventListener('loyaltyCardRemovedFromCart', this.handleLoyaltyCardUnset, false);
    document.addEventListener('orderPlaced', this.handlePlaceOrderEvent, false);

    // Get data from localStorage.
    const localStorageValues = Drupal.getItemFromLocalStorage(getAuraCheckoutLocalStorageKey());

    if (localStorageValues === null) {
      return;
    }

    const { cartId } = this.props;

    if (cartId === localStorageValues.cartId) {
      let { key } = localStorageValues;

      if (localStorageValues.type === 'apcNumber') {
        key = 'cardNumber';
      } else if (localStorageValues.type === 'phone') {
        key = 'mobile';
      }

      const data = {
        detail: {
          stateValues: {
            linkCardOption: key,
            [key]: localStorageValues.value,
          },
        },
      };
      this.handleSearchEvent(data);
    }
  }

  handleSearchEvent = (data) => {
    const { enableShowLinkCardMessage } = this.props;
    const { stateValues, searchData } = data.detail;

    if (stateValues.error) {
      this.setState({
        ...getAuraDetailsDefaultState(),
        loyaltyCardLinkedToCart: false,
      });

      this.showResponse({
        type: 'failure',
        message: getStringMessage(stateValues.error_message) || stateValues.error_message,
      });
      return;
    }

    this.showResponse({
      type: 'success',
      message: getStringMessage('checkout_points_to_be_credited_message'),
    });

    if (searchData) {
      const { cartId } = this.props;
      const dataForStorage = { cartId, ...searchData };

      Drupal.addItemInLocalStorage(
        getAuraCheckoutLocalStorageKey(),
        dataForStorage,
      );
    }

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: true,
    });
    // Set state in parent to show link card component.
    enableShowLinkCardMessage();
  };

  handleLoyaltyCardUnset = (data) => {
    this.resetStorage();

    const { stateValues } = data.detail;

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: false,
    });
  };

  handlePlaceOrderEvent = () => {
    Drupal.removeItemFromLocalStorage(getAuraCheckoutLocalStorageKey());
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
    Drupal.removeItemFromLocalStorage(getAuraCheckoutLocalStorageKey());
  };

  addCard = () => {
    this.resetStorage();

    const {
      linkCardOption,
      chosenCountryCode,
    } = this.state;

    // Just return from here if form is not active.
    const { formActive } = this.props;
    if (!formActive) {
      return;
    }

    const userInput = getUserInput(`${linkCardOption}Checkout`);

    if (hasValue(userInput)) {
      const { type } = userInput;
      showFullScreenLoader();
      const data = { ...userInput, action: 'add' };

      if (type === 'phone') {
        data.countryCode = chosenCountryCode;
      }
      processCheckoutCart(data);
    }
  };

  removeCard = () => {
    showFullScreenLoader();
    // Remove card from state.
    processCheckoutCart({ action: 'remove' });
    // We clear input values from the form.
    const input = document.querySelector('.spc-aura-link-card-wrapper .form-items input:not(:read-only)');
    input.value = '';
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

    const { formActive } = this.props;
    // Active class based on form active props.
    const active = formActive ? 'active' : 'in-active';

    return (
      <>
        <AuraFormLinkCardOptions
          selectedOption={linkCardOption}
          selectOptionCallback={this.selectOption}
          cardNumber={cardNumber}
        />
        <div className={`spc-aura-link-card-form-content ${active}`}>
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
                disabled={!formActive}
                onClick={() => this.addCard()}
              >
                { getStringMessage('checkout_apply') }
              </button>
            </div>
            <ConditionalView condition={window.innerWidth < 768}>
              <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
            </ConditionalView>
          </div>
          <div className="sub-text">
            <ConditionalView condition={loyaltyCardLinkedToCart}>
              <a onClick={() => this.removeCard()}>
                {getStringMessage('not_you_question')}
              </a>
            </ConditionalView>

            <ConditionalView condition={!loyaltyCardLinkedToCart}>
              <>
                <span>{ getStringMessage('checkout_not_member_question') }</span>
                <a onClick={() => this.openOTPModal()}>
                  {getStringMessage('sign_up_now')}
                </a>
              </>
            </ConditionalView>
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
