import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { callEgiftApi, performRedemption, updatePriceSummaryBlock } from '../../../utilities/egift_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import LinkedEgiftSVG from '../../../svg-component/linked-egift-svg';

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
      // Api Error message.
      apiErrorMessage: '',
    };
  }

  componentDidMount() {
    const { cart } = this.props;

    // Invoke magento API to get the user card number
    const response = callEgiftApi('eGiftHpsCustomerData', 'GET', {});

    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.card_number !== null && result.data.response_type) {
            // While doing topup of same egift card which is linked to the logged in customer,
            // dont show linked card redemption section in checkout page.
            if (hasValue(cart.cart.items[0].topupCardNumber)
              && result.data.card_number === cart.cart.items[0].topupCardNumber) {
              return;
            }

            // Card Available Balance.
            const currentBalance = result.data.current_balance;
            // Current Time stamp to check for expiry.
            const currentTime = Math.floor(Date.now() / 1000);

            // IF 0 balance card then no need to proceed further.
            if (currentBalance === 0) {
              this.setState({
                egiftCardActualBalance: currentBalance,
                egiftLinkedCardNumber: result.data.card_number,
                renderWait: false,
              });
              return;
            }

            // IF Expired card then no need to proceed further.
            if (currentTime > result.data.expiry_date_timestamp) {
              this.setState({
                egiftCardActualBalance: currentBalance,
                isEgiftCardExpired: (currentTime > result.data.expiry_date_timestamp),
                egiftLinkedCardNumber: result.data.card_number,
                renderWait: false,
              });
              return;
            }

            // If card with Balance and not Expired.
            if (currentBalance !== null && currentBalance > 0) {
              let isRedeemed = false;
              let remainingAmount = 0;
              let isChecked = false;

              // Check if user has already linked Egift card.
              if (cart.cart.totals.egiftRedeemedAmount > 0 && cart.cart.totals.egiftRedemptionType === 'linked') {
                // Set checked if already performed linking of egift card.
                isChecked = true;
                isRedeemed = true;

                // Check if card available balance is greater than cart total.
                // then calculate the remaining balance of the Card.
                if (currentBalance >= cart.cart.totals.base_grand_total) {
                  remainingAmount = currentBalance - cart.cart.totals.egiftRedeemedAmount;
                }
              }
              // balancePayable amount to show in order summary.
              const balancePayable = (hasValue(cart.cart.totals.balancePayable))
                ? cart.cart.totals.balancePayable
                : 0;

              // Set state to show Pay using egift card option under Payment Methods.
              this.setState({
                isEgiftCardredeemed: isRedeemed,
                setChecked: isChecked,
                eGiftbalancePayable: balancePayable,
                egiftCardRemainingBalance: remainingAmount,
                egiftCardActualBalance: currentBalance,
                isEgiftCardExpired: (currentTime > result.data.expiry_date_timestamp),
                modalInputAmount: cart.cart.totals.base_grand_total,
                egiftLinkedCardNumber: result.data.card_number,
              });
            }
          } else {
            // If Empty Response form eGiftHpsCustomerData Api.
            logger.error('Empty Response @customerEmail. Message: @message', {
              '@customerEmail': drupalSettings.userDetails.userEmailID,
              '@message': result.data.response_message,
            });
          }
        } else {
          // If eGiftHpsCustomerData API is returning Error.
          logger.error('Error while calling the egift HPS Customer Data Api @customerEmail. Message: @message', {
            '@customerEmail': drupalSettings.userDetails.userEmailID,
            '@message': result.data.error_message,
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

  // perform on checking / unchecking the checkbox
  handleOnClick = (e) => {
    const { egiftLinkedCardNumber, egiftCardActualBalance } = this.state;
    const { cart } = this.props;
    // On checking the checkbox this will be executed.
    if (e.target.checked) {
      showFullScreenLoader();
      // Perform linking of Egift card by calling the redemption API.
      const redemptionResponse = performRedemption(
        cart.cart.cart_id_int,
        cart.cart.totals.base_grand_total,
        egiftLinkedCardNumber,
        'linked',
      );
      if (redemptionResponse instanceof Promise) {
        redemptionResponse.then((res) => {
          if (res.status === 200) {
            if (res.data.card_number !== null && res.data.response_type) {
              // Perform calculations and set state for linking card.
              this.performCalculations(
                res.data.balance_payable,
                res.data.redeemed_amount,
                egiftCardActualBalance,
                res.data.card_number,
              );
              // Trigger event to update price in order summary block.
              updatePriceSummaryBlock();
              // Set checkbox status to checked as redemption was successfull.
              this.setState({
                setChecked: true,
              });
            } else {
              logger.error('Empty Response in eGiftRedemption for linked card. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': 'set_points',
                '@cardNumber': egiftLinkedCardNumber,
                '@response': res.data.response_message,
              });
              this.setState({
                apiErrorMessage: res.data.response_message,
              });
              removeFullScreenLoader();
            }
          } else {
            logger.error('Error while calling the eGiftRedemption linked card. Action: @action CardNumber: @cardNumber Response: @response', {
              '@action': 'set_points',
              '@cardNumber': egiftLinkedCardNumber,
              '@response': res.data.error_message,
            });
            this.setState({
              apiErrorMessage: Drupal.t('Something went wrong, please try again later.', {}, { context: 'egift' }),
              renderWait: false,
              setChecked: false,
            });
            removeFullScreenLoader();
          }
        });
      }
    } else {
      // On unchecking the checkbox this will be executed to remove redemption.
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
        // Handle the error and success message after the egift card is unlinked.
        response.then((result) => {
          if (result.status === 200) {
            if (result.data.response_type) {
              // Trigger event to update price summary block.
              updatePriceSummaryBlock();

              this.setState({
                isEgiftCardredeemed: false,
                egiftCardRemainingBalance: 0,
                eGiftbalancePayable: 0,
                apiErrorMessage: '',
                setChecked: false,
              });
            } else {
              logger.error('Empty Response while calling the cancel eGiftRedemption. Action: @action, CardNumber: @cardNumber, Response: @response', {
                '@action': postData.redeem_points.action,
                '@cardNumber': egiftLinkedCardNumber,
                '@response': result.data.response_message,
              });
              removeFullScreenLoader();
            }
          } else {
            logger.error('Error while calling the cancel eGiftRedemption for unlinking egift card. Action: @action, CardNumber: @cardNumber, Response: @response', {
              '@action': postData.redeem_points.action,
              '@cardNumber': egiftLinkedCardNumber,
              '@response': result.data.error_message,
            });
            removeFullScreenLoader();
            this.setState({
              apiErrorMessage: Drupal.t('Something went wrong please try again later.', {}, { context: 'egift' }),
              renderWait: false,
            });
          }
        });
      }
    }
  };

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    const { egiftLinkedCardNumber, egiftCardActualBalance } = this.state;
    showFullScreenLoader();
    // Prepare the request object for redeem API.
    const { cart } = this.props;

    // Api call to update the redemption amount.
    const response = performRedemption(cart.cart.cart_id_int, updateAmount, egiftLinkedCardNumber, 'linked');
    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.redeemed_amount !== null && result.data.response_type) {
            const balancePayable = result.data.balance_payable;
            const redeemedAmount = result.data.redeemed_amount;
            // Perform calculations and set state.
            this.performCalculations(
              balancePayable,
              redeemedAmount,
              egiftCardActualBalance,
              result.data.card_number,
            );
            // Trigger event to update price in order summary block.
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
  performCalculations = (balancePayable, redeemedAmount, egiftCardActualBalance, cardNumber) => {
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
      egiftLinkedCardNumber: cardNumber,
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
    if (renderWait && egiftLinkedCardNumber == null) {
      return null;
    }
    // Disable checkbox when egiftcard balance is 0 or is expired.
    const disabled = (egiftCardActualBalance === 0 || isEgiftCardExpired);

    // If card redeemed subtract the redeemed amount from card balance and show.
    let cardBlanceAmount = egiftCardActualBalance;
    if (isEgiftCardredeemed && !disabled) {
      cardBlanceAmount = egiftCardRemainingBalance;
    }

    return (
      <>
        <div className="payment-method payment-method-checkout_com_egift_linked_card">
          <div className="payment-method-top-panel">

            <div className="payment-method-label-wrapper">
              <ConditionalView condition={disabled}>
                <input type="checkbox" id="link-egift-card" disabled={disabled} />
              </ConditionalView>

              <ConditionalView condition={!disabled}>
                <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleOnClick} />
              </ConditionalView>

              <label htmlFor="link-egift-card" className="checkbox-sim checkbox-label egift-link-card-label">
                <ConditionalView condition={isEgiftCardExpired}>
                  {
                    Drupal.t('Pay using egift card (Card is expired)', {}, { context: 'egift' })
                  }
                </ConditionalView>

                <ConditionalView condition={!isEgiftCardExpired}>
                  {
                    Drupal.t('Pay using egift card', { context: 'egift' })
                  }
                  <div className="spc-payment-method-desc">
                    <div className="desc-content">
                      {
                        Drupal.t('(Available Balance: @currencyCode @amount)',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': cardBlanceAmount,
                          }, { context: 'egift' })
                      }
                    </div>
                  </div>
                </ConditionalView>
              </label>
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

                  <ConditionalView condition={eGiftbalancePayable > 0}>
                    <div className="spc-payment-method-desc">
                      <div className="desc-content">
                        {
                          Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                            {
                              '@currencyCode': getCurrencyCode(), '@amount': eGiftbalancePayable,
                            }, { context: 'egift' })
                        }
                      </div>
                    </div>
                  </ConditionalView>


                  <ConditionalView condition={cart.cart.totals.egiftRedemptionType === 'linked'}>
                    <div className="edit-egift-payment-amount" onClick={this.openModal}>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</div>
                  </ConditionalView>

                </div>
              </ConditionalView>
            </div>
            <LinkedEgiftSVG />
            <ConditionalView condition={apiErrorMessage !== ''}>
              <div id="api-error">{apiErrorMessage}</div>
            </ConditionalView>
          </div>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedEgiftCard;
