import React from 'react';
import getCurrencyCode from '../../../../js/utilities/util';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import {
  callEgiftApi,
  egiftCardHeader,
  isEgiftRedemptionDone,
  isValidResponse,
  isValidResponseWithFalseResult,
  performRedemption,
} from '../../utilities/egift_util';
import UpdateEgiftCardAmount from './UpdateEgiftCardAmount';
import logger from '../../../../js/utilities/logger';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../js/utilities/showRemoveFullScreenLoader';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../js/utilities/strings';

export default class ValidEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      amount: 0,
      pendingAmount: 0,
      isLinkedCardApplicable: false,
      cardLinked: false,
    };
  }

  componentDidMount = () => {
    // Calculate the remaining amount and egift card amount to be used.
    const {
      cart,
      egiftCardNumber,
    } = this.props;

    // Proceed only if redemption is done.
    if (isEgiftRedemptionDone(cart)) {
      this.setState({
        amount: cart.totals.egiftRedeemedAmount,
        pendingAmount: cart.totals.balancePayable,
      });
    }

    // Validate if link card is applicable.
    if (isUserAuthenticated()
      && drupalSettings.userDetails.userEmailID) {
      const params = { email: drupalSettings.userDetails.userEmailID };
      // Invoke magento API to check if any egift card is already associated
      // with the user account.
      const response = callEgiftApi('eGiftHpsSearch', 'GET', {}, params, true);
      if (response instanceof Promise) {
        response.then((result) => {
          if (isValidResponse(result)) {
            // Set linked card applicable to true if card_number is not
            // available.
            if (hasValue(result.data.card_number)
              && !result.data.card_number) {
              this.setState({
                isLinkedCardApplicable: true,
              });
            }
            // Updated the card linked state based on the search response.
            if (hasValue(result.data.card_number)
              && result.data.card_number === egiftCardNumber) {
              this.setState({
                cardLinked: true,
              });
            }
          } else {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId. Response: @response', {
              '@emailId': params.email,
              '@response': result,
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

  // Handle remove card.
  handleRemoveCard = async () => {
    const { removeCard } = this.props;
    const result = await removeCard();
    // Display the error message based on the response.
    if (result.error) {
      document.getElementById('egift_remove_card_error').innerHTML = result.message;
    }
  }

  // Update the user account with egift card.
  handleCardLink = (e) => {
    // Extract the current user email.
    const email = drupalSettings.userDetails.userEmailID;
    const { egiftCardNumber } = this.props;
    const { checked: egiftLinkCard } = e.target;

    if (hasValue(email)) {
      showFullScreenLoader();
      // Call the egift link card endpoint if checkbox is checked.
      if (egiftLinkCard) {
        const response = callEgiftApi('eGiftLinkCard', 'POST', {
          card_number: egiftCardNumber,
        }, {}, true);
        if (response instanceof Promise) {
          response.then((result) => {
            removeFullScreenLoader();
            if (isValidResponse(result)) {
              this.setState({
                cardLinked: true,
              });
            } else if (isValidResponseWithFalseResult(result)) {
              // Show the error message when result is false.
              document.getElementById('egift_linkcard_error').innerHTML = result.response_message;
            } else {
              document.getElementById('egift_linkcard_error').innerHTML = getStringMessage('egift_endpoint_down');
            }
          });
        }
      } else {
        const response = callEgiftApi('eGiftUnlinkCard', 'POST');
        if (response instanceof Promise) {
          response.then((result) => {
            removeFullScreenLoader();
            if (isValidResponse(result)) {
              // Update the card link status once the unlink is successful.
              this.setState({
                cardLinked: false,
              });
            } else if (isValidResponseWithFalseResult(result)) {
              document.getElementById('egift_linkcard_error').innerHTML = result.response_message;
            } else {
              document.getElementById('egift_linkcard_error').innerHTML = getStringMessage('egift_endpoint_down');
            }
          });
        }
      }
    }

    return false;
  };

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    // Prepare the request object for redeem API.
    const { cart, egiftCardNumber } = this.props;
    showFullScreenLoader();
    // Invoke the redemption API to update the redeem amount.
    const response = performRedemption(cart.cart_id_int, updateAmount, egiftCardNumber, 'guest');
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
            // Update the cart total.
            showFullScreenLoader();
            const currentCart = window.commerceBackend.getCart(true);
            if (currentCart instanceof Promise) {
              currentCart.then((data) => {
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

  render = () => {
    const {
      open,
      amount,
      pendingAmount,
      isLinkedCardApplicable,
      cardLinked,
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
        })}

        <ConditionalView conditional={open}>
          <UpdateEgiftCardAmount
            closeModal={this.closeModal}
            open={open}
            amount={amount}
            updateAmount={this.handleAmountUpdate}
            cart={cart}
          />
        </ConditionalView>
        <div className="remove-egift-card">
          <button type="button" onClick={this.handleRemoveCard}>{Drupal.t('Remove', {}, { context: 'egift' })}</button>
          <div id="egift_remove_card_error" className="error" />
        </div>
        <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
        <ConditionalView condition={pendingAmount > 0}>
          <div className="full-redeemed">
            {Drupal.t('Pay @currencyCode @pendingAmount using another payment method to complete purchase', { '@currencyCode': currencyCode, '@pendingAmount': pendingAmount }, { context: 'egift' })}
          </div>
        </ConditionalView>
        <ConditionalView condition={isLinkedCardApplicable}>
          <input
            type="checkbox"
            id="link-egift-card"
            name="egift_link_card"
            onChange={this.handleCardLink}
            checked={cardLinked}
          />
          <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
          <div id="egift_linkcard_error" className="error" />
        </ConditionalView>
      </div>
    );
  }
}
