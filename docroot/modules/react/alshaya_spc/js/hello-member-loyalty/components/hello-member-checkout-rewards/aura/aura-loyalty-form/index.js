import React from 'react';
import AuraFormFieldOptions from '../aura-form-field-options';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { processCheckoutCart, getAuraCustomerPoints } from '../../utilities/loyalty_helper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import PointsString from '../../../../../aura-loyalty/components/utilities/points-string';
import PointsExpiryMessage from '../../../../../aura-loyalty/components/utilities/points-expiry-message';
import ToolTip from '../../../../../utilities/tooltip';
import AuraRedeemPoints from '../aura-redeem-points';
import { getUserInput } from '../../../../../aura-loyalty/components/utilities/checkout_helper';
import dispatchCustomEvent from '../../../../../../../js/utilities/events';
import { cartContainsAnyVirtualProduct } from '../../../../../utilities/egift_util';
import { isEgiftCardEnabled } from '../../../../../../../js/utilities/util';
import LinkCardOptionMobile from '../../../../../aura-loyalty/components/aura-forms/aura-link-card-textbox/components/link-card-option-mobile';
import LinkCardOptionEmail from '../../../../../aura-loyalty/components/aura-forms/aura-link-card-textbox/components/link-card-option-email';
import LinkCardOptionCard from '../../../../../aura-loyalty/components/aura-forms/aura-link-card-textbox/components/link-card-option-card';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import Loading from '../../../../../utilities/loading';

class AuraLoyaltyForm extends React.Component {
  constructor(props) {
    super(props);
    const {
      cart: {
        cart: {
          loyalty_card: loyaltyCard,
          loyalty_type: loyaltyType,
        },
      },
    } = props;
    this.state = {
      linkCardOption: 'cardNumber',
      loyaltyCardLinkedToCart: false,
      cardNumber: hasValue(loyaltyCard) && (loyaltyType === 'aura') ? loyaltyCard : '',
      email: '',
      mobile: '',
      isFullyEnrolled: false,
      points: 0,
      expiringPoints: 0,
      expiryDate: null,
      waitForPoints: false,
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyDetailsSearchComplete', this.handleSearchEvent, false);
    document.addEventListener('loyaltyCardRemovedFromCart', this.handleLoyaltyCardUnset, false);

    // Get loyalty card data from cart.
    const { cart } = this.props;

    const {
      cart: {
        loyalty_card: loyaltyCard,
        loyalty_type: loyaltyType,
      },
    } = cart;

    if (!hasValue(loyaltyType) || !hasValue(loyaltyCard)
      || (hasValue(loyaltyType) && loyaltyType !== 'aura')) {
      return;
    }

    const data = {
      key: 'cardNumber',
      type: 'cardNumber',
      value: loyaltyCard,
      action: 'add',
    };
    showFullScreenLoader();
    processCheckoutCart(data);
  }

  handleSearchEvent = (data) => {
    this.setState({
      waitForPoints: true,
    });
    const { stateValues } = data.detail;

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

      // Push error events to gtm.
      Drupal.alshayaSeoGtmLoyaltyOptionsError('aura', stateValues.error_message);
      return;
    }

    dispatchCustomEvent('onLinkCardSuccessful', stateValues.cardNumber);

    if (hasValue(stateValues)) {
      // We get customer points only for fully enrolled customer.
      showFullScreenLoader();
      // If user is fully enrolled, we get all his apc points details.
      const customerPoints = getAuraCustomerPoints(stateValues.cardNumber);
      customerPoints.then((result) => {
        if (hasValue(result.error)) {
          this.showResponse({
            type: 'failure',
            message: result.error_message,
          });
        } else {
          this.setState({
            points: result.auraPoints,
            expiringPoints: result.auraPointsToExpire,
            expiryDate: result.auraPointsExpiryDate,
            isFullyEnrolled: stateValues.isFullyEnrolled,
          });
        }
        this.setState({
          waitForPoints: false,
        });
        removeFullScreenLoader();
      });
    }

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: true,
    });
  };

  handleLoyaltyCardUnset = (data) => {
    const { stateValues } = data.detail;

    this.setState({
      ...stateValues,
      loyaltyCardLinkedToCart: false,
    });
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
      waitForPoints,
    } = this.state;

    const { cart } = this.props;

    if (isFullyEnrolled && waitForPoints) {
      return (
        <div className="spc-hello-member-checkout-loading fadeInUp">
          <Loading />
        </div>
      );
    }

    // Disable AURA guest user link card form if cart contains virtual products.
    const formActive = !(isEgiftCardEnabled() && cartContainsAnyVirtualProduct(cart.cart));
    return (
      <>
        {!isFullyEnrolled && !loyaltyCardLinkedToCart
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
                    && <LinkCardOptionEmail email={email} />}
                    {(linkCardOption === 'cardNumber')
                    && <LinkCardOptionCard cardNumber={cardNumber} />}
                    {(linkCardOption === 'mobile')
                    && (
                    <LinkCardOptionMobile
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
                      { getStringMessage('card_submit') }
                    </button>
                  </div>
                </div>
                <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
              </div>
            </>
          )}
        {hasValue(points)
          && (
          <div className={(!isFullyEnrolled && loyaltyCardLinkedToCart) ? 'customer-points not-you' : 'customer-points'}>
            <div className="aura-points-info">
              <div className="total-points">
                { getStringMessage('checkout_you_have') }
                <PointsString points={points} />
              </div>
              {hasValue(expiringPoints) && hasValue(expiryDate)
                && (
                <div className="spc-aura-checkout-messages">
                  <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
                  <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
                </div>
                )}
            </div>
            {/* If user is partially enrolled, we show him partially enrolled message. */}
            {!isFullyEnrolled && loyaltyCardLinkedToCart
              && (
                <div className="partially-enrolled">
                  <div className="sub-text">
                    <a onClick={() => this.removeCard()}>
                      {getStringMessage('not_you_question')}
                    </a>
                  </div>
                  <div className="partially-enrolled-message">
                    { getStringMessage('aura_partially_enrolled_message') }
                  </div>
                </div>
              )}
            {/* If user is full enrolled, he is allowed to redeem points. */}
            {isFullyEnrolled
              && (
              <AuraRedeemPoints
                mobile={mobile}
                pointsInAccount={points}
                cardNumber={cardNumber}
                formActive={formActive}
                cart={cart}
              />
              )}
          </div>
          )}
      </>
    );
  }
}

export default AuraLoyaltyForm;
