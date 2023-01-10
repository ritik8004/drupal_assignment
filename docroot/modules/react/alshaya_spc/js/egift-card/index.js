import React from 'react';
import ConditionalView from '../common/components/conditional-view';
import {
  getEgiftCartTotal,
  isEgiftRedemptionDone,
  isEgiftUnsupportedPaymentMethod,
  isValidResponse,
  isValidResponseWithFalseResult,
  removeEgiftRedemption,
  updatePriceSummaryBlock,
} from '../utilities/egift_util';
import { callEgiftApi } from '../../../js/utilities/egiftCardHelper';
import GetEgiftCard from './components/GetEgiftCard';
import ValidateEgiftCard from './components/ValidateEgiftCard';
import ValidEgiftCard from './components/ValidEgiftCard';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import { isEgiftCardEnabled } from '../../../js/utilities/util';
import dispatchCustomEvent from '../../../js/utilities/events';
import logger from '../../../js/utilities/logger';
import { getDefaultErrorMessage } from '../../../js/utilities/error';
import RedeemEgiftSVG from '../svg-component/redeem-egift-svg';
import { isFullPaymentDoneByAura } from '../aura-loyalty/components/utilities/checkout_helper';
import { isUserAuthenticated } from '../backend/v2/utility';

export default class RedeemEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      codeSent: false,
      codeValidated: false,
      egiftEmail: '',
      egiftCardNumber: '',
      redemptionDisabled: false,
      active: false,
    };
  }

  // Update the state variables.
  componentDidMount = () => {
    // On payment method update, we refetch the cart to get payment method.
    document.addEventListener('refreshCartOnPaymentMethod', this.changeRedemptionStatusBasedOnPaymentMethod, false);
    // Change the redemption screen based on the cart redemption status.
    const { cart: cartData } = this.props;
    // Change the state of redemption if egift is already available.
    if (isEgiftRedemptionDone(cartData.cart)) {
      // Extract the card number for further calculation.
      this.setState({
        codeSent: false,
        codeValidated: true,
        egiftCardNumber: cartData.cart.totals.egiftCardNumber,
      });
    }
    // Update the redemption status based on selected payment method.
    if (isEgiftCardEnabled()
      && hasValue(cartData.cart.payment)
      && hasValue(cartData.cart.payment.method)) {
      this.setState({
        redemptionDisabled: isEgiftUnsupportedPaymentMethod(cartData.cart.payment.method),
      });
    }
  }

  // Update the payment method.
  changeRedemptionStatusBasedOnPaymentMethod = (event) => {
    const currentCart = event.detail.cart;
    if (hasValue(currentCart.payment)
      && hasValue(currentCart.payment.method)) {
      // Updated the state of redemption.
      const redemptionStatus = isEgiftUnsupportedPaymentMethod(currentCart.payment.method);
      this.setState({
        redemptionDisabled: redemptionStatus,
      });
      // If redemption is disabled then move redemption status to initial stage.
      if (redemptionStatus) {
        this.setState({
          codeSent: false,
          codeValidated: false,
        });
      }
    }
  }

  // Update the redemption accordion status.
  changeRedemptionAccordionStatus = () => {
    const { active } = this.state;
    this.setState({ active: !active });
  }

  // Returns event action name.
  getEventAction = () => {
    if (isUserAuthenticated()) {
      return 'card_logged_in';
    }
    return 'card_guest';
  }

  // Perform code validation.
  handleCodeValidation = async (code) => {
    const { egiftEmail, egiftCardNumber } = this.state;
    // Default result object.
    let result = {
      error: false,
      message: '',
      gtmMessage: '',
    };
    if (code) {
      // Extract cart to get card_id.
      const { cart: cartData, refreshCart } = this.props;
      // Call the otp verification API.
      showFullScreenLoader();
      const response = await callEgiftApi('eGiftRedemption', 'POST', {
        redeem_points: {
          action: 'set_points',
          quote_id: cartData.cart.cart_id_int,
          amount: getEgiftCartTotal(cartData.cart),
          card_number: egiftCardNumber,
          payment_method: 'hps_payment',
          card_type: 'guest',
          otp: code,
          email: egiftEmail,
        },
      });
      // Proceed only if the response is valid.
      if (isValidResponse(response)) {
        const currentCart = window.commerceBackend.getCart(true);
        if (currentCart instanceof Promise) {
          currentCart.then((data) => {
            if (data.data !== undefined && data.data.error === undefined) {
              if (data.status === 200) {
                // Update Egift card line item.
                dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
                // Refresh the cart in checkout.
                const formatedCart = window.commerceBackend.getCartForCheckout();
                if (formatedCart instanceof Promise) {
                  formatedCart.then((cart) => {
                    refreshCart({ cart: cart.data });
                    // Change the state of redemption once cart is updated.
                    this.setState({ codeValidated: response.data.response_type, codeSent: false });
                    // Remove the loader once state is updated.
                    removeFullScreenLoader();
                    // Dispatch egift guest redemption verification for GTM.
                    dispatchCustomEvent('egiftCardRedeemed', {
                      label: 'egift_verification',
                      action: this.getEventAction(),
                    });
                  });
                }
              }
            }
          });
        }
      } else if (isValidResponseWithFalseResult(response)) {
        result = {
          error: true,
          message: response.data.response_message,
          gtmMessage: hasValue(response.data.gtm_response_message)
            ? response.data.gtm_response_message
            : response.data.response_message,
        };
        // If 'gtm_response_message' attribute is missing log warning in
        // datadog for debugging, in this case event action is sent in
        // 'Arabic' to GTM datalayer for Arabic site.
        if (!hasValue(response.data.gtm_response_message)) {
          logger.warning('Missing field gtm_response_message in eGiftRedemption API response. Action: @action Response: @response', {
            '@action': 'set_points',
            '@response': response.data,
          });
        }

        // Log error in datadog.
        logger.error('Error Response in eGiftRedemption for guest card. Action: @action CardNumber: @cardNumber Response: @response', {
          '@action': 'set_points',
          '@cardNumber': egiftCardNumber,
          '@response': response.data,
        });
        // Remove the loader once we have the response.
        removeFullScreenLoader();
      } else {
        result = {
          error: true,
          message: getDefaultErrorMessage(),
          gtmMessage: 'Sorry, something went wrong and we are unable to process your request right now. Please try again later.',
        };
        // Log error in datadog.
        logger.error('Error Response in eGiftRedemption for guest card. Action: @action CardNumber: @cardNumber Response: @response', {
          '@action': 'set_points',
          '@cardNumber': egiftCardNumber,
          '@response': response,
        });
        // Remove the loader once we have the response.
        removeFullScreenLoader();
      }
    }

    return result;
  }

  // Send code to the email id.
  handleGetCode = async (egiftCardNumber) => {
    showFullScreenLoader();
    // Default result object.
    let result = {
      error: false,
      message: '',
      gtmMessage: '',
    };
    // Call api endpoint to send OTP.
    if (egiftCardNumber) {
      // Extract the card_id from props.
      const { cart: cartData } = this.props;
      const response = await callEgiftApi('eGiftRedemption', 'POST', {
        redeem_points: {
          action: 'send_otp',
          quote_id: cartData.cart.cart_id_int,
          card_number: egiftCardNumber,
        },
      });
      // Proceed only if we don't have any errors.
      if (isValidResponse(response)) {
        this.setState({
          codeSent: response.data.response_type,
          egiftEmail: response.data.email,
          egiftCardNumber: response.data.card_number,
        });
        // Dispatch egift guest redemption interaction for GTM.
        dispatchCustomEvent('egiftCardRedeemed', {
          label: 'egift_interaction',
          action: this.getEventAction(),
        });
      } else if (isValidResponseWithFalseResult(response)) {
        result = {
          error: true,
          message: response.data.response_message,
          gtmMessage: hasValue(response.data.gtm_response_message)
            ? response.data.gtm_response_message
            : response.data.response_message,
        };
        // If 'gtm_response_message' attribute is missing log warning in
        // datadog for debugging, in this case event action is sent in
        // 'Arabic' to GTM datalayer for Arabic site.
        if (!hasValue(response.data.gtm_response_message)) {
          logger.warning('Missing field gtm_response_message in eGiftRedemption API response. Action: @action Response: @response', {
            '@action': 'send_otp',
            '@response': response.data,
          });
        }

        // Log error in datadog.
        logger.error('Error Response in eGiftRedemption for guest card. Action: @action CardNumber: @cardNumber Response: @response', {
          '@action': 'send_otp',
          '@cardNumber': egiftCardNumber,
          '@response': response.data,
        });
      } else {
        result = {
          error: true,
          message: getDefaultErrorMessage(),
          gtmMessage: 'Sorry, something went wrong and we are unable to process your request right now. Please try again later.',
        };
        // Log error in datadog.
        logger.error('Error Response in eGiftRedemption for guest card. Action: @action CardNumber: @cardNumber Response: @response', {
          '@action': 'send_otp',
          '@cardNumber': egiftCardNumber,
          '@response': response,
        });
      }
    }
    // Remove loader once processing is done.
    removeFullScreenLoader();

    return result;
  }

  // Remove the added egift card.
  handleEgiftCardRemove = async () => {
    const { cart: cartData, refreshCart } = this.props;
    // Show loader while doing API call.
    showFullScreenLoader();
    // Remove redemption from the cart.
    const response = await removeEgiftRedemption(cartData.cart);
    if (!response.error) {
      // Reset the state to move back to initial redeem stage.
      this.setState({
        codeSent: false,
        codeValidated: false,
        egiftCardNumber: '',
      });
      // Update the cart total.
      updatePriceSummaryBlock(refreshCart);
      // Dispatch egift guest redemption removed for GTM.
      dispatchCustomEvent('egiftCardRedeemed', {
        label: 'egift_remove',
        action: this.getEventAction(),
      });
    }

    return response;
  }

  // Change the egift card.
  handleChangeEgiftCard = () => {
    // Reset the state to move back to initial redeem stage.
    this.setState({
      codeSent: false,
      codeValidated: false,
    });
  }

  render = () => {
    // Prepare the props based on the state values.
    const {
      codeSent,
      codeValidated,
      egiftEmail,
      egiftCardNumber,
      redemptionDisabled,
      active,
    } = this.state;
    const { cart: cartData, refreshCart } = this.props;
    const activeClass = active || codeValidated ? 'active' : '';
    const codeValidationClass = codeValidated ? 'has-validated-code' : '';
    // Disable redemption if redemptionDisable is set as true or redemption is
    // done by linked card or full payment is done by AURA.
    const disabledRedemptionClass = redemptionDisabled
      || isEgiftRedemptionDone(cartData.cart, 'linked')
      || isFullPaymentDoneByAura(cartData) ? 'in-active' : '';

    return (
      <div className="redeem-egift-card">
        {/* TO update the Payment Method Icon here for egift. */}
        <div className={`redeem-egift-card-header-container ${activeClass} ${codeValidationClass} ${disabledRedemptionClass}`} onClick={() => this.changeRedemptionAccordionStatus()}>
          <RedeemEgiftSVG />
          <div className="redeem-egift-card-header-label">{Drupal.t('Redeem eGift Card', {}, { context: 'egift' })}</div>
          <span className="accordion-icon" />
        </div>
        <div className="redeem-egift-card-content">
          <ConditionalView condition={!codeSent && !codeValidated}>
            <GetEgiftCard
              getCode={this.handleGetCode}
              egiftCardNumber={egiftCardNumber}
              redemptionDisabled={redemptionDisabled}
              cart={cartData.cart}
            />
          </ConditionalView>
          <ConditionalView condition={codeSent}>
            <ValidateEgiftCard
              resendCode={this.handleGetCode}
              codeValidation={this.handleCodeValidation}
              egiftEmail={egiftEmail}
              egiftCardNumber={egiftCardNumber}
              changeEgiftCard={this.handleChangeEgiftCard}
            />
          </ConditionalView>
          <ConditionalView condition={codeValidated}>
            <ValidEgiftCard
              removeCard={this.handleEgiftCardRemove}
              cart={cartData.cart}
              egiftCardNumber={egiftCardNumber}
              refreshCart={refreshCart}
            />
          </ConditionalView>
        </div>
      </div>
    );
  }
}
