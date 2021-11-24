import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
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
    // @todo To update code here once API is available.
    if (code) {
      this.setState({ codeValidated: true, codeSent: false });
    }
  }

  // Send code to the email id.
  handleGetCode = (egiftCardNumber, egiftEmailId) => {
    // @todo To update code here once API is available.
    if (egiftCardNumber && egiftEmailId) {
      this.setState({ codeSent: true, egiftEmail: egiftEmailId });
    }
  }

  // Remove the added egift card.
  handleEgiftCardRemove = () => {
    // @todo To update code here once API is available.
  }

  render = () => {
    // Prepare the props based on the state values.
    const { codeSent, codeValidated, egiftEmail } = this.state;

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
          />
        </ConditionalView>
      </div>
    );
  }
}
