import React from 'react';
import EgiftCardAmount from '../../egifts-card-step-one/egift-card-amount';
import { getParamsForTopUpCardSearch } from '../../../utilities';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import {
  getDefaultErrorMessage,
  getProcessedErrorMessage,
} from '../../../../../js/utilities/error';
import { callEgiftApi } from '../../../../../js/utilities/egiftCardHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default class MyEgiftTopUp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      topUpCard: null,
      amountSet: 0, // Store amount selected for top-up by user.
      disableSubmit: true,
      displayFormError: '', // Show top-up submit errors.
    };

    // Set ref for openAmount field.
    this.ref = React.createRef();
  }

  async componentDidMount() {
    const params = getParamsForTopUpCardSearch();
    showFullScreenLoader();
    // Get Top up card details.
    const response = await callEgiftApi('eGiftProductSearch', 'GET', params);
    removeFullScreenLoader();
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      const { handleHideDetails } = this.props;
      handleHideDetails();
      this.setState({
        topUpCard: response.data.items[0],
      });
    }
  }

  /**
   * Show next step fields when user select amount.
   */
  handleAmountSelect = (submitButtonState, amount) => {
    this.setState({
      amountSet: amount,
      disableSubmit: !submitButtonState,
    });
  };


  handleTopUpSubmit = async (e) => {
    e.preventDefault();
    const { topUpCard, amountSet } = this.state;

    // Unset any errors displayed in previous submission.
    this.setState({
      displayFormError: '',
    });

    const { cardNumber } = this.props;

    // Check if cart id is present for anonymous or authenticated user.
    if (window.commerceBackend.isAnonymousUserWithoutCart()
      || await window.commerceBackend.isAuthenticatedUserWithoutCart()) {
      const cartId = await window.commerceBackend.createCart();

      // Show error if still cart id is null.
      if (!hasValue(cartId)) {
        this.setState({
          displayFormError: getDefaultErrorMessage(),
        });
        return false;
      }
    }

    // Prepare params for add top-up to cart.
    const params = {
      topup: {
        sku: topUpCard.sku,
        amount: amountSet,
        customer_email: drupalSettings.userDetails.userEmailID,
        card_number: cardNumber,
        top_up_type: 'self', // Top-up for linked card.
      },
    };

    showFullScreenLoader();

    // Call top-up API to add top-up to cart.
    // Don't use bearer token with top-up add to cart API as it is public API.
    callEgiftApi('eGiftTopup', 'POST', params, false)
      .then((response) => {
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
          const topUpQuote = {
            id: response.data.quote_details.id,
            maskedQuoteId: response.data.masked_quote_id,
            created: new Date().getTime(),
          };
          Drupal.addItemInLocalStorage('topupQuote', topUpQuote);
          window.location = Drupal.url('checkout');
        }
      });

    return true;
  };

  render() {
    const { topUpCard, disableSubmit, displayFormError } = this.state;
    const { handleCancelTopUp } = this.props;

    if (topUpCard === null) {
      return null;
    }

    return (
      <div className="egifts-form-wrapper">
        <div className="my-egift-top-up-wrapper">
          <EgiftCardAmount
            selected={topUpCard}
            handleAmountSelect={this.handleAmountSelect}
            myAccountLabel
            field={this.ref}
          />
          <div className="action-buttons">
            <div id="my-topup-error" className="error form-error">{displayFormError}</div>
            <div
              className="action-cancel"
            >
              <button
                type="button"
                className="my-topup-cancel-btn"
                onClick={() => handleCancelTopUp()}
              >
                {Drupal.t('Cancel', {}, { context: 'egift' })}
              </button>
            </div>
            <div
              className="action-topup"
            >
              <button
                type="button"
                name="top-up"
                className="btn my-topup-submit-btn"
                disabled={disableSubmit}
                onClick={(e) => this.handleTopUpSubmit(e)}
              >
                {Drupal.t('Top up', {}, { context: 'egift' })}
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
