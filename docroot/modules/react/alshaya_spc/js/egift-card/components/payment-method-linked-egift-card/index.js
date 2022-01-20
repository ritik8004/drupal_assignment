import React from 'react';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import {
  isEgiftRedemptionDone,
  isEgiftUnsupportedPaymentMethod,
  selfCardTopup,
  updatePriceSummaryBlock,
  updateRedeemAmount,
} from '../../../utilities/egift_util';
import { callEgiftApi, performRedemption } from '../../../../../js/utilities/egiftCardHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import LinkedEgiftSVG from '../../../svg-component/linked-egift-svg';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import PriceElement from '../../../utilities/special-price/PriceElement';
import Loading from '../../../../../js/utilities/loading';

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
            // If selfTopup no need to show linked card redemption section in checkout page.
            if (selfCardTopup(cart.cart, result.data.card_number)) {
              this.setState({
                renderWait: false,
              });
              return;
            }

            // Card Available Balance.
            const currentBalance = parseInt(result.data.current_balance, 10);
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
              const balancePayable = (hasValue(cart.cart.totals.totalBalancePayable))
                ? cart.cart.totals.totalBalancePayable
                : 0;

              // Set state to show Pay using egift card option under Payment Methods.
              this.setState({
                isEgiftCardredeemed: isRedeemed,
                setChecked: isChecked,
                eGiftbalancePayable: balancePayable,
                egiftCardRemainingBalance: remainingAmount,
                egiftCardActualBalance: currentBalance,
                isEgiftCardExpired: (currentTime > result.data.expiry_date_timestamp),
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
          this.setState({
            renderWait: false,
          });
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
    const { cart, refreshCart } = this.props;
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
              updatePriceSummaryBlock(refreshCart);
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
      let postData = {
        redemptionRequest: {
          mask_quote_id: cart.cart.cart_id,
        },
      };
      // Change payload if authenticated user.
      if (isUserAuthenticated()) {
        postData = {
          redemptionRequest: {
            quote_id: cart.cart.cart_id_int,
          },
        };
      }

      showFullScreenLoader();
      // Invoke the remove redemption API.
      const response = callEgiftApi('eGiftRemoveRedemption', 'POST', postData);

      if (response instanceof Promise) {
        // Handle the error and success message after the egift card is unlinked.
        response.then((result) => {
          if (result.status === 200) {
            if (result.data.response_type) {
              // Trigger event to update price summary block.
              updatePriceSummaryBlock(refreshCart);

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
  handleAmountUpdate = async (updateAmount) => {
    const { egiftCardActualBalance } = this.state;
    // Prepare the request object for redeem API.
    const { cart, refreshCart } = this.props;

    // Api call to update the redemption amount.
    const response = await updateRedeemAmount(updateAmount, cart.cart, refreshCart);
    if (!response.error) {
      const { redeemedAmount, totalBalancePayable, cardNumber } = response;
      // Perform calculations and set state.
      this.performCalculations(
        totalBalancePayable,
        redeemedAmount,
        egiftCardActualBalance,
        cardNumber,
      );
    }

    return response;
  }

  // Perform Calulations and set state.
  performCalculations = (balancePayable, redeemedAmount, egiftCardActualBalance, cardNumber) => {
    // Remaining amount is the available card balance after redemption.
    const remainingAmount = egiftCardActualBalance - redeemedAmount;
    this.setState({
      isEgiftCardredeemed: true,
      eGiftbalancePayable: balancePayable,
      egiftCardRemainingBalance: remainingAmount,
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
      egiftCardActualBalance,
      egiftLinkedCardNumber,
      renderWait,
      apiErrorMessage,
    } = this.state;
    // Cart object need to be passed to UpdateGiftCardAmount.
    const { cart } = this.props;
    // show loader till we get response from API.
    if (renderWait && egiftLinkedCardNumber == null) {
      return (
        <div className="payment-method payment-method-checkout_com_egift_linked_card" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }
    // Return if no linked card and if any api fails.
    if (!renderWait && egiftLinkedCardNumber == null) {
      return null;
    }

    // Disable linked card redemption if un supported payment method is selected.
    let UnsupportedPaymentMethod = false;
    if (hasValue(cart.cart.payment)
      && hasValue(cart.cart.payment.method)) {
      UnsupportedPaymentMethod = isEgiftUnsupportedPaymentMethod(cart.cart.payment.method);
    }

    // Disable link card checkbox when egiftcard balance is 0 or is expired,
    // if already redeemed or any unsupported payment method selected.
    const disabled = (egiftCardActualBalance === 0
      || isEgiftCardExpired
      || isEgiftRedemptionDone(cart.cart)
      || UnsupportedPaymentMethod
    );

    // Add `in-active` class if disabled property is true.
    const additionalClasses = disabled
      ? 'in-active'
      : 'active';

    // If card redeemed subtract the redeemed amount from card balance and show.
    let cardBlanceAmount = egiftCardActualBalance;
    if (isEgiftCardredeemed && !disabled) {
      cardBlanceAmount = egiftCardRemainingBalance;
    }

    return (
      <>
        <div className={`payment-method payment-method-checkout_com_egift_linked_card ${additionalClasses}`}>
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
                    Drupal.t('Pay using egift card', {}, { context: 'egift' })
                  }
                  <div className="spc-payment-method-desc">
                    <div className="desc-content">
                      (
                      {Drupal.t('Available Balance: ', {}, { context: 'egift' })}
                      <PriceElement amount={cardBlanceAmount} format="string" showZeroValue />
                      )
                    </div>
                  </div>
                </ConditionalView>
              </label>
            </div>
            <LinkedEgiftSVG />
          </div>
          <ConditionalView condition={!isEgiftCardExpired && cart.cart.totals.egiftRedemptionType === 'linked'}>
            <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
              <ConditionalView conditional={openModal}>
                <UpdateEgiftCardAmount
                  closeModal={this.closeModal}
                  open={openModal}
                  amount={cart.cart.totals.egiftRedeemedAmount}
                  remainingAmount={egiftCardRemainingBalance}
                  updateAmount={this.handleAmountUpdate}
                  cart={cart.cart}
                />
              </ConditionalView>

              <ConditionalView condition={eGiftbalancePayable > 0}>
                <div className="spc-payment-method-desc">
                  <div className="desc-content">
                    {Drupal.t('Pay ', {}, { context: 'egift' })}
                    <PriceElement amount={eGiftbalancePayable} format="string" showZeroValue />
                    {Drupal.t(' using another payment method to complete purchase', {}, { context: 'egift' })}
                  </div>
                </div>
              </ConditionalView>

              <div className="edit-egift-payment-amount" onClick={this.openModal}>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</div>

            </div>
          </ConditionalView>

          <ConditionalView condition={apiErrorMessage !== ''}>
            <div id="api-error" className="error linked-card-payment-error">{apiErrorMessage}</div>
          </ConditionalView>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedEgiftCard;
