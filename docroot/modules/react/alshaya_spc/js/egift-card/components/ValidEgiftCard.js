import React from 'react';
import getCurrencyCode from '../../../../js/utilities/util';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import {
  callEgiftApi,
  egiftCardHeader,
  isEgiftRedemptionDone,
  isValidResponse,
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

export default class ValidEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      amount: 0,
      pendingAmount: 0,
    };
  }

  componentDidMount = () => {
    // Calculate the remaining amount and egift card amount to be used.
    const {
      cart,
      removeCard,
    } = this.props;

    // Proceed only if redemption is done.
    if (isEgiftRedemptionDone(cart)) {
      this.setState({
        amount: cart.totals.egiftRedeemedAmount,
        pendingAmount: cart.totals.balancePayable,
      });
    } else if (isEgiftRedemptionDone(cart, 'linked')) {
      // Disable guest redemption.
      dispatchCustomEvent('changeEgiftRedemptionStatus', { status: true });
    } else {
      // Move back to the initial stage of redemption.
      removeCard();
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
    const errors = await removeCard();
    // Display the error message based on the response.
    if (errors) {
      document.getElementById('egift_remove_card_error').innerHTML = Drupal.t('There was some error while removing the gift card. Please try again', {}, { context: 'egift' });
    }

    return !errors;
  }

  // Update the user account with egift card.
  handleCardLink = (e) => {
    // Extract the current user email.
    const email = drupalSettings.userDetails.userEmailID;
    const { egiftCardNumber } = this.props;
    const { egift_link_card: egiftLinkCard } = e.target.elements;

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
              // @todo To display message for successful link.
            }
          });
        }
      } else {
        const response = callEgiftApi('eGiftUnlinkCard', 'POST');
        if (response instanceof Promise) {
          response.then((result) => {
            removeFullScreenLoader();
            if (isValidResponse(result)) {
              // @todo To display message for successful unlink.
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
        <ConditionalView condition={this.isCardLinkingApplicable()}>
          <input
            type="checkbox"
            id="link-egift-card"
            name="egift_link_card"
            onChange={this.handleCardLink}
          />
          <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
          <div id="egift_linkcard_error" className="error" />
        </ConditionalView>
      </div>
    );
  }
}
