import React from 'react';
import {
  getImageUrl,
  getQueryStringForEgiftCards,
} from '../../utilities';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import EgiftCardsListStepOne from '../egifts-card-step-one';
import EgiftCardStepTwo from '../egift-card-step-two';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';

export default class EgiftCardPurchase extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      egiftItems: null,
      wait: false, // Waiting till we get data from api and show to user.
      activateStepTwo: false, // Set on amount select to show step 2.
      amountSet: 0,
    };
  }

  async componentDidMount() {
    const params = getQueryStringForEgiftCards();
    const response = await callMagentoApi('/V1/products', 'GET', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        egiftItems: response.data.items,
      });
    }
    this.setState({
      wait: true,
    });
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

      // Dispatch event to refresh the react minicart component.
      const refreshMiniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data() { return response.data; }, productInfo } });
      document.dispatchEvent(refreshMiniCartEvent);

      // Dispatch event to refresh the cart data.
      const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data() { return response.data; } } });
      document.dispatchEvent(refreshCartEvent);

      // Show minicart notification.
      Drupal.cartNotification.triggerNotification(productData);
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
    showFullScreenLoader();
    const { egiftItems } = this.state;
    const data = new FormData(e.target);

    // Set error flag for field validations.
    let isError = false;

    // Get firstname lastname values for validation.
    const name = data.get('egift-recipient-name');

    // Validate name field.
    if (name === '') {
      document.getElementById('fullname-error').innerHTML = Drupal.t('Please enter recipient name', {}, { context: 'egift' });
      document.getElementById('fullname-error').classList.add('error');
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById('fullname-error').innerHTML = '';
      document.getElementById('fullname-error').classList.remove('error');
    }

    // Get recipient email.
    const email = data.get('egift-recipient-email');
    // Validate email.
    if (email === '') {
      document.getElementById('email-error').innerHTML = Drupal.t('Please enter valid email address', {}, { context: 'egift' });
      document.getElementById('email-error').classList.add('error');
      isError = true;
    } else {
      document.getElementById('email-error').innerHTML = '';
      document.getElementById('email-error').classList.remove('error');
    }

    // Get egift card amount.
    const amount = data.get('egift-amount');
    // Validate amount selected.
    if (amount === 0) {
      this.handleAmountSelect(false, 0);
    }

    if (isError) {
      removeFullScreenLoader();
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
            hps_giftcard_sender_name: name,
            hps_giftcard_recipient_name: name,
            hps_giftcard_sender_email: email,
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
        };

        if (response.data.error) {
          // Prepare and dispatch the add to cart failed event.
          const form = document.getElementsByClassName('sku-base-form')[0];
          const productAddToCartFailed = new CustomEvent('product-add-to-cart-failed', {
            bubbles: true,
            detail: {
              params,
              productData,
              message: response.error_message,
            },
          });
          form.dispatchEvent(productAddToCartFailed);
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
    } = this.state;

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
            >
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
