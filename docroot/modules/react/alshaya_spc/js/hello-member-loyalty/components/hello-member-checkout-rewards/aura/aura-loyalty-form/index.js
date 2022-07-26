import React from 'react';
import AuraFormFieldOptions from '../aura-form-field-options';
import AuraFormEmailField from '../aura-form-email-field';
import AuraFormCardField from '../aura-form-card-field';
import AuraFormMobileNumberField from '../aura-form-mobile-number-field';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { processCheckoutCart, getHelloMemberAuraStorageKey } from '../../utilities/loyalty_helper';
import { showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import PointsString from '../../../../../aura-loyalty/components/utilities/points-string';
import PointsExpiryMessage from '../../../../../aura-loyalty/components/utilities/points-expiry-message';
import ToolTip from '../../../../../utilities/tooltip';
import AuraRedeemPoints from '../aura-redeem-points';
import { getUserInput } from '../../../../../aura-loyalty/components/utilities/checkout_helper';

class AuraLoyaltyForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'cardNumber',
      loyaltyCardLinkedToCart: false,
      cardNumber: hasValue(props.loyaltyCard) ? props.loyaltyCard : '',
      email: '',
      mobile: '',
      isFullyEnrolled: false,
      points: 0,
      expiringPoints: 0,
      expiryDate: '',
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
    document.addEventListener('loyaltyCardRemovedFromCart', this.handleLoyaltyCardUnset, false);
    document.addEventListener('orderPlaced', this.handlePlaceOrderEvent, false);

    // Get data from localStorage.
    const localStorageValues = Drupal.getItemFromLocalStorage(getHelloMemberAuraStorageKey());

    if (localStorageValues === null) {
      return;
    }
    const { cart } = this.props;

    const {
      cart: {
        cart_id: cartId,
      },
    } = cart;

    if (cartId === localStorageValues.cartId) {
      if (localStorageValues.type === 'apcNumber') {
        showFullScreenLoader();
        processCheckoutCart(localStorageValues);
      }
    }
  }

  handleSearchEvent = (data) => {
    const { stateValues, searchData } = data.detail;

    if (stateValues.error) {
      this.setState({
        loyaltyCardLinkedToCart: false,
        cardNumber: '',
        email: '',
        mobile: '',
        points: 0,
        expiringPoints: 0,
        expiryDate: '',
      });

      this.showResponse({
        type: 'failure',
        message: getStringMessage(stateValues.error_message) || stateValues.error_message,
      });
      return;
    }

    if (hasValue(stateValues) && !stateValues.isFullyEnrolled) {
      this.showResponse({
        type: 'failure',
        message: getStringMessage('aura_partially_enrolled_message'),
      });
    } else if (stateValues.isFullyEnrolled) {
      this.showResponse({
        type: 'success',
        message: '',
      });
      this.setState({
        isFullyEnrolled: true,
      });
    }

    if (searchData) {
      const { cart } = this.props;
      const {
        cart: {
          cart_id: cartId,
        },
      } = cart;
      const dataForStorage = { cartId, ...searchData };

      Drupal.addItemInLocalStorage(
        getHelloMemberAuraStorageKey(),
        dataForStorage,
      );
    }

    // @todo: After magento gives the point balance api, we will call api
    // to fetch expiry points and expiry date.
    this.setState({
      expiringPoints: '200',
      expiryDate: '2-12-2024',
    });

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: true,
    });
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
    Drupal.removeItemFromLocalStorage(getHelloMemberAuraStorageKey());
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
    Drupal.removeItemFromLocalStorage(getHelloMemberAuraStorageKey());
  };

  addCard = () => {
    this.resetStorage();

    const {
      linkCardOption,
      chosenCountryCode,
    } = this.state;

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
      loyaltyCardLinkedToCart,
      cardNumber,
      email,
      mobile,
      isFullyEnrolled,
      points,
      expiringPoints,
      expiryDate,
    } = this.state;
    return (
      <>
        {!isFullyEnrolled
          && (
            <>
              <div className="aura-details">
                {getStringMessage('enter_aura_details')}
                <ToolTip enable>{getStringMessage('aura_details_tooltip')}</ToolTip>
              </div>
              <AuraFormFieldOptions
                selectedOption={linkCardOption}
                selectOptionCallback={this.selectOption}
                cardNumber={cardNumber}
              />
              <div className="spc-aura-link-card-form-content active">
                <div className="spc-aura-link-card-wrapper">
                  <div className="form-items">
                    {(linkCardOption === 'email')
                    && <AuraFormEmailField email={email} />}
                    {(linkCardOption === 'cardNumber')
                    && <AuraFormCardField cardNumber={cardNumber} />}
                    {(linkCardOption === 'mobile')
                    && (
                    <AuraFormMobileNumberField
                      setChosenCountryCode={this.setChosenCountryCode}
                      mobile={mobile}
                    />
                    )}
                    <button
                      type="submit"
                      className="spc-aura-link-card-submit spc-aura-button"
                      disabled={false}
                      onClick={() => this.addCard()}
                    >
                      { Drupal.t('Submit') }
                    </button>
                  </div>
                </div>
                <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
              </div>
              <div className="sub-text">
                {loyaltyCardLinkedToCart
                && (
                <a onClick={() => this.removeCard()}>
                  {getStringMessage('not_you_question')}
                </a>
                )}
              </div>
            </>
          )}
        {isFullyEnrolled
          && (
          <div className="customer-points">
            <div className="title">
              <div className="subtitle-1">
                { getStringMessage('checkout_you_have') }
                <PointsString points={points} />
              </div>
              <div className="spc-aura-checkout-messages">
                <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
              </div>
              <AuraRedeemPoints
                mobile={mobile}
              />
            </div>
          </div>
          )}
      </>
    );
  }
}

export default AuraLoyaltyForm;
