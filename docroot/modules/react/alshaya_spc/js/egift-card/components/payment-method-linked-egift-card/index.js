import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import { showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { callEgiftApi, performRedemption, updatePriceSummaryBlock } from '../../../utilities/egift_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

class PaymentMethodLinkedEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      // OpenModal.
      openModal: false,
      // eGift balance Payable to pay using other payment methods.
      eGiftbalancePayable: 0,
      // Remaining Balance of egift card.
      egiftCardRemainingBalance: 0,
      // check if checkbox is checked.
      setChecked: false,
      // check if egift card is expired.
      isEgiftCardExpired: false,
      // check if user performed redemption already.
      isEgiftCardredeemed: false,
      // Amount to be passed to updatedEgiftCardAmount.
      modalInputAmount: 0,
      // Egift Card Balance.
      egiftCardActualBalance: 0,
      // Check if user has linked egift card.
      egiftLinkedCardNumber: null,
      // Api render wait time.
      renderWait: true,
      // Api render wait time.
      apiErrorMessage: '',
    };
  }

  componentDidMount() {
    const { cart } = this.props;
    // @todo if users tries topup for the same card.
    // Invoke magento API to get the user card number.
    const response = callEgiftApi('eGiftHpsCustomerData', 'GET', {});
    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.card_number !== null && result.data.response_type !== false) {
            const currentBalance = result.data.current_balance;
            // Current Time stamp to check for expiry.
            const currentTime = Math.floor(Date.now() / 1000);
            // IF expired or 0 balance card then no need to proceed further show message.
            if (currentTime > result.data.expiry_date_timestamp || currentBalance === 0) {
              this.setState({
                egiftCardActualBalance: currentBalance,
                isEgiftCardExpired: (currentTime > result.data.expiry_date_timestamp),
                renderWait: false,
              });
              return;
            }
            if (currentBalance !== null && currentBalance > 0) {
              let isRedeemed = false;
              let remainingAmount = 0;
              let isChecked = false;
              // Check if user has already linked Egift card.
              if (cart.cart.totals.egiftRedeemedAmount > 0 && cart.cart.totals.egiftRedemptionType === 'linked') {
                // Set checked if already performed linking of egift card.
                isChecked = true;
                isRedeemed = true;
                //
                if (currentBalance >= cart.cart.totals.base_grand_total) {
                  // Check if current balance is greater than cart total.
                  // then show remaining balance of the Card.
                  remainingAmount = currentBalance - cart.cart.totals.egiftRedeemedAmount;
                }
              }
              // balancePayable amount to show in order summary.
              const balancePayable = (hasValue(cart.cart.totals.balancePayable))
                ? cart.cart.totals.balancePayable
                : 0;
              this.setState({
                isEgiftCardredeemed: isRedeemed,
                setChecked: isChecked,
                eGiftbalancePayable: balancePayable,
                egiftCardRemainingBalance: remainingAmount,
                egiftCardActualBalance: currentBalance,
                isEgiftCardExpired: (currentTime > result.data.expiry_date_timestamp),
                modalInputAmount: cart.cart.totals.base_grand_total,
                egiftLinkedCardNumber: result.data.card_number,
                apiErrorMessage: '',
                renderWait: true,
              });
            }
          } else {
          // Handle error response.
            logger.error('Error while calling the egift HPS Search');
            this.setState({
              apiErrorMessage: result.data.response_message,
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

  // perform on checking / unchecking the checkbox
  handleOnClick = (e) => {
    const { egiftLinkedCardNumber, egiftCardActualBalance } = this.state;
    const { cart } = this.props;
    // On checking the checkbox this will be executed.
    if (e.target.checked) {
      showFullScreenLoader();
      // Perform redemption by calling the redemption API.
      const redemptionResponse = performRedemption(
        cart.cart.cart_id_int,
        cart.cart.totals.base_grand_total,
        egiftLinkedCardNumber,
        'linked',
      );
      if (redemptionResponse instanceof Promise) {
        redemptionResponse.then((res) => {
          if (res.status === 200) {
            if (res.data.card_number !== null && res.data.response_type !== false) {
              // Perform calculations and set state.
              this.performCalculations(
                res.data.balance_payable,
                res.data.redeemed_amount,
                egiftCardActualBalance,
              );
              // Trigger event to update price summary block.
              updatePriceSummaryBlock();
            } else {
              logger.error('Error while calling the eGiftRedemption. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': 'set_points',
                '@cardNumber': egiftLinkedCardNumber,
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
      // On unchecking the checkbox this will be executed to remve redemption.
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
        let errorMsg = '';
        response.then((result) => {
          if (result.status === 200) {
            if (result.data.response_type !== false) {
              // Trigger event to update price summary block.
              updatePriceSummaryBlock();
            } else {
              logger.error('Error while calling the cancel eGiftRedemption. Action: @action Response: @response', {
                '@action': postData.redeem_points.action,
                '@response': result.data.response_message,
              });
              errorMsg = result.data.response_message;
            }
            this.setState({
              isEgiftCardredeemed: false,
              egiftCardRemainingBalance: 0,
              eGiftbalancePayable: 0,
              apiErrorMessage: errorMsg,
            });
          }
        });
      }
    }
    // Reset the state to move back to initial redeem stage.
    this.setState({
      setChecked: e.target.checked,
    });
  };

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    const { egiftLinkedCardNumber, egiftCardActualBalance } = this.state;
    showFullScreenLoader();
    // Prepare the request object for redeem API.
    const { cart } = this.props;
    const response = performRedemption(cart.cart.cart_id_int, updateAmount, egiftLinkedCardNumber, 'linked');
    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.redeemed_amount !== null && result.data.response_type !== false) {
            const balancePayable = result.data.balance_payable;
            const redeemedAmount = result.data.redeemed_amount;
            // Perform calculations and set state.
            this.performCalculations(
              balancePayable,
              redeemedAmount,
              egiftCardActualBalance,
            );
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
  performCalculations = (balancePayable, redeemedAmount, egiftCardActualBalance) => {
    const remainingAmount = egiftCardActualBalance - redeemedAmount;
    let inputAmount = redeemedAmount;
    if (balancePayable === 0 && redeemedAmount > 0) {
      // If api response has balance payable.
      // then show user for using different payment method.
      inputAmount = redeemedAmount;
    } else if (balancePayable > 0 && redeemedAmount > 0) {
      // If api response has no balance payable.
      // then show remaining balance to the user.
      inputAmount = balancePayable + redeemedAmount;
    }
    this.setState({
      isEgiftCardredeemed: true,
      eGiftbalancePayable: balancePayable,
      egiftCardRemainingBalance: remainingAmount,
      modalInputAmount: inputAmount,
      openModal: false,
    });
  }

  render() {
    const {
      openModal,
      eGiftbalancePayable,
      egiftCardRemainingBalance,
      setChecked,
      isEgiftCardExpired,
      isEgiftCardredeemed,
      modalInputAmount,
      egiftCardActualBalance,
      egiftLinkedCardNumber,
      renderWait,
      apiErrorMessage,
    } = this.state;
    // Cart object need to be passed to UpdateGiftCardAmount.
    const { cart } = this.props;
    // Return if no linked card and if any api fails.
    if (!renderWait && egiftLinkedCardNumber == null) {
      return null;
    }
    // Disable checkbox when egiftcard balance is 0.
    const disabled = (egiftCardActualBalance === 0 || isEgiftCardExpired);
    return (
      <>
        <div className="payment-method fadeInUp payment-method-checkout_com_egift_linked_card">
          <div className="payment-method-top-panel">
            <div className="payment-method-label-wrapper">
              <ConditionalView condition={disabled}>
                <input type="checkbox" id="link-egift-card" disabled={disabled} />
              </ConditionalView>
              <ConditionalView condition={!disabled}>
                <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleOnClick} />
              </ConditionalView>
              <label className="checkbox-sim checkbox-label egift-link-card-label">
                <ConditionalView condition={isEgiftCardExpired}>
                  {
                    Drupal.t('Pay using egift card (Card is expired)', {}, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={egiftCardActualBalance === 0}>
                  {
                    Drupal.t('Pay using egift card (Available Balance 0)', {}, { context: 'egift' })
                  }
                </ConditionalView>
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
                    Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': egiftCardRemainingBalance,
                      }, { context: 'egift' })
                  }
                </ConditionalView>
              </label>
            </div>
            {/* FE to update Payment Icon SVG any where here. */}
            <ConditionalView condition={apiErrorMessage !== ''}>
              <div id="api-error">{apiErrorMessage}</div>
            </ConditionalView>
            <ConditionalView condition={!isEgiftCardExpired}>
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
                    <ConditionalView condition={eGiftbalancePayable > 0}>
                      {
                        Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': eGiftbalancePayable,
                          }, { context: 'egift' })
                      }
                    </ConditionalView>
                  </div>
                </div>
                <ConditionalView condition={cart.cart.totals.egiftRedemptionType === 'linked'}>
                  <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
                </ConditionalView>
              </div>
            </ConditionalView>
          </div>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedEgiftCard;
