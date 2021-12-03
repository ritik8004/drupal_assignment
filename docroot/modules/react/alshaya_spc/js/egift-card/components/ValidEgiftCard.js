import React from 'react';
import getCurrencyCode from '../../../../js/utilities/util';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { egiftCardHeader } from '../../utilities/egift_util';
import UpdateEgiftCardAmount from './UpdateEgiftCardAmount';
import { getApiEndpoint } from '../../backend/v2/utility';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import logger from '../../../../js/utilities/logger';
import dispatchCustomEvent from '../../utilities/events';

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
    const { quoteId } = this.props;
    const postData = {
      redeem_points: {
        action: 'remove_points',
        quote_id: quoteId,
      }
    }
    // Invoke the redemption API.
    const endpoint = getApiEndpoint('eGiftRedemption');
    const response = callMagentoApi(endpoint, 'POST', postData);
    if (response instanceof Promise) {
      // Handle the error and success message after the egift card is removed
      // from the cart.
      response.then((result) => {
        let messageInfo = null;
        if (result.error !== undefined) {
          messageInfo = {
            type: 'error',
            message: result.response_message,
          };
        } else {
          messageInfo = {
            type: 'success',
            message: result.response_message,
          };
        }
      });
      // Trigger message.
      if (messageInfo !== null) {
        dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
      }
    }
  }

  // Update the user account with egift card.
  handleCardLink = () => {

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
      if (typeof response.data !== 'undefined'
        && typeof response.data.error === 'undefined') {
        // Update the amount in state.
        this.setState({
          amount: updateAmount,
        });
      }
    }
    this.closeModal();
  }

  // Whether user is applicable to link card in the account.
  isCardLinkingApplicable = () => {
    // @todo To fetch the email value from localStorage or static storage.
    const params = { email: 'admin@example.com' };
    const endpoint = getApiEndpoint('eGiftHpsSearch', params);
    if (endpoint) {
      // Invoke magento API to check if any egift card is already associated
      // with the user account.
      const response = callMagentoApi(endpoint, 'GET');
      if (typeof response.data !== 'undefined'
        && typeof response.data.error === 'undefined') {
        // return false if card number is already linked else true.
        return !response.data.card_number;
      }
      // Handle error response.
      if (response.data.error) {
        logger.error('Error while calling the egift HPS Search. EmailId: @emailId. Response: @response', {
          '@emailId': params.email,
          '@response': JSON.stringify(response.data),
        });
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
        </ConditionalView>
      </div>
    );
  }
}
