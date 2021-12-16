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

export default class EgiftTopPurchase extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      topUpCard: null,
      wait: false, // Flag to check api call is complete.
      amountSet: 0,
      activate: false,
      displayFormError: '',
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
      });
    }
  }

  /**
   * Show next step fields when user select amount.
   */
  handleAmountSelect = (activate, amount) => {
    this.setState({
      amountSet: amount,
      activate: true,
      displayFormError: '',
    });
  }

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

  handleSubmit = (e) => {
    e.preventDefault();
    showFullScreenLoader();
    this.setState({
      displayFormError: '',
    });

    // Get Form data.
    const data = new FormData(e.target);

    // Prepare params for add top-up to cart.
    const params = {
      topup: {
        sku: data.get('egift-sku'),
        amount: data.get('egift-amount'),
        // @todo update customer email for anonymous user.
        customer_email: (isUserAuthenticated()) ? drupalSettings.userDetails.userEmailID : 'test@test.com',
        card_number: data.get('card_number'),
        top_up_type: 'self',
      },
    };

    // Call top-up API to add top-up to cart.
    // Don't use bearer token with top-up add to cart API as it is public API.
    const result = callMagentoApi('/V1/egiftcard/topup', 'POST', params, false);
    if (result instanceof Promise) {
      result.then((response) => {
        removeFullScreenLoader();
        if (response.data.error) {
          this.setState({
            displayFormError: response.data.error_message,
          });
        }

        if (typeof response.data.response_type !== 'undefined' && response.data.response_type === false) {
          let message = getProcessedErrorMessage(response);
          if (message === undefined) {
            message = getDefaultErrorMessage();
          }
          this.setState({
            displayFormError: message,
          });
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
      activate,
      amountSet,
      displayFormError,
    } = this.state;

    return (
      <div className="egifts-form-wrap">
        <form onSubmit={this.handleSubmit} className="egift-form">
          <ConditionalView condition={wait === true}>
            <div className="egift-hero-wrap">
              <HeroImage item={topUpCard} />
            </div>
            <div className="egift-topup-fields-wrap">
              <EgiftTopupFor />
              <EgiftCardAmount selected={topUpCard} handleAmountSelect={this.handleAmountSelect} />
              <input type="hidden" name="egift-amount" value={amountSet} />
            </div>
          </ConditionalView>
          <div className="action-buttons">
            <input
              type="hidden"
              name="egift-sku"
              value={(topUpCard !== null) ? topUpCard.sku : ''}
            />
            <div className="error form-error">{displayFormError}</div>
            <button
              type="submit"
              name="top-up"
              className="btn"
              disabled={!activate}
            >
              {Drupal.t('Top-up', {}, { context: 'egift' })}
            </button>
          </div>
        </form>
      </div>
    );
  }
}
