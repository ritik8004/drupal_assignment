import React from 'react';
import ConditionalView from '../common/components/conditional-view';
import PaymentMethodIcon from '../svg-component/payment-method-svg';
import { callEgiftApi, isEgiftUnsupportedPaymentMethod } from '../utilities/egift_util';
import GetEgiftCard from './components/GetEgiftCard';
import ValidateEgiftCard from './components/ValidateEgiftCard';
import ValidEgiftCard from './components/ValidEgiftCard';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../js/utilities/events';
import isEgiftCardEnabled from '../../../js/utilities/egiftCardHelper';

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
    // Allow other components to disable/enable redemption.
    document.addEventListener('changeEgiftRedemptionStatus', this.changeRedemptionStatus, false);
    // Change the redemption screen based on the cart redemption status.
    const { cart: cartData } = this.props;
    // Change the state of redeemption if egift is already available.
    if (hasValue(cartData.cart.totals.egiftRedeemedAmount)) {
      this.setState({
        codeSent: false,
        codeValidated: true,
      });
    }
    // Update the payment method.
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

  // Update the redemption status.
  changeRedemptionStatus = (status) => {
    this.setState({
      redemptionDisabled: status,
    });
  }

  // Perform code validation.
  handleCodeValidation = (code) => {
    // Call the otp verification API.
    showFullScreenLoader();
    let errors = false;

    const { egiftEmail } = this.state;
    if (code) {
      const response = callEgiftApi('eGiftVerifyOtp', 'GET', {}, {
        email: egiftEmail,
        otp: code,
      });
      // Call to Egift API always returns a Promise object.
      if (response instanceof Promise) {
        response.then((result) => {
          // Remove loader as response is now available.
          removeFullScreenLoader();
          // Proceed only if we don't have any errors.
          if (result.error === undefined && result.data !== undefined) {
            // Get updated cart response with updated egift card amount.
            this.setState({ codeValidated: result.data, codeSent: false });
          } else if (result.error) {
            errors = true;
          }
        });
      }
    }

    return !errors;
  }

  // Send code to the email id.
  handleGetCode = (egiftCardNumber, egiftEmailId) => {
    showFullScreenLoader();
    let errors = false;
    // Call api endpoint to send OTP.
    if (egiftCardNumber && egiftEmailId) {
      const response = callEgiftApi('eGiftSendOtp', 'GET', {}, {
        email: egiftEmailId,
      });
      // Proceed only if we don't have any errors.
      if (response instanceof Promise) {
        response.then((result) => {
          // Remove loader as response is now available.
          removeFullScreenLoader();
          if (result.error === undefined && result.data !== undefined && result.status === 200) {
            this.setState({
              codeSent: result.data,
              egiftEmail: egiftEmailId,
              egiftCardNumber,
            });
          } else {
            errors = true;
          }
        });
      }
    }

    return !errors;
  }

  // Remove the added egift card.
  handleEgiftCardRemove = () => {
    const { cart: cartData } = this.props;
    const postData = {
      redeem_points: {
        action: 'remove_points',
        quote_id: cartData.cart.cart_id_int,
      },
    };
    let errors = true;
    showFullScreenLoader();
    // Invoke the redemption API.
    const response = callEgiftApi('eGiftRedemption', 'POST', postData);
    if (response instanceof Promise) {
      // Handle the error and success message after the egift card is removed
      // from the cart.
      response.then((result) => {
        removeFullScreenLoader();
        if (result.error !== undefined) {
          errors = false;
        } else {
          // Reset the state to move back to initial redeem stage.
          this.setState({
            codeSent: false,
            codeValidated: false,
          });
          // Update the cart total.
          showFullScreenLoader();
          const currentCart = window.commerceBackend.getCart(true);

          if (currentCart instanceof Promise) {
            currentCart.then((data) => {
              removeFullScreenLoader();
              if (data.data !== undefined && data.data.error === undefined) {
                if (data.status === 200) {
                  // Update Egift card line item.
                  dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
                  removeFullScreenLoader();
                }
              }
            });
          }
        }
      });
    }

    return !errors;
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
    const { cart: cartData } = this.props;

    return (
      <div className="redeem-egift-card">
        {/* TO update the Payment Method Icon here for egift. */}
        <PaymentMethodIcon methodName="egiftCart" />
        <div>{Drupal.t('Redeem eGift Card', {}, { context: 'egift' })}</div>
        <ConditionalView condition={!codeSent && !codeValidated}>
          <GetEgiftCard
            getCode={this.handleGetCode}
            egiftEmail={egiftEmail}
            egiftCardNumber={egiftCardNumber}
            redemptionDisabled={redemptionDisabled}
          />
        </ConditionalView>
        <ConditionalView condition={codeSent}>
          <ValidateEgiftCard
            resendCode={this.handleGetCode}
            codeValidation={this.handleCodeValidation}
            egiftEmail={egiftEmail}
            egiftCardNumber={egiftCardNumber}
            changeEgiftCard={this.handleEgiftCardRemove}
          />
        </ConditionalView>
        <ConditionalView condition={codeValidated}>
          <ValidEgiftCard
            removeCard={this.handleEgiftCardRemove}
            egiftCardNumber={egiftCardNumber}
            egiftEmail={egiftEmail}
            cart={cartData.cart}
          />
        </ConditionalView>
      </div>
    );
  }
}
