import React from 'react';
import _debounce from 'lodash/debounce';
import {
  triggerUpdateCart,
  isMaxSaleQtyEnabled,
  isHideMaxSaleMsg,
  pushSeoGtmData,
  triggerCartTextNotification,
} from '../../utilities/addtobag';
import ErrorMessage from '../error-message';
import QuantitySelector from '../quantity-selector';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { isProductBuyable } from '../../../../js/utilities/display';
import NotBuyableButton from '../buttons/not-buyable';
import getStringMessage from '../../../../js/utilities/strings';
import { isWishlistPage } from '../../../../js/utilities/wishlistHelper';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

export default class AddToBagSimple extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      cartId: null,
      cartQty: 0,
      qtyLimitMessage: null,
      stockQtyLimit: null,
    };

    // Store reference to the main contiainer.
    this.buttonContainerRef = React.createRef();

    // Handle debounce effect for the click handler.
    this.handleUserAction = _debounce(this.handleUserAction, 100);
  }

  /**
   * Listen to `refreshCart` event triggered from `mini-cart/index.js`.
   */
  componentDidMount = () => {
    // Check if the cart data is present in local storage.
    const cartData = Drupal.alshayaSpc.getCartData();
    const cartIdAndQty = this.updateCartIdAndQty(cartData);
    const { productData, stockQty } = this.props;
    let { cartQty, qtyLimitMessage } = this.state;

    cartQty = typeof cartIdAndQty.cartQty !== 'undefined'
      ? cartIdAndQty.cartQty
      : cartQty;
    // Get max sale quantity from the algolia indexed data.
    const maxSaleQty = (typeof productData.max_sale_qty !== 'undefined') ? productData.max_sale_qty : 0;
    // Define enable or disable button conditions for quantity selector.
    // If max sale quantity limit enabled, do check for the limit.
    const stockQtyLimit = (isMaxSaleQtyEnabled())
      ? Math.min(stockQty, maxSaleQty)
      : stockQty;
    const isEnabledIncreaseBtn = (cartQty < stockQtyLimit || stockQtyLimit === 0);
    qtyLimitMessage = (!isEnabledIncreaseBtn && !isHideMaxSaleMsg())
      ? getStringMessage('purchase_limit_error_msg')
      : qtyLimitMessage;

    this.setState({ qtyLimitMessage, stockQtyLimit });

    // Listen to `refreshCart` event triggered
    // if cart data not present in local storage.
    document.addEventListener('refreshCart', this.handleRefreshCart, false);
  };

  /**
   * Remove the event listner when component gets deleted.
   */
  componentWillUnmount = () => {
    document.removeEventListener('refreshCart', this.handleRefreshCart, false);
  };

  /**
   * Handler for global refresh cart event.
   */
  handleRefreshCart = (e) => {
    const data = e.detail.data();
    this.updateCartIdAndQty(data);
  };

  /**
   * Check if cart ID is available and set in state variable and
   * check if product is available in cart and set cart quantity.
   */
  updateCartIdAndQty = (cartData) => {
    const { sku } = this.props;
    const { cartQty, cartId } = this.state;
    const stateData = {};

    if (cartData && typeof cartData.cart_id !== 'undefined') {
      // If the cart ID is different than change the current state value.
      if (cartData.cart_id !== cartId) {
        stateData.cartId = cartData.cart_id;
      }

      // If product have different qty in cart compare to current state.
      if (hasValue(cartData.items) && typeof cartData.items[sku] !== 'undefined') {
        const cartItem = cartData.items[sku];
        if (cartItem.qty !== cartQty) {
          stateData.cartQty = cartItem.qty;
        }
      } else if (cartQty > 0) {
        // If product is removed from the cart, set state
        // quantity to zero for add button to display.
        stateData.cartQty = 0;
      }

      // Check if we have something to change in state.
      if (Object.keys(stateData).length) {
        this.setState(stateData);
      }
    }

    return stateData;
  };

  /**
   * Handle onClick event on add button.
   *
   * @param {object} e
   *   The event object.
   */
  onClickHandler = (e) => {
    e.preventDefault();
    e.persist();
    e.stopPropagation();

    this.handleUserAction(e, 'add');
  };

  /**
   * Validate and prepare cart actions and quantity.
   * Call cart operation handler with the updated details.
   */
  handleUserAction = (e, action) => {
    const { sku } = this.props;
    let { cartQty } = this.state;
    const prevQty = cartQty;
    let notify = true;
    let cartAction = action;

    // Check if the product data exists in local storage, fetch
    // the latest cart quantity. If not, perform add operation.
    const cartData = Drupal.alshayaSpc.getCartData();
    if (cartData) {
      if (typeof cartData.cart_id !== 'undefined') {
        if (typeof cartData.items[sku] !== 'undefined') {
          cartQty = cartData.items[sku].qty;
        } else {
          // When product is not in the cart, set quantity to 0.
          cartQty = 0;
        }
      }
    }

    // Process actions from the quantity selector component.
    if (action === 'increase' || action === 'add') {
      cartAction = (cartQty === 0) ? 'add item' : 'update item';
      cartQty += 1;
      // Trigger Product Details View GTM push only while adding to cart.
      Drupal.alshayaSeoGtmPushProductDetailView(this.buttonContainerRef.current.closest('article.node--view-mode-search-result'));
    } else if (action === 'decrease') {
      // Show add button when trying to decrease the quantity
      // while product is not in cart.
      if (cartQty === 0) {
        this.setState({ cartQty: 0 });
        return;
      }

      // Change the action to remove item if quantity to update is zero.
      cartAction = ((cartQty - 1) === 0) ? 'remove item' : 'update item';
      cartQty -= 1;

      // Disable cart notification for decrease button.
      notify = false;
    }

    this.handleCartOperation(e, cartQty, prevQty, cartAction, notify);
  };

  /**
   * Handle cart operations of adding, updating and removing of products.
   * @todo: Later try to move this to utilities, if possible.
   */
  handleCartOperation = (e, qty, prevQty, action, notify) => {
    const { sku, productData } = this.props;
    const { cartId } = this.state;

    // Get the container element for placing the loader effect.
    const btnContainer = e.target.parentNode;

    // Adding the loader class to start spinner.
    btnContainer.classList.toggle('add-to-basket-loader');

    // Define default values for product image and cart title.
    let productImage = null;
    let productCartTitle = null;

    // Get algolia indexed data for product image and cart title.
    if (typeof productData !== 'undefined') {
      if (typeof productData.cart_image !== 'undefined') {
        productImage = productData.cart_image;
      }
      if (typeof productData.cart_title !== 'undefined') {
        productCartTitle = productData.cart_title;
      }
    }

    // Call utility function to add product in cart.
    triggerUpdateCart(
      {
        action,
        sku,
        qty,
        variant: '',
        options: [],
        productImage,
        productCartTitle,
        cartId,
        notify,
      },
    ).then((response) => {
      // Remove the loader class to stop spinner.
      btnContainer.classList.toggle('add-to-basket-loader');

      // Show error message if error present.
      if (response.error === true) {
        // Push error events to GTM.
        pushSeoGtmData({ sku, error: true, error_message: response.error_message });
        // Trigger a minicart notification.
        triggerCartTextNotification(response.error_message, 'error');

        return;
      }

      // We only want to show qty limit message on page load. So if any cart
      // operation is performed, which means decrease operation, we hide the
      // message.
      this.setState({ qtyLimitMessage: null });

      // Push product values to GTM.
      pushSeoGtmData({ element: this.buttonContainerRef.current, qty, prevQty });

      // Dispatch add to cart event for plp products.
      // We only dispatch event if item is added or updated in cart.
      if (action === 'add item' || action === 'update item') {
        dispatchCustomEvent('product-add-to-cart-success', { sku });
      }
    });
  };

  render() {
    const {
      sku, isBuyable, url, extraInfo,
    } = this.props;

    const {
      cartQty, stockQtyLimit,
    } = this.state;

    let { qtyLimitMessage } = this.state;

    // Early return if product is not buyable.
    if (!isProductBuyable(isBuyable)) {
      return (
        <NotBuyableButton url={url} />
      );
    }

    // Define component visibility condition.
    let btnCondition = (typeof cartQty === 'undefined') || (cartQty === 0) || (cartQty === null);
    const wrapperClasses = (!btnCondition ? 'addtobag-button-qty-wrapper' : '');
    const isEnabledDecreaseBtn = (cartQty > 0);
    const isEnabledIncreaseBtn = (cartQty < stockQtyLimit || stockQtyLimit === 0);

    // Check for the max sale limit reached.
    qtyLimitMessage = (!isEnabledIncreaseBtn && !isHideMaxSaleMsg())
      ? getStringMessage('purchase_limit_error_msg')
      : qtyLimitMessage;

    let addToCartText = getStringMessage('add_to_cart');
    // Check if button text is available in extraInfo. For example we are
    // passing a different button text for the wishlist page.
    if (typeof extraInfo.addToCartButtonText !== 'undefined') {
      addToCartText = extraInfo.addToCartButtonText;
    }

    // If the current page the wishlist page, we always want to show
    // add to bag button component and not increase/decrease component.
    if (isWishlistPage(extraInfo)) {
      btnCondition = true;
    }

    return (
      <div
        className={`addtobag-button-container ${wrapperClasses}`}
        ref={this.buttonContainerRef}
      >
        <ConditionalView condition={btnCondition}>
          <div className="addtobag-simple-button-container">
            <button
              className="addtobag-button"
              id={`addtobag-button-${sku}`}
              type="button"
              onClick={this.onClickHandler}
            >
              {addToCartText}
            </button>
          </div>
        </ConditionalView>

        <ConditionalView condition={!btnCondition}>
          <QuantitySelector
            type="inc_dec"
            qty={cartQty}
            qtyText={getStringMessage('in_basket')}
            isEnabledDecreaseBtn={isEnabledDecreaseBtn}
            isEnabledIncreaseBtn={isEnabledIncreaseBtn}
            onClickCallback={this.handleUserAction}
          />
        </ConditionalView>

        <ErrorMessage message={qtyLimitMessage} />
      </div>
    );
  }
}
