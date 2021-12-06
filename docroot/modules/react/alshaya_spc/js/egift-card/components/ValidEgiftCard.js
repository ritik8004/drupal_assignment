import React from 'react';
import getCurrencyCode from '../../../../js/utilities/util';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { egiftCardHeader } from '../../utilities/egift_util';
import UpdateEgiftCardAmount from './UpdateEgiftCardAmount';
import { getApiEndpoint } from '../../backend/v2/utility';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { isUserAuthenticated } from '../../../../js/utilities/helper';

export default class ValidEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      amount: 0,
      remainingAmount: 0,
    };
  }

  componentDidMount = () => {
    // @todo get amount and update the state.
    this.setState({
      amount: 280,
      remainingAmount: 220.0,
    });
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
    const { quoteId, removeCard } = this.props;
    const postData = {
      redeem_points: {
        action: 'remove_points',
        quote_id: quoteId,
      },
    };
    // Invoke the redemption API.
    const endpoint = getApiEndpoint('eGiftRedemption');
    const response = callMagentoApi(endpoint, 'POST', postData);
    if (response instanceof Promise) {
      // Handle the error and success message after the egift card is removed
      // from the cart.
      let messageInfo = null;
      response.then((result) => {
        if (result.error !== undefined) {
          messageInfo = {
            type: 'error',
            message: Drupal.t('There was some error while removing the gift card. Please try again', {}, { context: 'egift' }),
          };
        } else {
          messageInfo = {
            type: 'success',
            message: result.response_message,
          };
        }
        // Trigger the remove method of parent component to move back to the
        // initial redeem stage.
        removeCard();
      });
      // Trigger message.
      if (messageInfo !== null) {
        dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
      }
    }
  }

  // Update the user account with egift card.
  handleCardLink = () => {
    // Extract the current user email.
    const params = { email: drupalSettings.userDetails.userEmailID };
    if (params.email) {
      // @todo Call user acount link API.
    }

    return false;
  }

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    // Prepare the request object for redeem API.
    const { quoteId } = this.props;
    const postData = {
      redeem_points: {
        action: 'set_points',
        quote_id: quoteId,
        amount: updateAmount,
        card_number: '4250120656063430',
        payment_method: 'hps_payment',
      },
    };
    // Proceed only if postData object is available.
    if (postData) {
      // Invoke the redemption API to update the redeem amount.
      const endpoint = getApiEndpoint('eGiftRedemption');
      const response = callMagentoApi(endpoint, 'POST', postData);
      if (response instanceof Promise) {
        // Update the amount in state.
        response.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              amount: updateAmount,
            });
          } else if (result.error || result.status !== 200) {
            return false;
          }

          return true;
        });
      }
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
      const endpoint = getApiEndpoint('eGiftHpsSearch', params);
      if (endpoint) {
        // Invoke magento API to check if any egift card is already associated
        // with the user account.
        const response = callMagentoApi(endpoint, 'GET');
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
    }

    return false;
  }

  render = () => {
    const { open, amount, remainingAmount } = this.state;
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
          />
        </ConditionalView>
        <div className="remove-egift-card">
          <button type="button" onClick={this.handleRemoveCard}>{Drupal.t('Remove', {}, { context: 'egift' })}</button>
        </div>
        <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
        <ConditionalView condition={this.isCardLinkingApplicable()}>
          <input type="checkbox" id="link-egift-card" onChange={this.handleCardLink} />
          <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
          <div id="egift_linkcard_error" className="error" />
        </ConditionalView>
      </div>
    );
  }
}
