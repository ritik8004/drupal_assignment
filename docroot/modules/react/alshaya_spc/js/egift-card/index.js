import React from 'react';
import ConditionalView from '../common/components/conditional-view';
import PaymentMethodIcon from '../svg-component/payment-method-svg';
import { callEgiftApi } from '../utilities/egift_util';
import GetEgiftCard from './components/GetEgiftCard';
import ValidateEgiftCard from './components/ValidateEgiftCard';
import ValidEgiftCard from './components/ValidEgiftCard';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../js/utilities/conditionsUtility';

export default class RedeemEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      codeSent: false,
      codeValidated: false,
      egiftEmail: '',
      egiftCardNumber: '',
    };
  }

  // Update the state variables.
  componentDidMount = () => {
    // Change the redemption screen based on the cart redemption status.
    const { cart: cartData } = this.props;
    // Change the state of redeemption if egift is already available.
    if (hasValue(cartData.cart.totals.egiftRedeemedAmount)) {
      this.setState({
        codeSent: false,
        codeValidated: true,
      });
    }
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
            quoteId={cartData.cart.cart_id_int}
            egiftCardNumber={egiftCardNumber}
            egiftEmail={egiftEmail}
            cart={cartData.cart}
          />
        </ConditionalView>
      </div>
    );
  }
}
