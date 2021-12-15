import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import { showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { callEgiftApi, performRedemption, updatePriceSummaryBlock } from '../../../utilities/egift_util';

class PaymentMethodLinkedEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      // OpenModal.
      openModal: false,
      // Pending amount to pay using other payment methods.
      egiftCardPendingAmount: 0,
      // Remaining Balance of egift card.
      egiftCardRemainingBalance: 0,
      // check if checkbox is checked.
      setChecked: false,
      // check if egift card is expired.
      isEgiftCardNotExpired: true,
      // check if user performed redemption already.
      isEgiftCardredeemed: false,
      // Amount to be passed to updatedEgiftCardAmount.
      modalInputAmount: 0,
      // Egift Card Balance.
      egiftCardActualBalance: '',
      // Check if user has linked egift card.
      EgiftLinkedCardNumber: '',
      // Api render wait time.
      renderWait: true,
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
          // set render wait to false incase of failure.
          let wait = true;
          // set errormessage from api response.
          let errorMessage = '';
          if (result.data.card_number !== null && result.data.response_type !== false) {
            // Call balance api in case of search api success.
            const balanceData = {
              accountInfo: {
                cardNumber: result.data.card_number,
                email: drupalSettings.userDetails.userEmailID,
              },
            };
            const balanceResponse = callEgiftApi('eGiftGetBalance', 'POST', balanceData);
            if (balanceResponse instanceof Promise) {
              balanceResponse.then((balanceResult) => {
                if (balanceResult.status === 200) {
                  const currentBalance = balanceResult.data.current_balance;
                  let isRedeemed = false;
                  // Dont redeem if card balance is 0.
                  if (currentBalance === 0) {
                    isRedeemed = false;
                  }
                  const responseType = balanceResult.data.response_type;
                  let pendingAmount = 0;
                  let remainingAmount = 0;
                  let isChecked = false;
                  const currentTime = Math.floor(Date.now() / 1000);
                  const redeemedAmount = cart.cart.totals.egiftRedeemedAmount;
                  const cartTotal = cart.cart.totals.base_grand_total;
                  const redemptionType = cart.cart.totals.egiftRedemptionType;
                  if (currentBalance !== null && responseType !== false) {
                    if (typeof redeemedAmount !== 'undefined' && typeof redemptionType !== 'undefined') {
                      // Check if user has already performed redemption on page load.
                      if (redeemedAmount > 0 && redemptionType === 'linked') {
                        // Check if current balance is less than cart total.
                        // then show pending amount to be paid using another payment method.
                        if (currentBalance < cartTotal) {
                          pendingAmount = cartTotal - currentBalance;
                        } else if (currentBalance >= cartTotal) {
                          // Check if current balance is greater than cart total.
                          // then show remaining balance.
                          remainingAmount = currentBalance - redeemedAmount;
                        }
                        // Set checked if already performed redemption o page load.
                        isChecked = true;
                        isRedeemed = true;
                      }
                    }
                  } else {
                  // Handle error response.
                    wait = false;
                    errorMessage = balanceResult.data.response_message;
                    logger.error('Error while calling the eGiftGetBalance. CardNumber: @cardNumber . Response: @response', {
                      '@cardNumber': balanceData.accountInfo.cardNumber,
                      '@response': errorMessage,
                    });
                  }
                  this.setState({
                    isEgiftCardredeemed: isRedeemed,
                    setChecked: isChecked,
                    egiftCardPendingAmount: pendingAmount,
                    egiftCardRemainingBalance: remainingAmount,
                    egiftCardActualBalance: balanceResult.data.current_balance,
                    isEgiftCardNotExpired: (currentTime < balanceResult.data.expiry_date_timestamp),
                    modalInputAmount: cartTotal,
                    EgiftLinkedCardNumber: balanceResult.data.card_number,
                    apiErrorMessage: errorMessage,
                    renderWait: wait,
                  });
                }
              });
            }
          } else {
          // Handle error response.
            errorMessage = result.data.response_message;
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId . Response: @response', {
              '@emailId': params.email,
              '@response': errorMessage,
            });
            this.setState({
              apiErrorMessage: errorMessage,
              renderWait: false,
            });
          }
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
    const { EgiftLinkedCardNumber, egiftCardActualBalance } = this.state;
    const { cart } = this.props;
    let errorMessage = '';
    if (e.target.checked === true) {
      showFullScreenLoader();
      // Perform redemption by calling the redemption API.
      const redemptionResponse = performRedemption(cart.cart.cart_id_int, cart.cart.totals.base_grand_total, EgiftLinkedCardNumber, 'linked');
      if (redemptionResponse instanceof Promise) {
        redemptionResponse.then((res) => {
          if (res.status === 200) {
            if (res.data.card_number !== null && res.data.response_type !== false) {
              const balancePayable = res.data.balance_payable;
              const redeemedAmount = res.data.redeemed_amount;
              // Perform calculations and set state.
              this.performCalculations(balancePayable, redeemedAmount, egiftCardActualBalance);
              // Trigger event to update price summary block.
              updatePriceSummaryBlock();
            } else {
              logger.error('Error while calling the eGiftRedemption. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': 'set_points',
                '@cardNumber': EgiftLinkedCardNumber,
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
          if (result.status === 200) {
            if (result.data.response_type !== false) {
              // Trigger event to update price summary block.
              updatePriceSummaryBlock();
            } else {
              errorMessage = result.data.response_message;
              logger.error('Error while calling the cancel eGiftRedemption. Action: @action Response: @response', {
                '@action': postData.redeem_points.action,
                '@response': errorMessage,
              });
            }
            this.setState({
              isEgiftCardredeemed: false,
              egiftCardRemainingBalance: 0,
              apiErrorMessage: errorMessage,
            });
          }
        });
      }
    }
  };

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    const { EgiftLinkedCardNumber, egiftCardActualBalance } = this.state;
    showFullScreenLoader();
    // Prepare the request object for redeem API.
    const { cart } = this.props;
    const response = performRedemption(cart.cart.cart_id_int, updateAmount, EgiftLinkedCardNumber, 'linked');
    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.redeemed_amount !== null && result.data.response_type !== false) {
            const balancePayable = result.data.balance_payable;
            const redeemedAmount = result.data.redeemed_amount;
            // Perform calculations and set state.
            this.performCalculations(balancePayable, redeemedAmount, egiftCardActualBalance);
            // Trigger event to update price summary block.
            updatePriceSummaryBlock();
          }
          return true;
        }
        return false;
      });
    }
    return true;
  }

  // Perform Calulations and set state.
  performCalculations = (balancePayable, redeemedAmount, egiftCardBalance) => {
    let pendingAmount = 0;
    const remainingAmount = egiftCardBalance - redeemedAmount;
    let inputAmount = redeemedAmount;
    if (balancePayable === 0 && redeemedAmount > 0) {
      // If api response has balance payable.
      // then show user for using different payment method.
      inputAmount = redeemedAmount;
    } else if (balancePayable > 0 && redeemedAmount > 0) {
      // If api response has no balance payable.
      // then show remaining balance to the user.
      pendingAmount = balancePayable;
      inputAmount = balancePayable + redeemedAmount;
    }
    this.setState({
      isEgiftCardredeemed: true,
      egiftCardPendingAmount: pendingAmount,
      egiftCardRemainingBalance: remainingAmount,
      modalInputAmount: inputAmount,
      openModal: false,
    });
  }

  render() {
    const {
      openModal,
      egiftCardPendingAmount,
      egiftCardRemainingBalance,
      setChecked,
      isEgiftCardNotExpired,
      isEgiftCardredeemed,
      modalInputAmount,
      egiftCardActualBalance,
      EgiftLinkedCardNumber,
      renderWait,
      apiErrorMessage,
    } = this.state;
    // Cart object need to be passed to UpdateGiftCardAmount.
    const { cart } = this.props;
    // Return if no linked card and if any api fails.
    if (!renderWait && EgiftLinkedCardNumber == null) {
      return null;
    }
    // Disable checkbox when egiftcard balance is 0.
    const disabled = (egiftCardActualBalance === 0 || !isEgiftCardNotExpired);
    return (
      <>
        <div className="payment-method fadeInUp payment-method-checkout_com_egift_linked_card">
          <div className="payment-method-top-panel">
            <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleOnClick} disabled={disabled} />
            <div className="payment-method-label-wrapper">
              <label className="checkbox-sim checkbox-label egift-link-card-label">
                <ConditionalView condition={!isEgiftCardNotExpired}>
                  {
                    Drupal.t('Card is expired please use another payment method to complete purchase', {}, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={egiftCardActualBalance === 0}>
                  {
                    Drupal.t('Linked card has 0 balance please use another payment method to complete purchase', {}, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={isEgiftCardNotExpired}>
                  <ConditionalView condition={!isEgiftCardredeemed}>
                    {
                      Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                        {
                          '@currencyCode': getCurrencyCode(), '@amount': egiftCardActualBalance,
                        }, { context: 'egift' })
                    }
                  </ConditionalView>
                  <ConditionalView condition={isEgiftCardredeemed}>
                    {
                      Drupal.t('Pay using egift card (Remaining Balance: @currencyCode @amount)',
                        {
                          '@currencyCode': getCurrencyCode(), '@amount': egiftCardRemainingBalance,
                        }, { context: 'egift' })
                    }
                  </ConditionalView>
                </ConditionalView>
              </label>
            </div>
            {/* FE to update Payment Icon SVG any where here. */}
            <ConditionalView condition={apiErrorMessage !== ''}>
              <div id="api-error">{apiErrorMessage}</div>
            </ConditionalView>
            <ConditionalView condition={isEgiftCardNotExpired}>
              <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
                <ConditionalView conditional={openModal}>
                  <UpdateEgiftCardAmount
                    closeModal={this.closeModal}
                    open={openModal}
                    amount={modalInputAmount}
                    remainingAmount={egiftCardRemainingBalance}
                    updateAmount={this.handleAmountUpdate}
                    cart={cart}
                  />
                </ConditionalView>
                <div className="spc-payment-method-desc">
                  <div className="desc-content">
                    <ConditionalView condition={egiftCardPendingAmount > 0}>
                      {
                        Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': egiftCardPendingAmount,
                          }, { context: 'egift' })
                      }
                    </ConditionalView>
                  </div>
                </div>
                <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
              </div>
            </ConditionalView>
          </div>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedEgiftCard;
