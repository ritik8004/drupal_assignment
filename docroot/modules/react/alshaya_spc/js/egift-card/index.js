import React from 'react';
import ConditionalView from '../common/components/conditional-view';
import PaymentMethodIcon from '../svg-component/payment-method-svg';
import {
  getTopUpQuote,
  isEgiftRedemptionDone,
  isEgiftUnsupportedPaymentMethod,
  isValidResponse,
  isValidResponseWithFalseResult,
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
import { isUserAuthenticated } from '../../../js/utilities/helper';
import logger from '../../../js/utilities/logger';
import { getDefaultErrorMessage } from '../../../js/utilities/error';

export default class RedeemEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      codeSent: false,
      codeValidated: false,
      egiftEmail: '',
      egiftCardNumber: '',
      redemptionDisabled: false,
    };
  }

  // Update the state variables.
  componentDidMount = () => {
    // On payment method update, we refetch the cart to get payment method.
    document.addEventListener('refreshCartOnPaymentMethod', this.changeRedemptionStatusBasedOnPaymentMethod, false);
    // Change the redemption screen based on the cart redemption status.
    const { cart: cartData } = this.props;
    // Change the state of redeemption if egift is already available.
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

  // Perform code validation.
  handleCodeValidation = async (code) => {
    const { egiftEmail, egiftCardNumber } = this.state;
    // Default result object.
    let result = {
      error: false,
      message: '',
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
          amount: cartData.cart.cart_total,
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
        };
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
      } else if (isValidResponseWithFalseResult(response)) {
        result = {
          error: true,
          message: response.data.response_message,
        };
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
    let quoteId = cartData.cart.cart_id;
    // Check if topup is applicable.
    const topUpQuote = getTopUpQuote();
    if (topUpQuote) {
      quoteId = topUpQuote.maskedQuoteId;
    }
    let postData = {
      redemptionRequest: {
        mask_quote_id: quoteId,
      },
    };
    // Change payload if authenticated user.
    if (isUserAuthenticated()) {
      postData = {
        redemptionRequest: {
          quote_id: cartData.cart.cart_id_int,
        },
      };
    }
    // Default result object.
    let result = {
      error: false,
      message: '',
    };
    showFullScreenLoader();
    // Invoke the redemption API.
    const response = await callEgiftApi('eGiftRemoveRedemption', 'POST', postData);
    if (isValidResponse(response)) {
      // Reset the state to move back to initial redeem stage.
      this.setState({
        codeSent: false,
        codeValidated: false,
        egiftCardNumber: '',
      });
      // Update the cart total.
      updatePriceSummaryBlock(refreshCart);
    } else if (isValidResponseWithFalseResult(response)) {
      result = {
        error: true,
        message: response.data.response_message,
      };
      // Log error in datadog.
      logger.error('Error Response in remove eGiftRedemption. Action: @action Response: @response', {
        '@action': 'remove_redemption',
        '@response': response.data,
      });
      // Remove loader once the data response is available.
      removeFullScreenLoader();
    } else {
      result = {
        error: true,
        message: getDefaultErrorMessage(),
      };
      // Log error in datadog.
      logger.error('Error Response in remove eGiftRedemption. Action: @action Response: @response', {
        '@action': 'remove_redemption',
        '@response': response,
      });
      // Remove loader once the data response is available.
      removeFullScreenLoader();
    }

    return result;
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
    } = this.state;
    const { cart: cartData, refreshCart } = this.props;

    return (
      <div className="redeem-egift-card">
        {/* TO update the Payment Method Icon here for egift. */}
        <PaymentMethodIcon methodName="egiftCart" />
        <div>{Drupal.t('Redeem eGift Card', {}, { context: 'egift' })}</div>
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
    );
  }
}
