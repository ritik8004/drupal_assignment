import React from 'react';
import getCurrencyCode from '../../../../js/utilities/util';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { callEgiftApi, egiftCardHeader, performRedemption } from '../../utilities/egift_util';
import UpdateEgiftCardAmount from './UpdateEgiftCardAmount';
import logger from '../../../../js/utilities/logger';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../js/utilities/showRemoveFullScreenLoader';
import dispatchCustomEvent from '../../../../js/utilities/events';

export default class ValidEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      amount: 0,
      remainingAmount: 0,
      redeemedFully: false,
      pendingAmount: 0,
    };
  }

  componentDidMount = () => {
    // Calculate the remaining amount and egift card amount to be used.
    const {
      egiftCardNumber,
      egiftEmail,
      cart,
    } = this.props;

    let postData = {
      accountInfo: {
        cardNumber: egiftCardNumber,
        email: egiftEmail,
      },
    };
    showFullScreenLoader();
    // Call balance API to get the current balance of egift card number.
    const response = callEgiftApi('eGiftGetBalance', 'POST', postData);
    if (response instanceof Promise) {
      response.then((result) => {
        // Remove the loader as response is available.
        removeFullScreenLoader();
        if (result.error === undefined && result.data !== undefined && result.status === 200) {
          // Calculate the remaining amount based on cart value.
          // @todo For remaining balance we will use some key from cart only and
          // no calculation on FE;
          const currentBalance = result.data.current_balance;
          const cartTotal = cart.totals.base_grand_total;
          if (currentBalance < cartTotal) {
            // Set the flag to display message to pay pending amount using other
            // payment method.
            this.setState({
              redeemedFully: true,
              pendingAmount: cartTotal - currentBalance,
            });
          } else if (currentBalance >= cartTotal) {
            this.setState({
              amount: cartTotal,
              remainingAmount: currentBalance - cartTotal,
            });
          }

          const { amount: updateAmount } = this.state;
          if (updateAmount > 0) {
            // update the post data object.
            // For Redeemption card_type to be always 'guest' for both guest and logged-in users.
            postData = {
              redeem_points: {
                action: 'set_points',
                quote_id: cart.cart_id_int,
                amount: updateAmount,
                card_number: egiftCardNumber,
                payment_method: 'hps_payment',
                card_type: 'guest', // card type to be guest or linked.
              },
            };
            showFullScreenLoader();
            // Perform redemption by calling the redemption API.
            const redemptionResponse = callEgiftApi('eGiftRedemption', 'POST', postData);
            if (redemptionResponse instanceof Promise) {
              redemptionResponse.then((res) => {
                if (res.error === undefined && res.data !== undefined && res.status === 200) {
                  const cartData = window.commerceBackend.getCart(true);
                  if (cartData instanceof Promise) {
                    cartData.then((data) => {
                      if (data.data !== undefined && data.data.error === undefined) {
                        if (data.status === 200) {
                          // Update Egift card line item.
                          dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
                          // Remove the loader as response is available.
                          removeFullScreenLoader();
                        }
                      }
                    });
                  }
                }
              });
            }
          }
        }
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

  // Handle remove card.
  handleRemoveCard = () => {
    const { removeCard } = this.props;
    const status = removeCard();
    if (status) {
      document.getElementById('egift_remove_card_error').innerHTML = Drupal.t('There was some error while removing the gift card. Please try again', {}, { context: 'egift' });
    }

    return !status;
  }

  // Update the user account with egift card.
  handleCardLink = () => {
    // Extract the current user email.
    const params = { email: drupalSettings.userDetails.userEmailID };
    if (params.email) {
      // @todo Call user acount link API.
    }

    return false;
  };

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    // Prepare the request object for redeem API.
    const { quoteId, egiftCardNumber } = this.props;
    showFullScreenLoader();
    // Invoke the redemption API to update the redeem amount.
    const response = performRedemption(quoteId, updateAmount, egiftCardNumber, 'guest');
    if (response instanceof Promise) {
      response.then((result) => {
        // Remove loader once result is available.
        removeFullScreenLoader();
        if (result.status === 200) {
          if (result.data.redeemed_amount !== null && result.data.response_type !== false) {
            this.setState({
              amount: updateAmount,
              open: false,
            });
            const cartData = window.commerceBackend.getCart(true);
            if (cartData instanceof Promise) {
              cartData.then((data) => {
                if (data.data !== undefined && data.data.error === undefined) {
                  if (data.status === 200) {
                    // Update Egift card line item.
                    dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
                    removeFullScreenLoader();
                  }
                }
              });
            }
            return true;
          }
        }
        return false;
      });
    }
    return true;
  }

  // Whether user is applicable to link card in the account.
  isCardLinkingApplicable = () => {
    // Return if user is not authenticated.
    if (isUserAuthenticated()) {
      return false;
    }

    const params = { email: drupalSettings.userDetails.userEmailID };
    if (params.email) {
      // Invoke magento API to check if any egift card is already associated
      // with the user account.
      const response = callEgiftApi('eGiftHpsSearch', 'GET', {}, params);
      if (response instanceof Promise) {
        response.then((result) => {
          if (result.data !== 'undefined'
            && result.error === 'undefined') {
            // return false if card number is already linked else true.
            return !result.data.card_number;
          }
          // Handle error response.
          if (result.error) {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId. Response: @response', {
              '@emailId': params.email,
              '@response': result.data,
            });
          }
          return false;
        });
      }
    }

    return false;
  }

  render = () => {
    const {
      open,
      amount,
      remainingAmount,
      redeemedFully,
      pendingAmount,
    } = this.state;

    const { cart } = this.props;
    const currencyCode = getCurrencyCode();

    return (
      <div className="egift-wrapper">
        {egiftCardHeader({
          egiftHeading: Drupal.t('Applied card amount - @currencyCode @amount', {
            '@currencyCode': currencyCode,
            '@amount': amount,
          }, { context: 'egift' }),
          egiftSubHeading: Drupal.t('Remaining Balance - @currencyCode @remainingAmount', {
            '@currencyCode': currencyCode,
            '@remainingAmount': remainingAmount,
          }, { context: 'egift' }),
        })}

        <ConditionalView conditional={open}>
          <UpdateEgiftCardAmount
            closeModal={this.closeModal}
            open={open}
            amount={amount}
            remainingAmount={remainingAmount}
            updateAmount={this.handleAmountUpdate}
            cart={cart}
          />
        </ConditionalView>
        <div className="remove-egift-card">
          <button type="button" onClick={this.handleRemoveCard}>{Drupal.t('Remove', {}, { context: 'egift' })}</button>
          <div id="egift_remove_card_error" className="error" />
        </div>
        <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
        <ConditionalView condition={redeemedFully}>
          <div className="full-redeemed">
            {Drupal.t('Pay @currencyCode @pendingAmount using another payment method to complete purchase', { '@currencyCode': currencyCode, '@pendingAmount': pendingAmount }, { context: 'egift' })}
          </div>
        </ConditionalView>
        <ConditionalView condition={this.isCardLinkingApplicable()}>
          <input type="checkbox" id="link-egift-card" onChange={this.handleCardLink} />
          <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
          <div id="egift_linkcard_error" className="error" />
        </ConditionalView>
      </div>
    );
  }
}
