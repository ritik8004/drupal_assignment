import React from 'react';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import ValidEgiftCard from '../ValidEgiftCard';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import {callEgiftApi, getUserLinkedCardNumber} from '../../../utilities/egift_util';
import {removeFullScreenLoader, showFullScreenLoader} from "../../../../../js/utilities/showRemoveFullScreenLoader";

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
      redeemed: false,
      amount: 0,
      cardNumber: 0,
    };
  }

  async componentDidMount() {
    const cardDetails = getUserLinkedCardNumber();
    if (cardDetails.card_available) {
      // Invoke magento API to check if card has balance.
      const postData = { 'accountInfo': { 'cardNumber': cardDetails.card_number, 'email': drupalSettings.userDetails.userEmailID } };
      const response = callEgiftApi('eGiftGetBalance', 'POST', postData);
      if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
        this.setState({
          egiftcardbalance: response.data.items.current_balance,
          egiftcardvalidity: response.data.items.expiry_date,
          cardNumber: response.data.items.card_number,
        });
      }
      this.setState({
        apiCallFlag: true,
      });
    }else {
      this.setState({
        // @todo get amount and update the state.
        egiftcardbalance: 500,
        cardNumber: cardDetails.card_number,
      });
    }
  }

  openModal = (e) => {
    this.setState({
      open: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
  };

  // Update egift amount.
  handleExceedingAmount = (status, extraAmount, model) => {
    this.setState({
      redeemed: status,
      exceedingAmount: extraAmount,
      open: model,
    });
  };

  // Get redeemed amount.
  getRedeemAmount = (status, redeemAmount, model) => {
    this.setState({
      redeemed: status,
      amount: redeemAmount,
      open: model,
    });
  };

  // Handle Checkbox Change.
  handleCheckbox = (e) => {
    // Reset the state to move back to initial redeem stage.
    this.setState({
      setChecked: e.target.checked,
    });
    const {egiftcardbalance, cardNumber} = this.state;
    const {cart} = this.props
    if (e.target.checked === true) {
      this.setState({
        exceedingAmount: 0,
      });
      const postData = {
        redeem_points: {
          action: 'set_points',
          quote_id: cart.cart_id_int,
          amount: egiftcardbalance,
          card_number: cardNumber,
          payment_method: 'hps_payment',
        },
      };
        // Proceed only if postData object is available.
      if (postData) {
        showFullScreenLoader();
        // Invoke the redemption API to update the redeem amount.
        const response = callEgiftApi('eGiftRedemption', 'POST', postData);
        if (response instanceof Promise) {
          response.then((result) => {
            // Remove loader once result is available.
            removeFullScreenLoader();
            // if (result.error === undefined && result.status === 200) {
            if (result.status === 200) {
              if (cart.cart.cart_total <= egiftcardbalance) {
                this.setState({
                  redeemed: true,
                  amount: egiftcardbalance - cart.cart.cart_total,
                  open: false,
                });
              }
              if (cart.cart.cart_total > egiftcardbalance) {
                this.setState({
                  exceedingAmount: cart.cart.cart_total - egiftcardbalance,
                });
              }
              if (egiftcardbalance >= cart.cart.cart_total) {
                this.setState({
                  redeemed: true,
                  amount: egiftcardbalance - cart.cart.cart_total,
                  open: false,
                });
              }
              return true;
            }
            return false;
          });
        }
      }
    } else {
      this.setState({
        exceedingAmount: 0,
      });
      const postData = {
        redeem_points: {
          action: 'remove_points',
          quote_id: cart.cart_id_int,
        },
      };
      let errors = false;
      showFullScreenLoader();
      // Invoke the redemption API.
      const response = callEgiftApi('eGiftRedemption', 'POST', postData);
      if (response instanceof Promise) {
        // Handle the error and success message after the egift card is removed
        // from the cart.
        response.then((result) => {
          removeFullScreenLoader();
          if (result.status === 200) {
            this.getRedeemAmount(false, 0, false);
          }
          if (result.error !== undefined) {
            document.getElementById('egift_remove_card_error').innerHTML = Drupal.t('There was some error while removing the gift card. Please try again', {}, {context: 'egift'});
            errors = true;
          }
        });
      }
    }
  };

  render() {
    const { open, egiftcardbalance, setChecked, remainingAmount, exceedingAmount, redeemed, amount } = this.state;
    const { cart, isSelected, animationOffset, changePaymentMethod, disablePaymentMethod } = this.props;
    const cardDetails = getUserLinkedCardNumber();
    const egiftCardStatus = cardDetails.card_available;
    const cardNumber = cardDetails.card_number;
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
            <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleCheckbox} />
             <div className="payment-method-label-wrapper">
               <label className="checkbox-sim checkbox-label egift-link-card-label">
                 <ConditionalView condition={!redeemed}>
                   {
                     Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                       {
                         '@currencyCode': getCurrencyCode(), '@amount': egiftcardbalance,
                       }, { context: 'egift' })
                   }
                 </ConditionalView>
                 <ConditionalView condition={redeemed}>
                   {
                     Drupal.t('Pay using egift card (Remaining Balance: @currencyCode @amount)',
                       {
                         '@currencyCode': getCurrencyCode(), '@amount': amount,
                       }, { context: 'egift' })
                   }
                 </ConditionalView>
               </label>
             </div>
            <PaymentMethodIcon methodName="checkout_com_egift_linked_card" methodLabel="Egift Linked Card" />
            <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
              <ConditionalView condition={setChecked === true}>
                <ConditionalView conditional={open}>
                  <UpdateEgiftCardAmount
                    closeModal={this.closeModal}
                    open={open}
                    amount={egiftcardbalance}
                    remainingAmount={remainingAmount}
                    updateAmount={this.egiftCardhelper.handleAmountUpdate}
                    redeemAmount={this.getRedeemAmount}
                    handleExceedingAmount={this.handleExceedingAmount}
                    cart={cart.cart}
                    egiftCardNumber={cardNumber}
                  />
                </ConditionalView>
                  <div className="spc-payment-method-desc">
                    <div className="desc-content">
                      <ConditionalView condition={egiftcardbalance > 0 && exceedingAmount > 0}>
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
