import React from 'react';
import logger from '../../../js/utilities/logger';
import ConditionalView from '../common/components/conditional-view';
import PaymentMethodIcon from '../svg-component/payment-method-svg';
import { callEgiftApi } from '../utilities/egift_util';
import GetEgiftCard from './components/GetEgiftCard';
import ValidateEgiftCard from './components/ValidateEgiftCard';
import ValidEgiftCard from './components/ValidEgiftCard';

export default class RedeemEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      codeSent: false,
      codeValidated: false,
      egiftEmail: '',
    };
  }

  // Update the state variables.
  componentDidMount = () => {

  }

  // Perform code validation.
  handleCodeValidation = (code) => {
    // Call the otp verification API.
    if (code) {
      const response = callEgiftApi('eGiftVerifyOtp', 'GET', {
        email: egiftEmailId,
        otp: code,
      });
      // Proceed only if we don't have any errors.
      if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
        this.setState({ codeValidated: response.data, codeSent: false });
      } else if (response.data.error) {
        document.getElementById('egift_verification_code_error').innerHTML = response.data.error.message;
        return false;
      }
    }

    return true;
  }

  // Send code to the email id.
  handleGetCode = (egiftCardNumber, egiftEmailId) => {
    // Call api endpoint to send OTP.
    if (egiftCardNumber && egiftEmailId) {
      const response = callEgiftApi('eGiftSendOtp', 'GET', {
        email: egiftEmailId,
      });
      // Proceed only if we don't have any errors.
      if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
        this.setState({ codeSent: response.data, egiftEmail: egiftEmailId });
      }
    }
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
    const { codeSent, codeValidated, egiftEmail } = this.state;
    const { cart: cartData } = this.props;

    return (
      <div className="redeem-egift-card">
        {/* TO update the Payment Method Icon here for egift. */}
        <PaymentMethodIcon methodName="egiftCart" />
        <div>{Drupal.t('Redeem eGift Card', {}, { context: 'egift' })}</div>
        <ConditionalView condition={!codeSent && !codeValidated}>
          <GetEgiftCard
            getCode={this.handleGetCode}
          />
        </ConditionalView>
        <ConditionalView condition={codeSent}>
          <ValidateEgiftCard
            codeValidation={this.handleCodeValidation}
            emailId={egiftEmail}
          />
        </ConditionalView>
        <ConditionalView condition={codeValidated}>
          <ValidEgiftCard
            removeCard={this.handleEgiftCardRemove}
            quoteId={cartData.cart.cart_id_int}
          />
        </ConditionalView>
      </div>
    );
  }
}
