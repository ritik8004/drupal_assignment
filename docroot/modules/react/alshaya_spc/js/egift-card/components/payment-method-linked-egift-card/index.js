import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import ValidEgiftCard from '../ValidEgiftCard';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { callEgiftApi, performRedemption } from '../../../utilities/egift_util';

class PaymentMethodLinkedEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.egiftCardhelper = new ValidEgiftCard();
    this.state = {
      // OpenModal.
      openModal: false,
      // Pending amount to pay using other payment methods.
      pendingAmount: 0,
      // Remaining Balance.
      remainingAmount: 0,
      // check if checkbox is checked.
      setChecked: false,
      // check if egift card is valid.
      isEgiftCardValid: false,
      // check if user performed redemption already.
      redeemedFully: false,
      // Amount to be passed to updatedEgiftCardAmount component after calculations.
      modalInputAmount: 0,
      // Actual Card Balance.
      egiftCardBalance: 0,
      // Check if user has linked egift card.
      linkedEgiftCard: false,
      // linked egift card number.
      linkedEgiftCardNumber: '',
      // Api render wait time.
      renderWait: false,
      // Api failure error messages.
      apiErrorMessage: '',
    };
  }

  componentDidMount() {
    const { cart } = this.props;
    const params = { email: drupalSettings.userDetails.userEmailID };
    // Invoke magento API to get the user card number.
    const response = callEgiftApi('eGiftHpsSearch', 'GET', {}, params);
    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.card_number !== null && result.data.response_type !== false) {
            this.setState({
              linkedEgiftCard: true,
              linkedEgiftCardNumber: result.data.card_number,
              renderWait: true,
            });
          }
          // Handle error response.
          if (result.data.account_id === null && result.data.response_type === false) {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId . Response: @response', {
              '@emailId': params.email,
              '@response': result.data.response_message,
            });
            this.setState({
              apiErrorMessage: result.data.response_message,
            });
          }
        }
      });
    }
    const { linkedEgiftCardNumber } = this.state;
    // Invoke magento API to check if card has balance.
    const balanceData = {
      accountInfo: {
        cardNumber: linkedEgiftCardNumber,
        email: drupalSettings.userDetails.userEmailID,
      },
    };
    const balanceResponse = callEgiftApi('eGiftGetBalance', 'POST', balanceData);
    if (balanceResponse instanceof Promise) {
      balanceResponse.then((result) => {
        if (result.status === 200) {
          if (result.data.current_balance !== null && result.data.response_type !== false) {
            const currentBalance = result.data.current_balance;
            const currentTime = Math.floor(Date.now() / 1000);
            this.setState({
              egiftCardBalance: currentBalance,
              redeemedFully: false,
              // Check if the linked card is valid or not.
              isEgiftCardValid: (currentTime < result.data.expiry_date_timestamp),
            });
            const redeemedAmount = cart.totals.extension_attributes.hps_redeemed_amount;
            const cartTotal = cart.cart.totals.base_grand_total;
            const redemptionType = cart.totals.extension_attributes.hps_redeemed_amount;
            // Check if user has already performed redemption on page load.
            if (redeemedAmount > 0 && redemptionType === 'linked') {
              if (currentBalance < cartTotal) {
                // Check if current balance is less than cart total.
                // then show pending amount to be paid using another payment method.
                this.setState({
                  pendingAmount: cartTotal - currentBalance,
                });
              } else if (currentBalance >= cartTotal) {
                // Check if current balance is greater than cart total then show remaining balance.
                this.setState({
                  remainingAmount: currentBalance - redeemedAmount,
                });
              }
              this.setState({
                redeemedFully: true,
                setChecked: true,
              });
            }
            // Dont redeem in case if balance is 0.
            if (currentBalance === 0) {
              this.setState({
                redeemedFully: false,
              });
            }
          }
        }
        // Handle error response.
        if (result.data.account_id === null && result.data.response_type === false) {
          logger.error('Error while calling the eGiftGetBalance. CardNumber: @cardNumber . Response: @response', {
            '@cardNumber': balanceData.accountInfo.cardNumber,
            '@response': result.data.response_message,
          });
          this.setState({
            apiErrorMessage: result.data.response_message,
          });
        }
      });
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

  // Handle Onclick.
  handleOnClick = (e) => {
    // Reset the state to move back to initial redeem stage.
    this.setState({
      setChecked: e.target.checked,
    });
    const { linkedEgiftCardNumber, egiftCardBalance } = this.state;
    const { cart } = this.props;
    if (e.target.checked === true) {
      // Calculate the remaining amount based on cart value.
      // @todo For remaining balance we will use some key from cart only and
      // no calculation on FE;
      const postData = {
        redeem_points: {
          action: 'set_points',
          quote_id: cart.cart.cart_id_int,
          amount: cart.cart.totals.base_grand_total,
          cardNumber: linkedEgiftCardNumber,
          payment_method: 'hps_payment',
          email: drupalSettings.userDetails.userEmailID,
          card_type: 'linked',
        },
      };
      showFullScreenLoader();
      // Perform redemption by calling the redemption API.
      const redemptionResponse = callEgiftApi('eGiftRedemption', 'POST', postData);
      if (redemptionResponse instanceof Promise) {
        redemptionResponse.then((res) => {
          // Remove the loader as response is available.
          removeFullScreenLoader();
          if (res.status === 200) {
            if (res.data.card_number !== null && res.data.response_type !== false) {
              if (res.data.balance_payable === 0 && res.data.redeemed_amount > 0) {
                // If api response has balance payable.
                // then show user for using different payment method.
                this.setState({
                  pendingAmount: 0,
                  remainingAmount: egiftCardBalance - res.data.redeemed_amount,
                  modalInputAmount: res.data.redeemed_amount,
                });
              } else if (res.data.balance_payable > 0 && res.data.redeemed_amount > 0) {
                // If api response has no balance payable.
                // then show remaining balance to the user.
                this.setState({
                  pendingAmount: res.data.balance_payable,
                  remainingAmount: egiftCardBalance - res.data.redeemed_amount,
                  modalInputAmount: res.data.balance_payable + res.data.redeemed_amount,
                });
              }
              this.setState({
                redeemedFully: true,
              });
              // Refresh the cart to show the price summary changes.
              window.commerceBackend.refreshCart({});
            }
            if (res.status === 200 && res.data.response_type === false) {
              logger.error('Error while calling the eGiftRedemption. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': postData.redeem_points.action,
                '@cardNumber': postData.redeem_points.cardNumber,
                '@response': res.data.response_message,
              });
              this.setState({
                apiErrorMessage: res.data.response_message,
              });
            }
          }
        });
      }
    } else {
      const postData = {
        redeem_points: {
          action: 'remove_points',
          quote_id: cart.cart.cart_id_int,
        },
      };
      showFullScreenLoader();
      // Invoke the remove redemption API.
      const response = callEgiftApi('eGiftRedemption', 'POST', postData);
      if (response instanceof Promise) {
        // Handle the error and success message after the egift card is removed
        // from the cart.
        response.then((result) => {
          removeFullScreenLoader();
          if (result.status === 200 && result.data.response_type !== false) {
            this.setState({
              redeemedFully: false,
              remainingAmount: 0,
            });
            window.commerceBackend.refreshCart({});
          }
          if (result.data.response_type === false) {
            logger.error('Error while calling the cancel eGiftRedemption. Action: @action Response: @response', {
              '@action': postData.redeem_points.action,
              '@response': result.data.response_message,
            });
            this.setState({
              apiErrorMessage: result.data.response_message,
            });
          }
        });
      }
    }
  };

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    const { linkedEgiftCardNumber, egiftCardBalance } = this.state;
    showFullScreenLoader();
    // Prepare the request object for redeem API.
    const { cart } = this.props;
    const response = performRedemption(cart.cart.cart_id_int, updateAmount, linkedEgiftCardNumber, 'linked');
    if (response instanceof Promise) {
      response.then((result) => {
        // Remove loader once result is available.
        removeFullScreenLoader();
        if (result.status === 200) {
          if (result.data.redeemed_amount !== null && result.data.response_type !== false) {
            if (result.data.balance_payable === 0 && result.data.redeemed_amount > 0) {
              // If api response has balance payable.
              // then show user for using different payment method.
              this.setState({
                pendingAmount: 0,
                remainingAmount: egiftCardBalance - result.data.redeemed_amount,
                modalInputAmount: result.data.redeemed_amount,
                openModal: false,
              });
            } else if (result.data.balance_payable > 0 && result.data.redeemed_amount > 0) {
              // If api response has no balance payable.
              // then show remaining balance to the user.
              this.setState({
                pendingAmount: result.data.balance_payable,
                remainingAmount: egiftCardBalance - result.data.redeemed_amount,
                modalInputAmount: result.data.balance_payable + result.data.redeemed_amount,
                openModal: false,
              });
            }
          }
          return true;
        }
        return false;
      });
    }
    return true;
  }

  render() {
    const {
      openModal,
      remainingAmount,
      apiErrorMessage,
      setChecked,
      egiftCardBalance,
      pendingAmount,
      redeemedFully,
      renderWait,
      linkedEgiftCard,
      isEgiftCardValid,
      modalInputAmount,
    } = this.state;
    const { cart } = this.props;
    if (!renderWait && !linkedEgiftCard) {
      return null;
    }
    const disabled = (egiftCardBalance === 0);

    return (
      <>
        <div className="payment-method fadeInUp payment-method-checkout_com_egift_linked_card">
          <div className="payment-method-top-panel">
            <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleOnClick} disabled={disabled} />
            <div className="payment-method-label-wrapper">
              <label className="checkbox-sim checkbox-label egift-link-card-label">
                <ConditionalView condition={isEgiftCardValid && !redeemedFully}>
                  {
                    Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': egiftCardBalance,
                      }, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={isEgiftCardValid && redeemedFully
                  && egiftCardBalance !== 0 && remainingAmount > 0}
                >
                  {
                    Drupal.t('Pay using egift card (Remaining Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': remainingAmount,
                      }, { context: 'egift' })
                  }
                </ConditionalView>
              </label>
            </div>
            {/* FE to update Payment Icon SVG any where here. */}
            <div id="api-error">{apiErrorMessage}</div>
            <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
              <ConditionalView conditional={openModal}>
                <UpdateEgiftCardAmount
                  closeModal={this.closeModal}
                  open={openModal}
                  amount={modalInputAmount}
                  remainingAmount={remainingAmount}
                  updateAmount={this.handleAmountUpdate}
                  cart={cart}
                />
              </ConditionalView>
              <div className="spc-payment-method-desc">
                <div className="desc-content">
                  <ConditionalView condition={!isEgiftCardValid}>
                    {
                      Drupal.t('Card is expired please use another payment method to complete purchase', {}, { context: 'egift' })
                    }
                  </ConditionalView>
                  <ConditionalView condition={egiftCardBalance === 0 && isEgiftCardValid}>
                    {
                      Drupal.t('Linked card has 0 balance please use another payment method to complete purchase', {}, { context: 'egift' })
                    }
                  </ConditionalView>
                  <ConditionalView condition={setChecked === true
                    && pendingAmount > 0 && isEgiftCardValid}
                  >
                    {
                      Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                        {
                          '@currencyCode': getCurrencyCode(), '@amount': pendingAmount,
                        }, { context: 'egift' })
                    }
                  </ConditionalView>
                </div>
              </div>
              <ConditionalView condition={setChecked === true && egiftCardBalance > 0}>
                <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
              </ConditionalView>
            </div>
          </div>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedEgiftCard;
