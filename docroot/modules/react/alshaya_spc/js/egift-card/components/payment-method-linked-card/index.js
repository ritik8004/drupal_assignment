import React from 'react';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import ValidEgiftCard from '../ValidEgiftCard';
import ValidateEgiftCard from "../ValidEgiftCard";

class PaymentMethodLinkedCard extends React.Component {
  constructor(props) {
    super(props);
    this.egiftCardhelper = new ValidEgiftCard();
    this.state = {
      open: false,
      remainingAmount: 0,
      exceedingAmount: 0,
      egiftcardbalance: 0,
      apiCallFlag: false, // Set when API call is complete.
      setChecked: false,
      egiftcardvalidity: '',
      codeSent: false,
      codeValidated: false,
    };
  }

  async componentDidMount() {
    const {cardDetails} = this.egiftCardhelper.getUserLinkedCardNumber;
    console.log(cardDetails);
    const params = { 'accountInfo': { 'cardNumber': 4250120656063430, 'email': 'm.test@gmail.com'} };
    const response = await callMagentoApi('/V1/egiftcard/getBalance', 'POST', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        egiftcardbalance: response.data.items.current_balance,
        egiftcardvalidity: response.data.items.expiry_date,
      });
    }
    this.setState({
      apiCallFlag: true,
    });
    this.setState({
      // @todo get amount and update the state.
      egiftcardbalance: 500,
    });
  }

  openModal = (e) => {
    console.log('open');
    this.setState({
      open: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    console.log('close');
    this.setState({
      open: false,
    });
  };

  // Update egift amount.
  handleExceedingAmount = (Amt) => {
    if (Amt > 0){
      this.setState({
        exceedingAmount: Amt,
      });
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

  render() {
    const { open, egiftcardbalance, setChecked, remainingAmount, exceedingAmount } = this.state;
    const { cart, isSelected, animationOffset, changePaymentMethod, disablePaymentMethod } = this.props;
    const animationDelayValue = `${0.4 + animationOffset}s`;
    let additionalClasses = '';
    // Add `in-active` class if disablePaymentMethod property is true.
    additionalClasses = disablePaymentMethod
      ? `${additionalClasses} in-active`
      : additionalClasses;

    return (
      <>
        <div className={`payment-method fadeInUp payment-method-checkout_com_egift_linked_card ${additionalClasses}`} style={{ animationDelay: animationDelayValue }} onClick={() => changePaymentMethod()}>
          <div className="payment-method-top-panel">
            <input type="checkbox" id="link-egift-card" checked={setChecked} />
             <div className="payment-method-label-wrapper">
               <label className="checkbox-sim checkbox-label egift-link-card-label">
                 {
                   Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                     {
                       '@currencyCode': getCurrencyCode(), '@amount': egiftcardbalance,
                     }, { context: 'egift' })
                }
               </label>
             </div>
            <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
              <ConditionalView condition={setChecked === true && egiftcardbalance > 0}>*/}
                <ConditionalView conditional={open}>
                  <UpdateEgiftCardAmount
                    closeModal={this.closeModal}
                    open={open}
                    amount={egiftcardbalance}
                    remainingAmount={remainingAmount}
                    updateAmount={this.egiftCardhelper.handleAmountUpdate}
                    handleExceedingAmount={this.handleExceedingAmount}
                    cartTotal={cart}
                  />

                </ConditionalView>
                  <div className="spc-payment-method-desc">
                    <div className="desc-content">
                      <ConditionalView condition={egiftcardbalance && exceedingAmount > 0}>
                      {
                        Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': exceedingAmount,
                          }, { context: 'egift' })
                      }
                      </ConditionalView>
                      <ConditionalView condition={setChecked === true && egiftcardbalance === 0}>
                        {
                          Drupal.t('Linked card has 0 Balance please use another payment method to complete purchase', {}, { context: 'egift' })
                        }
                      </ConditionalView>
                    </div>
                  </div>
                <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
              </ConditionalView>
            </div>
          </div>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedCard;
