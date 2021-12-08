import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import ValidEgiftCard from '../ValidEgiftCard';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import {removeFullScreenLoader, showFullScreenLoader} from "../../../../../js/utilities/showRemoveFullScreenLoader";
import {callEgiftApi} from '../../../utilities/egift_util';

class PaymentMethodLinkedCard extends React.Component {
  constructor(props) {
    super(props);
    this.egiftCardhelper = new ValidEgiftCard();
    this.state = {
      open: false,
      exceedingAmount: 0,
      remainingAmount: 0,
      egiftCardBalance: 0,
      setChecked: false,
      egiftCardValidity: false,
      redeemed: false,
      amount: 0,
    };
  }

  async componentDidMount() {
    const { linkCardStatus, cardNumber } = this.props;
    if (linkCardStatus === true) {
      // Invoke magento API to check if card has balance.drupalSettings.userDetails.userEmailID
      const postData = { 'accountInfo': { 'cardNumber': cardNumber, 'email': 'vasanthkumaaaar.a@gmail.com' } };
      const response = callEgiftApi('eGiftGetBalance', 'POST', postData);
      if (response instanceof Promise) {
        response.then((result) => {
          if (typeof result.data !== 'undefined' && typeof result.error === 'undefined') {
            let currentTime = Math.floor(Date.now()/1000);
            this.setState({
              egiftCardBalance: result.data.current_balance,
              egiftCardValidity: (currentTime < result.data.expiry_date_timestamp),
            });
          }
          // Handle error response.
          if (result.error) {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId. Response: @response', {
              '@cardNumber': postData.cardNumber,
              '@email': postData.email,
              '@response': result.data,
            });
          }
        });
      }
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
    const {egiftCardBalance} = this.state;
    const {cardNumber} = this.props;
    const {cart} = this.props
    if (e.target.checked === true) {
      this.setState({
        exceedingAmount: 0,
      });
      const postData = {
        redeem_points: {
          action: 'set_points',
          quote_id: cart.cart.cart_id_int,
          amount: cart.cart.totals.base_grand_total,
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
              if (cart.cart.cart_total <= egiftCardBalance) {
                this.setState({
                  redeemed: true,
                  amount: egiftCardBalance - cart.cart.cart_total,
                  open: false,
                });
              }
              if (cart.cart.cart_total > egiftCardBalance) {
                this.setState({
                  exceedingAmount: cart.cart.cart_total - egiftCardBalance,
                });
              }
              if (egiftCardBalance >= cart.cart.cart_total) {
                this.setState({
                  redeemed: true,
                  amount: egiftCardBalance - cart.cart.cart_total,
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
          quote_id: cart.cart.cart_id_int,
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
    const { open, egiftCardBalance, remainingAmount, setChecked, exceedingAmount, redeemed, amount, egiftCardValidity } = this.state;
    const { cart, animationOffset, changePaymentMethod, disablePaymentMethod, cardNumber } = this.props;
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
                <ConditionalView condition={egiftCardValidity === false}>
                  {
                    Drupal.t('Card is expired', {}, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={!redeemed}>
                  {
                    Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': egiftCardBalance,
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
                    cardBalance={egiftCardBalance}
                    remainingAmount={remainingAmount}
                    amount={cart.cart.totals.base_grand_total}
                    updateAmount={this.egiftCardhelper.handleAmountUpdate}
                    redeemAmount={this.getRedeemAmount}
                    handleExceedingAmount={this.handleExceedingAmount}
                    cart={cart.cart}
                    egiftCardNumber={cardNumber}
                  />
                </ConditionalView>
                <div className="spc-payment-method-desc">
                  <div className="desc-content">
                    <ConditionalView condition={egiftCardValidity === false}>
                      {
                        Drupal.t('Card is expired please use another payment method to complete purchase', {}, { context: 'egift' })
                      }
                    </ConditionalView>
                    <ConditionalView condition={egiftCardBalance > 0 && exceedingAmount > 0}>
                      {
                        Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': exceedingAmount,
                          }, { context: 'egift' })
                      }
                    </ConditionalView>
                    <ConditionalView condition={setChecked === true && egiftCardBalance === 0}>
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
