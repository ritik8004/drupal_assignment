import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import ValidateEgiftCard from './components/ValidateEgiftCard';

export default class RedeemEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      codeSent: false,
      codeValidated: false,
    };
  }

  // Update the state variables.
  componentDidMount = () => {

  }

  // Perform code validation.
  handleCodeValidation = () => {
    // @todo To update code here once API is available.
  }

  // Send code to the email id.
  handleGetCode = () => {
    // @todo To update code here once API is available.

  }

  // Remove the added egift card.
  handleEgiftCartRemove = () => {
    // @todo To update code here once API is available.
  }

  render = () => {
    // Prepare the props based on the state values.
    const { codeSent, codeValidated } = this.state;

    return (
      <div className="redeem-egift-card">
        {/* TO update the Payment Method Icon here for egift. */}
        <PaymentMethodIcon methodName="egiftCart" />
        <div>{Drupal.t('Redeem eGift Card')}</div>
        <ConditionalView condition={!codeSent}>
          <ValidateEgiftCard
            callback={this.handleGetCode}
            codeSent={codeSent}
            codeValidated={codeValidated}
            redeemEgiftCardTitle={Drupal.t('Verify eGift Card to redeem from card balance')}
            redeemEgiftCardSubTitle={Drupal.t('We’ll send a verification code to your email to verify eGift card')}
            buttonText={Drupal.t('Get Code')}
          />
        </ConditionalView>
        <ConditionalView condition={codeSent}>
          <ValidateEgiftCard
            callback={this.handleCodeValidation}
            codeSent={codeSent}
            codeValidated={codeValidated}
            redeemEgiftCardTitle={Drupal.t('Verify eGift Card to redeem from card balance')}
            redeemEgiftCardSubTitle={Drupal.t('Verification code sent to xyz@xyz.com')}
            buttonText={Drupal.t('Verify')}
          />
        </ConditionalView>
        <ConditionalView condition={codeValidated}>
          <ValidateEgiftCard
            callback={this.handleEgiftCartRemove}
            codeSent={codeSent}
            codeValidated={codeValidated}
            redeemEgiftCardTitle={Drupal.t('Your eGift card is applied - KWD 280')}
            redeemEgiftCardSubTitle={Drupal.t('Remaining Balance - KWD 220.00')}
            buttonText={Drupal.t('Remove')}
          />
        </ConditionalView>
      </div>
    );
  }
}
