import React from 'react';
import HeroImage from '../egifts-card-step-one/hero-image';
import EgiftCardAmount from '../egifts-card-step-one/egift-card-amount';
import {
  getParamsForTopUpCardSearch,
} from '../../utilities';
import { callEgiftApi } from '../../../../js/utilities/egiftCardHelper';
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
} from '../../../../js/utilities/error';
import logger from '../../../../js/utilities/logger';
import Loading from '../../../../js/utilities/loading';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

export default class EgiftTopPurchase extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      topUpCard: null, // Store top-up card details.
      wait: false, // Flag to check api call is complete.
      amountSet: 0, // Amount select by user.
      linkedCardNumber: null, // User linked card number.
      linkedCardBalance: null, // User linked card balance.
      linkedCardImage: null, // Card number image.
      eGiftFor: 'other', // Flag to switch heroImage.
      disableSubmit: true, // Flag to enable / disable top-up submit button.
      displayFormError: '', // Display form errors.
      cardNumberError: '', // Display card number error.
    };

    // Set ref for openAmount field.
    this.ref = React.createRef();
  }

  async componentDidMount() {
    const params = getParamsForTopUpCardSearch();
    // Get Top up card details.
    const response = await callEgiftApi('eGiftProductSearch', 'GET', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        topUpCard: response.data.items[0],
        wait: true,
      }, () => this.getUserLinkedCard());
    } else {
      this.setState({
        wait: true,
      });
      // If /V1/products API is returning Error.
      logger.error('Error while calling the Topup Product search Data Api @params', {
        '@params': params,
      });
    }
  }

  /**
   * Get User linked card helper.
   */
  getUserLinkedCard = () => {
    if (!isUserAuthenticated()) {
      return;
    }
    showFullScreenLoader();
    // Call to get customer linked card details.
    const result = callEgiftApi('eGiftHpsCustomerData', 'GET', {});
    if (result instanceof Promise) {
      result.then((response) => {
        removeFullScreenLoader();
        if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
          this.setState({
            linkedCardNumber: response.data.card_number !== null ? response.data.card_number : null,
            linkedCardBalance:
              response.data.current_balance !== null ? response.data.current_balance : null,
            linkedCardImage: {
              url: response.data.card_image,
              title: response.data.card_type,
              alt: response.data.card_type,
            },
            // if linked card show linked card topup image for my card option,
            // show default topup image for no linked card and other option.
            eGiftFor: response.data.card_number !== null ? 'self' : 'other',
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
    // Add expiration timestamp in the object.
    const topUpQuote = {
      id: data.quote_details.id,
      maskedQuoteId: data.masked_quote_id,
      created: new Date().getTime(),
    };
    Drupal.addItemInLocalStorage('topupQuote', topUpQuote);
  };

  logAddTopupError = (params, response) => {
    logger.error('Error while trying adding top-up. @params @response', {
      '@params': params,
      '@response': response,
    });
  };

  handleSubmit = async (e) => {
    e.preventDefault();

    // Unset any errors displayed in previous submission.
    this.setState({
      displayFormError: '',
      cardNumberError: '',
    });

    // Get Form data.
    const data = new FormData(e.target);
    const {
      topUpCard,
      amountSet,
      linkedCardNumber,
    } = this.state;

    // Get top-up card for options. For anonymous top-up-type default set to other.
    const egiftCardFor = data.get('egift-for') !== null ? data.get('egift-for') : 'other';
    // If card for options is self then get linked-card-number from state
    // else get card-number from field.
    const cardNumber = egiftCardFor === 'self' ? linkedCardNumber : data.get('card_number').trim();

    if (cardNumber === '') {
      document.getElementById('card-number-error').innerHTML = Drupal.t('Please enter an eGift card number.', {}, { context: 'egift' });
      return false;
    }

    // Check if cart id is present for anonymous or authenticated user.
    if (window.commerceBackend.isAnonymousUserWithoutCart()
      || await window.commerceBackend.isAuthenticatedUserWithoutCart()) {
      const cartId = await window.commerceBackend.createCart();

      // Show error if still cart id is null.
      if (!hasValue(cartId)) {
        document.getElementById('top-up-error').innerHTML = getDefaultErrorMessage();
        return false;
      }
    }

    // Prepare params for add top-up to cart.
    const params = {
      topup: {
        sku: topUpCard.sku,
        amount: amountSet,
        customer_email: (isUserAuthenticated()) ? drupalSettings.userDetails.userEmailID : '',
        card_number: cardNumber,
        // For anonymous top-up-type default set to other.
        top_up_type: egiftCardFor,
      },
    };

    // Show loader before api call.
    showFullScreenLoader();

    // Call top-up API to add top-up to cart.
    // Don't use bearer token with top-up add to cart API as it is public API.
    const result = callEgiftApi('eGiftTopup', 'POST', params, false);
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
        if (typeof response.data !== 'undefined' && response.data.response_type === false) {
          // If egift card is for others, then show card number error.
          if (egiftCardFor === 'other') {
            this.setState({
              cardNumberError: response.data.response_message,
            });
            this.logAddTopupError(params, response);
          } else {
            // Show response error on submit.
            this.setState({
              displayFormError: response.data.response_message,
            }, () => this.logAddTopupError(params, response));
          }
        }

        // Redirect user to checkout if response type is true.
        if (typeof response.data.response_type !== 'undefined' && response.data.response_type) {
          // GTM product attributes.
          const productGtm = {
            name: `${topUpCard.name}/${params.topup.amount}`,
            price: params.topup.amount,
            variant: topUpCard.sku,
            dimension2: topUpCard.type_id,
            dimension4: 1,
            quantity: 1,
            metric2: params.topup.amount,
          };

          // Push addtocart gtm event.
          Drupal.alshayaSeoGtmPushAddToCart(productGtm);

          // Add top-up quote Id to storage.
          this.setTopUpQuoteIdInStorage(response.data);

          // Redirect to checkout
          window.location = Drupal.url('checkout');
        }
      });
    }
    return true;
  };

  handleImage = (eGiftForOption) => {
    this.setState({
      eGiftFor: eGiftForOption,
    });
  };

  render() {
    const {
      topUpCard,
      wait,
      disableSubmit,
      linkedCardNumber,
      linkedCardBalance,
      linkedCardImage,
      eGiftFor,
      displayFormError,
      cardNumberError,
    } = this.state;

    if (wait && topUpCard === null) {
      // Return if wait is true as no top-up card found.
      return null;
    }
    if (!wait && topUpCard === null) {
      // Show loader if wait is false as no top-up card found.
      return (
        <div className="egifts-form-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    const heroImage = (eGiftFor === 'self') ? linkedCardImage : topUpCard;

    return (
      <div className="egifts-form-wrapper">
        <form onSubmit={this.handleSubmit} className="egift-form">
          <ConditionalView condition={wait === true}>
            <div className="step-wrapper step-one-wrapper fadeInUp">
              <HeroImage item={heroImage} />
              <div className="egift-topup-fields-wrapper">
                <EgiftTopupFor
                  linkedCardNumber={linkedCardNumber}
                  linkedCardBalance={linkedCardBalance}
                  cardNumberError={cardNumberError}
                  handleImage={this.handleImage}
                />
                <EgiftCardAmount
                  selected={topUpCard}
                  handleAmountSelect={this.handleAmountSelect}
                  field={this.ref}
                />
                <div className="action-buttons">
                  <div className="error form-error" id="top-up-error">{displayFormError}</div>
                  <button
                    type="submit"
                    name="top-up"
                    className="btn"
                    disabled={disableSubmit}
                  >
                    {Drupal.t('Top up', {}, { context: 'egift' })}
                  </button>
                </div>
              </div>
            </div>
          </ConditionalView>
        </form>
      </div>
    );
  }
}
