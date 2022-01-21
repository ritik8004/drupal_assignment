import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import {
  egiftCardHeader,
  isEgiftRedemptionDone,
  isValidResponse,
  isValidResponseWithFalseResult,
  updateRedeemAmount,
} from '../../utilities/egift_util';
import { callEgiftApi } from '../../../../js/utilities/egiftCardHelper';
import UpdateEgiftCardAmount from './UpdateEgiftCardAmount';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import PriceElement from '../../utilities/special-price/PriceElement';
import logger from '../../../../js/utilities/logger';
import { getDefaultErrorMessage } from '../../../../js/utilities/error';

export default class ValidEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      amount: 0,
      pendingAmount: 0,
      isLinkedCardApplicable: false,
      disableLinkCard: false,
    };
  }

  componentDidMount = () => {
    const {
      cart,
    } = this.props;

    // Proceed only if redemption is done.
    if (isEgiftRedemptionDone(cart)) {
      this.setState({
        amount: cart.totals.egiftRedeemedAmount,
        pendingAmount: cart.totals.totalBalancePayable,
      });
    }

    // Validate if link card is applicable.
    if (isUserAuthenticated()) {
      // Invoke magento API to check if any egift card is already associated
      // with the user account.
      const response = callEgiftApi('eGiftHpsCustomerData', 'GET', {});
      if (response instanceof Promise) {
        response.then((result) => {
          if (isValidResponseWithFalseResult(result)) {
            // Set linkcard applicable as true.
            this.setState({
              isLinkedCardApplicable: true,
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
  };

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
          link_data: {
            action: 'verified_redemption_link',
            card_number: egiftCardNumber,
          },
        });
        if (response instanceof Promise) {
          response.then((result) => {
            if (isValidResponse(result)) {
              this.setState({
                disableLinkCard: true,
              });
            } else if (isValidResponseWithFalseResult(result)) {
              // Show the error message when result is false.
              document.getElementById('egift_linkcard_error').innerHTML = result.response_message;
              // Log error in datadog.
              logger.error('Error Response in eGiftLinkCard. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': 'link_card',
                '@cardNumber': egiftCardNumber,
                '@response': response.data,
              });
            } else {
              document.getElementById('egift_linkcard_error').innerHTML = getDefaultErrorMessage();
              // Log error in datadog.
              logger.error('Error Response in eGiftLinkCard. Action: @action CardNumber: @cardNumber Response: @response', {
                '@action': 'link_card',
                '@cardNumber': egiftCardNumber,
                '@response': response,
              });
            }
            // Remove loader once processing is done.
            removeFullScreenLoader();
          });
        }
      }
    }

    return false;
  };

  // Update egift amount.
  handleAmountUpdate = async (updateAmount) => {
    // Prepare the request object for redeem API.
    const { cart, refreshCart } = this.props;
    const result = await updateRedeemAmount(updateAmount, cart, refreshCart);
    if (!result.error) {
      const { redeemedAmount, totalBalancePayable } = result;
      // Update the state with the valid response from endpoint.
      this.setState({
        amount: redeemedAmount,
        open: false,
        pendingAmount: totalBalancePayable,
      });
    }

    return result;
  }

  render = () => {
    const {
      open,
      amount,
      pendingAmount,
      isLinkedCardApplicable,
      disableLinkCard,
    } = this.state;

    const { cart } = this.props;
    const appliedAmount = (
      <span>
        {Drupal.t('Applied card amount - ', {}, { context: 'egift' })}
        <span className="price-wrapper">
          <PriceElement amount={amount} format="string" showZeroValue />
        </span>
      </span>
    );

    return (
      <div className="egift-wrapper">
        <ConditionalView conditional={open}>
          <UpdateEgiftCardAmount
            closeModal={this.closeModal}
            open={open}
            amount={amount}
            updateAmount={this.handleAmountUpdate}
            cart={cart}
          />
        </ConditionalView>
        <div className="egift-redeem-applied-amount-wrapper">
          {egiftCardHeader({
            egiftHeading: appliedAmount,
          })}
          <div className="remove-egift-card">
            <button type="button" onClick={this.handleRemoveCard}>{Drupal.t('Remove', {}, { context: 'egift' })}</button>
            <div id="egift_remove_card_error" className="error" />
          </div>
        </div>
        <div className="egift-redeem-edit-amount" onClick={this.openModal}>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</div>
        <ConditionalView condition={pendingAmount > 0}>
          <div className="full-redeemed">
            {Drupal.t('Pay ', {}, { context: 'egift' })}
            {<PriceElement amount={pendingAmount} format="string" showZeroValue />}
            {Drupal.t(' using another payment method to complete purchase', {}, { context: 'egift' })}
          </div>
        </ConditionalView>
        <ConditionalView condition={isLinkedCardApplicable}>
          <div className="egift-redeem-link-card-wrapper">
            <input
              type="checkbox"
              id="guest-link-egift-card"
              name="egift_link_card"
              onChange={this.handleCardLink}
              disabled={disableLinkCard}
            />
            <label htmlFor="guest-link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
            <div id="egift_linkcard_error" className="error" />
          </div>
        </ConditionalView>
      </div>
    );
  }
}
