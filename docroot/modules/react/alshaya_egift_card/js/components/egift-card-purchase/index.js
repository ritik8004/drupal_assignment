import React from 'react';
import {
  getImageUrl,
  getQueryStringForEgiftCards,
} from '../../utilities';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import EgiftCardsListStepOne from '../egifts-card-step-one';
import EgiftCardStepTwo from '../egift-card-step-two';
import { callEgiftApi } from '../../../../js/utilities/egiftCardHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import logger from '../../../../js/utilities/logger';
import Loading from '../../../../js/utilities/loading';
import { smoothScrollTo } from '../../../../alshaya_spc/js/utilities/smoothScroll';
import isAuraEnabled, { getAuraUserDetails } from '../../../../js/utilities/helper';
import { redeemAuraPoints } from '../../../../alshaya_spc/js/aura-loyalty/components/utilities/checkout_helper';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

export default class EgiftCardPurchase extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      egiftItems: null,
      wait: false, // Waiting till we get data from api and show to user.
      activateStepTwo: false, // Set on amount select to show step 2.
      amountSet: 0,
      formError: '', // Set form error from MDC.
    };
    // Set ref for error element.
    this.errorElementRef = React.createRef();
  }

  async componentDidMount() {
    const params = getQueryStringForEgiftCards();
    const response = await callEgiftApi('eGiftProductSearch', 'GET', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        egiftItems: response.data.items,
        wait: true,
      });
    } else {
      this.setState({
        wait: true,
      });
      // If /V1/products API is returning Error.
      logger.error('Error while calling the Egift Product search Data Api @params', {
        '@params': params,
      });
    }
  }

  /**
   * Show next step fields when user select amount.
   */
  handleAmountSelect = (activate, amount) => {
    this.setState({
      activateStepTwo: activate,
      amountSet: amount,
      action: 'add-to-cart',
    });
  }

  /**
   * Handle the response once the cart is updated.
   */
  handleUpdateCartResponse = (response, productData) => {
    const productInfo = productData;

    // Return response data if the error present to process it by component itself.
    if (response.data.error === true) {
      return response.data;
    }

    if (response.data.cart_id) {
      if ((typeof response.data.items[productInfo.sku] !== 'undefined')) {
        const cartItem = response.data.items[productInfo.sku];
        productInfo.totalQty = cartItem.qty;
      }

      // Dispatch event to refresh the react mini-cart component.
      const refreshMiniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data() { return response.data; }, productInfo } });
      document.dispatchEvent(refreshMiniCartEvent);

      // Dispatch event to refresh the cart data.
      const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data() { return response.data; } } });
      document.dispatchEvent(refreshCartEvent);

      // If Aura is enabled and already redeemed with aura points,
      // On adding eGfit products to cart remove aura redeemed points.
      if (isAuraEnabled()
        && (typeof response.data.totals.paidWithAura !== 'undefined'
          || typeof response.data.totals.balancePayable !== 'undefined'
          || typeof response.data.loyaltyCard !== 'undefined')) {
        const cardNumber = response.data.loyaltyCard;
        // Call API to remove redeemed aura points.
        const requestData = {
          action: 'remove points',
          userId: getAuraUserDetails().id,
          cardNumber,
        };
        redeemAuraPoints(requestData);
      }
      // Show minicart notification.
      Drupal.cartNotification.triggerNotification(productData);

      let productName = productData.product_name;

      if (hasValue(response.data.items)) {
        Object.keys(response.data.items).forEach((element) => {
          if (response.data.items[element].title === productData.product_name) {
            productName = response.data.items[element].itemGtmName;
          }
        });
      }

      // GTM product attributes.
      const productGtm = {
        name: `${productName}/${productData.price}`,
        price: productData.price,
        variant: productData.sku,
        category: 'eGift Card',
        dimension2: 'virtual',
        dimension4: 1,
        quantity: 1,
        metric2: productData.price,
      };

      // Push addtocart gtm event.
      Drupal.alshayaSeoGtmPushAddToCart(productGtm);
    }
    removeFullScreenLoader();

    // Reset eGift card purchase form.
    this.setState({
      activateStepTwo: false, // Set on amount select to show step 2.
      amountSet: 0,
    });

    // Empty open amount field.
    if (document.getElementById('open-amount') !== null) {
      document.getElementById('open-amount').value = '';
      document.getElementById('open-amount').removeAttribute('readonly');
    }

    // Redirect user to checkout if action button checkout is clicked.
    const { action } = this.state;
    if (action === 'checkout') {
      window.location = Drupal.url('checkout');
    }

    return response;
  };

  handleSubmit = (e) => {
    e.preventDefault();
    this.setState({
      formError: '',
    });
    showFullScreenLoader();
    const { egiftItems } = this.state;
    const data = new FormData(e.target);

    // Set error flag for field validations.
    let isError = false;

    // Get firstname lastname values for validation.
    const name = data.get('egift-recipient-name').trim();

    // Validate name field.
    if (name === '' || !name.match(/^[a-zA-Z ]+$/)) {
      document.getElementById('fullname-error').innerHTML = Drupal.t('Please enter recipient name', {}, { context: 'egift' });
      document.getElementById('fullname-error').classList.add('error');
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById('fullname-error').innerHTML = '';
      document.getElementById('fullname-error').classList.remove('error');
    }

    // Get recipient email.
    const email = data.get('egift-recipient-email').trim();
    // Validate email, check if email has @ and with domain after dot.
    if (email === '' || !(/^\w+([+.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(email))) {
      document.getElementById('email-error').innerHTML = Drupal.t('Please enter valid email address', {}, { context: 'egift' });
      document.getElementById('email-error').classList.add('error');
      isError = true;
    } else {
      document.getElementById('email-error').innerHTML = '';
      document.getElementById('email-error').classList.remove('error');
    }

    // If user is loggedin and  has selected freinds and family,
    // senderName and senderEmail should be of loggedin users.
    let senderName = name;
    let senderEmail = email;
    if (drupalSettings.userDetails.customerId > 0 && data.get('egift-for') === 'friends') {
      senderName = drupalSettings.userDetails.userName;
      senderEmail = drupalSettings.userDetails.userEmailID;
    }

    // Get egift card amount.
    const amount = data.get('egift-amount');
    // Validate amount selected.
    if (amount === 0) {
      this.handleAmountSelect(false, 0);
    }

    // Get open amount input element.
    const element = document.getElementById('open-amount');
    // Get open amount value.
    const openAmount = (element !== null) ? element.value : '';
    if (openAmount !== '') {
      // Min and Max value allowed for open amount.
      const amountFrom = parseFloat(element.getAttribute('min'));
      const amountTo = parseFloat(element.getAttribute('max'));

      // on submit compare if user input for open amount lies in the allowed range.
      if (parseFloat(openAmount) < amountFrom || parseFloat(openAmount) > amountTo) {
        document.getElementById('open-amount-error').innerHTML = Drupal.t('Please enter amount in the range of @amountFrom to @amountTo', {
          '@amountFrom': amountFrom,
          '@amountTo': amountTo,
        }, { context: 'egift' });
        isError = true;
      }
    }
    // on submit check if user input for open amount or amount list,
    // is not selected after switching the card.
    if (document.querySelectorAll('.item-amount.active').length === 0 && openAmount === '') {
      document.getElementById('open-amount-error').innerHTML = Drupal.t('Please enter amount or select from above.', {}, { context: 'egift' });
      isError = true;
    }
    if (isError) {
      removeFullScreenLoader();
      // Scroll to error.
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    const params = {
      action: 'add item',
      product_type: 'virtual',
      quantity: 1,
      sku: data.get('egift-sku'),
      options: {
        extension_attributes: {
          hps_giftcard_item_option: {
            hps_giftcard_amount: 'custom',
            hps_giftcard_sender_name: senderName,
            hps_giftcard_recipient_name: name,
            hps_giftcard_sender_email: senderEmail,
            hps_giftcard_recipient_email: email,
            hps_custom_giftcard_amount: amount,
            hps_giftcard_message: typeof data.get('egift-message') !== 'undefined' ? data.get('egift-message') : '',
            extension_attributes: {},
          },
        },
      },
    };

    let product = {};
    egiftItems.forEach((item) => {
      if (item.sku === data.get('egift-sku')) {
        product = item;
      }
    });

    const productImage = getImageUrl(product.custom_attributes, 'thumbnail');

    window.commerceBackend.addUpdateRemoveCartItem(params).then(
      (response) => {
        // Prepare product data.
        const productData = {
          quantity: params.quantity,
          sku: params.sku,
          parentSku: params.sku,
          options: {},
          variant: params.sku,
          image: productImage,
          product_name: product.name,
          price: amount,
        };

        if (response.data.error) {
          // Remove full screen loader.
          removeFullScreenLoader();

          // Get error message.
          let errorMessage = response.data.error_message;

          // If error code 604 set product not found.
          if (response.data.error_code === 604) {
            errorMessage = Drupal.t('The product that you are trying to add is not available.');
          }

          // Log error on datadog and ga.
          const label = `Update cart failed for Product [${params.sku}}] `;
          Drupal.alshayaSeoGtmPushAddToCartFailure(label, errorMessage);

          // Show error message.
          this.setState({
            formError: errorMessage,
          });

          // Scroll to error.
          smoothScrollTo('body');

          return false;
        }

        return this.handleUpdateCartResponse(
          response,
          productData,
        );
      },
    );
  };

  render() {
    const {
      egiftItems,
      wait,
      activateStepTwo,
      amountSet,
      formError,
    } = this.state;

    if (!wait && egiftItems === null) {
      // Show loader if wait is false as no Egift card found.
      return (
        <div className="egifts-form-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <>
        <ConditionalView condition={egiftItems === null && wait === true}>
          <div>
            <p>{Drupal.t('No eGift cards found.', {}, { context: 'egift' })}</p>
          </div>
        </ConditionalView>
        <ConditionalView condition={egiftItems !== null}>
          <div className="egifts-form-wrapper">
            <form
              onSubmit={this.handleSubmit}
              className="egift-form fadeInUp"
              id="egift-purchase-form"
              noValidate
            >
              <div
                ref={this.errorElementRef}
                className="error errors-container egift-purchase-page-error"
                id="edit-errors-container"
              >
                { formError }
              </div>
              <EgiftCardsListStepOne
                items={egiftItems}
                handleEgiftSelect={this.handleEgiftSelect}
                handleAmountSelect={this.handleAmountSelect}
              />
              <EgiftCardStepTwo activate={activateStepTwo} />
              <div className="action-buttons sku-base-form fadeInUp">
                <button
                  type="submit"
                  name="add-to-cart"
                  className="btn egift-purchase-add-to-cart-button"
                  disabled={!activateStepTwo}
                  onClick={() => { this.state.action = 'add-to-cart'; }}
                >
                  {Drupal.t('add to bag', {}, { context: 'egift' })}
                </button>
                <button
                  type="submit"
                  name="checkout"
                  className="btn egift-purchase-checkout-button"
                  disabled={!activateStepTwo}
                  onClick={() => { this.state.action = 'checkout'; }}
                >
                  {Drupal.t('checkout', {}, { context: 'egift' })}
                </button>
              </div>
              <input type="hidden" name="egift-amount" value={amountSet} />
            </form>
          </div>
        </ConditionalView>
      </>
    );
  }
}
