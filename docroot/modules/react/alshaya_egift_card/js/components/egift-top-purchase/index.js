import React from 'react';
import HeroImage from '../egifts-card-step-one/hero-image';
import EgiftCardAmount from '../egifts-card-step-one/egift-card-amount';
import {
  getParamsForTopUpCardSearch,
} from '../../utilities';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import EgiftTopupFor from '../egift-topup-for';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../js/utilities/showRemoveFullScreenLoader';
import {
  getDefaultErrorMessage,
  getProcessedErrorMessage,
} from '../../../../js/utilities/error';
import { setStorageInfo } from '../../../../js/utilities/storage';
import logger from '../../../../js/utilities/logger';

export default class EgiftTopPurchase extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      topUpCard: null, // Store top-up card details.
      wait: false, // Flag to check api call is complete.
      amountSet: 0, // Amount select by user.
      linkedCardNumber: null, // User linked card number.
      linkedCardBalance: null, // User linked card balance.
      disableSubmit: true, // Flag to enable / disable top-up submit button.
      displayFormError: '', // Display form errors.
    };
  }

  async componentDidMount() {
    const params = getParamsForTopUpCardSearch();
    // Get Top up card details.
    const response = await callMagentoApi('/V1/products', 'GET', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        topUpCard: response.data.items[0],
        wait: true,
      }, () => this.getUserLinkedCard());
    }
  }

  /**
   * Get User linked card helper.
   */
  getUserLinkedCard = () => {
    if (!isUserAuthenticated()) {
      return;
    }
    // Call to get customer linked card details.
    const result = callMagentoApi('/V1/customers/hpsCustomerData', 'GET', {});
    if (result instanceof Promise) {
      result.then((response) => {
        if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
          this.setState({
            linkedCardNumber: response.data.card_number !== null ? response.data.card_number : null,
            linkedCardBalance:
              response.data.current_balance !== null ? response.data.current_balance : null,
          });
        }
      });
    }
  };

  /**
   * Show next step fields when user select amount.
   */
  handleAmountSelect = (submitButtonState, amount) => {
    this.setState({
      amountSet: amount,
      disableSubmit: !submitButtonState,
      displayFormError: '',
    });
  };

  /**
   * Set the quote id received from topup api response in storage.
   */
  setTopUpQuoteIdInStorage = (data) => {
    const topUpQuote = {
      id: data.quote_details.id,
      maskedQuoteId: data.masked_quote_id,
    };
    setStorageInfo(topUpQuote, 'topupQuote');
  };

  logAddTopupError = (params, response) => {
    logger.error('Error while trying adding top-up. @params @response', {
      '@params': params,
      '@response': response,
    });
  };

  handleSubmit = (e) => {
    e.preventDefault();
    showFullScreenLoader();

    // Unset any errors displayed in previous submission.
    this.setState({
      displayFormError: '',
    });

    // Get Form data.
    const data = new FormData(e.target);
    const {
      topUpCard,
      amountSet,
      linkedCardNumber,
    } = this.state;

    let cardNumber = '';
    if (data.get('egift-for') === 'self') {
      cardNumber = linkedCardNumber;
    } else {
      cardNumber = data.get('card_number');
    }

    // Prepare params for add top-up to cart.
    const params = {
      topup: {
        sku: topUpCard.sku,
        amount: amountSet,
        // @todo update customer email for anonymous user.
        customer_email: (isUserAuthenticated()) ? drupalSettings.userDetails.userEmailID : 'test@test.com',
        card_number: cardNumber,
        top_up_type: data.get('egift-for'),
      },
    };

    // Call top-up API to add top-up to cart.
    // Don't use bearer token with top-up add to cart API as it is public API.
    const result = callMagentoApi('/V1/egiftcard/topup', 'POST', params, false);
    if (result instanceof Promise) {
      result.then((response) => {
        removeFullScreenLoader();

        // In case of exception from magento, log error and show default error message.
        if (response.data.error) {
          this.setState({
            displayFormError: getDefaultErrorMessage(),
          }, () => this.logAddTopupError(params, response));
        }

        // In case of 200 response but with error process error message to display and log error.
        if (typeof response.data.response_type !== 'undefined' && response.data.response_type === false) {
          let message = getProcessedErrorMessage(response);
          if (message === undefined) {
            message = getDefaultErrorMessage();
          }
          this.setState({
            displayFormError: message,
          }, () => this.logAddTopupError(params, response));
        }

        // Redirect user to checkout if response type is true.
        if (typeof response.data.response_type !== 'undefined' && response.data.response_type) {
          this.setTopUpQuoteIdInStorage(response.data);
          window.location = Drupal.url('checkout');
        }
      });
    }
  };

  render() {
    const {
      topUpCard,
      wait,
      disableSubmit,
      linkedCardNumber,
      linkedCardBalance,
      displayFormError,
    } = this.state;

    if (!wait) {
      // Return if wait is false as no top-up card found.
      return null;
    }

    return (
      <div className="egifts-form-wrap">
        <form onSubmit={this.handleSubmit} className="egift-form">
          <ConditionalView condition={wait === true}>
            <div className="egift-hero-wrap">
              <HeroImage item={topUpCard} />
            </div>
            <div className="egift-topup-fields-wrap">
              <EgiftTopupFor
                linkedCardNumber={linkedCardNumber}
                linkedCardBalance={linkedCardBalance}
              />
              <EgiftCardAmount selected={topUpCard} handleAmountSelect={this.handleAmountSelect} />
            </div>
          </ConditionalView>
          <div className="action-buttons">
            <div className="error form-error">{displayFormError}</div>
            <button
              type="submit"
              name="top-up"
              className="btn"
              disabled={disableSubmit}
            >
              {Drupal.t('Top-up', {}, { context: 'egift' })}
            </button>
          </div>
        </form>
      </div>
    );
  }
}
