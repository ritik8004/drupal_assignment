import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import ValidEgiftCard from '../ValidEgiftCard';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { callEgiftApi } from '../../../utilities/egift_util';
import isEgiftCardEnabled from '../../../../../js/utilities/egiftCardHelper';

class PaymentMethodLinkedEgift extends React.Component {
  constructor(props) {
    super(props);
    this.egiftCardhelper = new ValidEgiftCard();
    this.state = {
      // OpenModal.
      openModal: false,
      // State to set if user balance is low.
      exceedingAmount: 0,
      // State to set remaining balance.
      remainingAmount: 0,
      // State to set card balance.
      egiftCardBalance: 0,
      // State to handle checkbox.
      setChecked: false,
      // State to check card validity.
      isEgiftCardValid: false,
      // State to check if redemption performed already.
      redeemed: false,
      // State to get the amount from Modal.
      amount: 0,
      // State to check if user has linked card.
      linkedEgiftCard: false,
      // State to get linked card number.
      linkedEgiftCardNumber: '',
      // State to perform api call.
      apiWait: false,
      // State to get api error message.
      apiErrorMessage: '',
    };
  }

  componentDidMount() {
    // Check if egift module enabled.
    if (isEgiftCardEnabled()) {
      const { cart } = this.props;
      let params = { email: drupalSettings.userDetails.userEmailID };
      // Invoke magento API to get the user card number.
      const response = callEgiftApi('eGiftHpsSearch', 'GET', {}, params);
      if (response instanceof Promise) {
        response.then((result) => {
          // eslint-disable-next-line max-len
          if (result.status === 200 && result.data.card_number !== null && result.data.response_type !== false) {
            this.setState({
              linkedEgiftCard: true,
              linkedEgiftCardNumber: result.data.card_number,
              apiWait: true,
            });
          }
          // Handle error response.
          // eslint-disable-next-line max-len
          if (result.status === 200 && result.data.account_id === null && result.data.response_type === false) {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId . Response: @response', {
              '@emailId': params.email,
              '@response': result.data.response_message,
            });
            this.setState({
              apiWait: false,
              apiErrorMessage: result.data.response_message,
            });
          }
        });
      }
      const { linkedEgiftCardNumber } = this.state;
      // Invoke magento API to check if card has balance.
      // eslint-disable-next-line max-len
      let postData = { accountInfo: { cardNumber: linkedEgiftCardNumber, email: drupalSettings.userDetails.userEmailID } };
      const balanceResponse = callEgiftApi('eGiftGetBalance', 'POST', postData);
      if (balanceResponse instanceof Promise) {
        balanceResponse.then((result) => {
          // eslint-disable-next-line max-len
          if (result.status === 200 && result.data.current_balance !== null && result.data.response_type !== false) {
            let currentTime = Math.floor(Date.now() / 1000);
            this.setState({
              egiftCardBalance: result.data.current_balance,
              isEgiftCardValid: (currentTime < result.data.expiry_date_timestamp),
            });
            // Handle if user already performed redemption.
            if (cart.cart.totals.extension_attributes.hps_redeemed_amount > 0) {
              if (cart.cart.cart_total > result.data.current_balance) {
                // eslint-disable-next-line max-len
                this.handleExceedingAmount(true, cart.cart.cart_total - result.data.current_balance, false);
              }
              if (cart.cart.cart_total <= result.data.current_balance) {
                // eslint-disable-next-line max-len
                this.getRedeemAmount(true, result.data.current_balance - cart.cart.cart_total, false);
              }
              this.setState({
                apiWait: true,
                redeemed: true,
                setChecked: true,
              });
            }
          }
          // Handle error response.
          // eslint-disable-next-line max-len
          if (result.status === 200 && result.data.account_id === null && result.data.response_type === false) {
            logger.error('Error while calling the eGiftGetBalance. CardNumber: @cardNumber . Response: @response', {
              '@cardNumber': postData.accountInfo.cardNumber,
              '@response': result.data.response_message,
            });
            this.setState({
              apiWait: false,
              apiErrorMessage: result.data.response_message,
            });
          }
        });
      }
    }
  }

  openModal = (e) => {
    this.setState({
      openModal: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      openModal: false,
    });
  };

  // Handle exceeding amount scenario.
  handleExceedingAmount = (status, extraAmount, model) => {
    this.setState({
      redeemed: status,
      exceedingAmount: extraAmount,
      openModal: model,
    });
  };

  // Get redeemed amount.
  getRedeemAmount = (status, redeemAmount, model) => {
    this.setState({
      redeemed: status,
      amount: redeemAmount,
      openModal: model,
    });
  };

  // Handle Onclick.
  handleOnClick = (e) => {
    // Reset the state to move back to initial redeem stage.
    this.setState({
      setChecked: e.target.checked,
    });
    const { linkedEgiftCardNumber, egiftCardBalance } = this.state;
    const { cart } = this.props;
    if (e.target.checked === true) {
      this.setState({
        exceedingAmount: 0,
      });
      let postData = {
        redeem_points: {
          action: 'set_points',
          quote_id: cart.cart.cart_id_int,
          amount: cart.cart.totals.base_grand_total,
          cardNumber: linkedEgiftCardNumber,
          payment_method: 'hps_payment',
          email: drupalSettings.userDetails.userEmailID,
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
            // eslint-disable-next-line max-len
            if (result.status === 200 && result.data.redeemed_amount !== null && result.data.response_type !== false) {
              if (cart.cart.cart_total <= egiftCardBalance) {
                this.setState({
                  redeemed: true,
                  amount: egiftCardBalance - cart.cart.cart_total,
                  openModal: false,
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
                  openModal: false,
                });
              }
            }
            if (result.status === 200 && result.data.response_type === false) {
              logger.error('Error while calling the eGiftRedemption. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': postData.redeem_points.action,
                '@cardNumber': postData.redeem_points.cardNumber,
                '@response': result.data.response_message,
              });
              this.setState({
                apiWait: false,
                apiErrorMessage: result.data.response_message,
              });
            }
          });
        }
      }
    } else {
      this.setState({
        exceedingAmount: 0,
      });
      let postData = {
        redeem_points: {
          action: 'remove_points',
          quote_id: cart.cart.cart_id_int,
        },
      };
      showFullScreenLoader();
      // Invoke the redemption API.
      const response = callEgiftApi('eGiftRedemption', 'POST', postData);
      if (response instanceof Promise) {
        // Handle the error and success message after the egift card is removed
        // from the cart.
        response.then((result) => {
          removeFullScreenLoader();
          if (result.status === 200 && result.data.response_type !== false) {
            this.getRedeemAmount(false, 0, false);
          }
          if (result.data.response_type === false) {
            logger.error('Error while calling the cancel eGiftRedemption. Action: @action Response: @response', {
              '@action': postData.redeem_points.action,
              '@response': result.data.response_message,
            });
            this.setState({
              apiWait: false,
              apiErrorMessage: result.data.response_message,
            });
          }
        });
      }
    }
  };

  render() {
    const {
      // eslint-disable-next-line max-len
      openModal, egiftCardBalance, remainingAmount, apiErrorMessage, setChecked, exceedingAmount, redeemed, apiWait, linkedEgiftCard, amount, linkedEgiftCardNumber, isEgiftCardValid,
    } = this.state;
    const { cart, animationOffset } = this.props;
    const animationDelayValue = `${0.4 + animationOffset}s`;
    if (!apiWait && !linkedEgiftCard) {
      return (<></>);
    }

    return (
      <>
        <div className="payment-method fadeInUp payment-method-checkout_com_egift_linked_card" style={{ animationDelay: animationDelayValue }}>
          <div className="payment-method-top-panel">
            <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleOnClick} />
            <div className="payment-method-label-wrapper">
              <label className="checkbox-sim checkbox-label egift-link-card-label">
                <ConditionalView condition={isEgiftCardValid && !redeemed}>
                  {
                    Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': egiftCardBalance,
                      }, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={isEgiftCardValid && redeemed}>
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
            <div id="api-error">{apiErrorMessage}</div>
            <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
              <ConditionalView condition={setChecked === true}>
                <ConditionalView conditional={openModal}>
                  <UpdateEgiftCardAmount
                    closeModal={this.closeModal}
                    open={openModal}
                    cardBalance={egiftCardBalance}
                    remainingAmount={remainingAmount}
                    amount={cart.cart.totals.base_grand_total}
                    updateAmount={this.egiftCardhelper.handleAmountUpdate}
                    redeemAmount={this.getRedeemAmount}
                    handleExceedingAmount={this.handleExceedingAmount}
                    cart={cart.cart}
                    egiftCardNumber={linkedEgiftCardNumber}
                  />
                </ConditionalView>
                <div className="spc-payment-method-desc">
                  <div className="desc-content">
                    <ConditionalView condition={!isEgiftCardValid}>
                      {
                        Drupal.t('Card is expired please use another payment method to complete purchase', {}, { context: 'egift' })
                      }
                    </ConditionalView>
                    {/* eslint-disable-next-line max-len */}
                    <ConditionalView condition={exceedingAmount > 0 && isEgiftCardValid}>
                      {
                        Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': exceedingAmount,
                          }, { context: 'egift' })
                      }
                    </ConditionalView>
                    {/* eslint-disable-next-line max-len */}
                    <ConditionalView condition={setChecked === true && egiftCardBalance === 0 && isEgiftCardValid}>
                      {
                        Drupal.t('Linked card has 0 balance please use another payment method to complete purchase', {}, { context: 'egift' })
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

export default PaymentMethodLinkedEgift;
