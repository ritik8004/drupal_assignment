import React from 'react';
import AuraFormFieldOptions from '../aura-form-field-options';
import AuraFormEmailField from '../aura-form-email-field';
import AuraFormCardField from '../aura-form-card-field';
import AuraFormMobileNumberField from '../aura-form-mobile-number-field';
import { getAuraCheckoutLocalStorageKey, getAuraDetailsDefaultState } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { getUserInput, processCheckoutCart } from '../../../../../aura-loyalty/components/utilities/checkout_helper';
import { showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import PointsString from '../../../../../aura-loyalty/components/utilities/points-string';
import PointsExpiryMessage from '../../../../../aura-loyalty/components/utilities/points-expiry-message';
import logger from '../../../../../../../js/utilities/logger';
import ToolTip from '../../../../../utilities/tooltip';

class AuraLoyaltyForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkCardOption: 'cardNumber',
      isOTPModalOpen: false,
      loyaltyCardLinkedToCart: false,
      cardNumber: '',
      email: '',
      mobile: '',
      loyaltyStatus: 0,
      points: 0,
      expiringPoints: 0,
      expiryDate: '',
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
    //document.addEventListener('loyaltyCardRemovedFromCart', this.handleLoyaltyCardUnset, false);
    // document.addEventListener('orderPlaced', this.handlePlaceOrderEvent, false);

    // Get data from localStorage.
    const localStorageValues = Drupal.getItemFromLocalStorage('aura_checkout_data');

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
    const { stateValues, searchData } = data.detail;
    console.log(stateValues);
    console.log(searchData);

    if (stateValues.error) {
      this.setState({
        loyaltyCardLinkedToCart: false,
        cardNumber: '',
        email: '',
        mobile: '',
        loyaltyStatus: 0,
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

    if (hasValue(stateValues) && parseInt(stateValues.loyaltyStatus, 10) !== 2) {
      this.showResponse({
        type: 'failure',
        message: getStringMessage('aura_partially_enrolled_message'),
      });
      return;
    }

    this.showResponse({
      type: 'success',
      message: '',
    });

    if (searchData) {
      const { cartId } = this.props;
      const dataForStorage = { cartId, ...searchData };

      Drupal.addItemInLocalStorage(
        getAuraCheckoutLocalStorageKey(),
        dataForStorage,
      );
    }

    const { customerId } = drupalSettings.userDetails;
    const { uid } = drupalSettings.user;

    if (!hasValue(customerId) || !hasValue(uid)) {
      logger.warning('Error while trying to fetch loyalty points for customer. No customer available in session. Customer Id: @customerId, User Id: @uid', {
        '@customerId': customerId,
        '@uid': uid,
      });
  
      return getErrorResponse('No user in session', 404);
    }
    this.setState({
      expiringPoints: '700',
      expiryDate: '2022-09-30',
    })

    // const customerPoints = getHelloMemberCustomerInfoByIdentifier(stateValues.cardNumber);

    // if (customerPoints instanceof Promise) {
    //   customerPoints.then((response) => {
    //     if (hasValue(response) && !hasValue(response.error)) {
    //       this.setState({
    //         expiringPoints: response.auraPointsToExpire,
    //         expiryDate: response.auraPointsExpiryDate,
    //       })
    //     } else if (hasValue(response.error)) {
    //       logger.error('Error while trying to fetch customer information for user with customer id @customerId. Message: @message', {
    //         '@customerId': customerId,
    //         '@message': customerPoints.error_message || '',
    //       });
    //     }
    //   });
    // }

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: true,
    });
    // Set state in parent to show link card component.
    //enableShowLinkCardMessage();
  };

//   handleLoyaltyCardUnset = (data) => {
//     this.resetStorage();

//     const { stateValues } = data.detail;

//     this.setState({
//       ...stateValues,
//       loyaltyCardLinkedToCart: false,
//     });
//   };

//   handlePlaceOrderEvent = () => {
//     Drupal.removeItemFromLocalStorage(getAuraCheckoutLocalStorageKey());
//   };

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

//   openOTPModal = () => {
//     this.setState({
//       isOTPModalOpen: true,
//     });
//   };

//   closeOTPModal = () => {
//     this.setState({
//       isOTPModalOpen: false,
//     });
//   };

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
    Drupal.removeItemFromLocalStorage('aura_checkout_data');
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
      processCheckoutCart(data, 'hello_member');
    }
  };

//   removeCard = () => {
//     showFullScreenLoader();
//     // Remove card from state.
//     processCheckoutCart({ action: 'remove' });
//     // We clear input values from the form.
//     const input = document.querySelector('.spc-aura-link-card-wrapper .form-items input:not(:read-only)');
//     input.value = '';
//   };

  selectOption = (option) => {
    console.log(option);
    this.showResponse({
      type: 'failure',
      message: '',
    });

    this.setState({
      linkCardOption: option,
    });
  };

  redeemPoints = () => {
    console.log("redeem");
  }

  render() {
    const {
      linkCardOption,
      loyaltyCardLinkedToCart,
      cardNumber,
      email,
      mobile,
      loyaltyStatus,
      points,
      expiringPoints,
      expiryDate
    } = this.state;
    return (
      <>
        {parseInt(loyaltyStatus) !== 2 &&
          <>
            <div className="label">
              {getStringMessage('enter_aura_details')}
              <ToolTip enable>{getStringMessage('aura_details_tooltip')}</ToolTip>
            </div>
            <AuraFormFieldOptions
              selectedOption={linkCardOption}
              selectOptionCallback={this.selectOption}
              cardNumber={cardNumber}
            />
            <div className={'spc-aura-link-card-form-content active'}>
              <div className="spc-aura-link-card-wrapper">
                <div className="form-items">
                  {(linkCardOption === 'email') &&
                    <AuraFormEmailField email={email} />
                  }
                  {(linkCardOption === 'cardNumber') &&
                    <AuraFormCardField cardNumber={cardNumber} />
                  }
                  {(linkCardOption === 'mobile') &&
                    <AuraFormMobileNumberField
                      setChosenCountryCode={this.setChosenCountryCode}
                      mobile={mobile}
                    />
                  }
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
              {window.innerWidth >= 768 && 
                <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
              }
            </div>
          </>
        }
        {parseInt(loyaltyStatus) === 2 &&
          <>
            <div className="customer-points">
              <div className="title">
                <div className="subtitle-1">
                  { getStringMessage('checkout_you_have') }
                  <PointsString points={points} />
                </div>
                <div className="spc-aura-checkout-messages">
                  <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
                </div>
              </div>
              <div className="redeem-points">
              { getStringMessage('redeem_points_message') }
              <button
                type="submit"
                className="spc-aura-link-card-submit spc-aura-button"
                disabled={false}
                onClick={() => this.redeemPoints()}
              >
                { getStringMessage('redeem_points_button') }
              </button>
              </div>
            </div>
          </>
        }
      </>
    );
  }
}

export default AuraLoyaltyForm;
