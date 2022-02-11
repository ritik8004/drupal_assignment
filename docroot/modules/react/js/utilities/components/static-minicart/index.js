import React from 'react';
import ConditionalView from '../conditional-view';
import PriceElement from '../../../../alshaya_spc/js/utilities/special-price/PriceElement';

class StaticMinicart extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      cartQty: 0,
      totalAmount: 0,
    };
  }

  /**
   * Listen to cart refresh events triggered from other components.
   */
  componentDidMount = () => {
    // Check if the cart data is present in local storage.
    const cartData = Drupal.getItemFromLocalStorage('cart_data');
    if (cartData && cartData.cart !== undefined) {
      this.updateStaticMinicartValues(cartData.cart);
    }

    // Listen to `refreshCart` event triggered
    // if cart data not present in local storage.
    document.addEventListener('refreshCart', this.handleRefreshCart, false);

    // Listen to `refreshMiniCart` event triggered
    document.addEventListener('refreshMiniCart', this.handleRefreshCart, false);
  };

  /**
   * Remove the event listner when component gets deleted.
   */
  componentWillUnmount = () => {
    document.removeEventListener('refreshCart', this.handleRefreshCart, false);
    document.removeEventListener('refreshMiniCart', this.handleRefreshCart, false);
  };

  /**
   * Handler for cart refresh events.
   */
  handleRefreshCart = (e) => {
    const data = e.detail.data();
    this.updateStaticMinicartValues(data);
  };

  /**
   * Update static minicart cartQty and totalAmount from the cart data.
   */
  updateStaticMinicartValues = (cartData) => {
    if (cartData && typeof cartData.cart_id !== 'undefined' && cartData.cart_id !== null) {
      this.setState({
        cartQty: cartData.items_qty,
        totalAmount: cartData.minicart_total,
      });
    }
  };

  render() {
    const { cartQty, totalAmount } = this.state;

    return (
      <div id="static-minicart-section" className="static-minicart-section">
        <div className="static-minicart-wrapper">
          <ConditionalView condition={totalAmount > 0}>
            <a className="cart-link-total" href="/en/cart"><PriceElement amount={totalAmount} /></a>
          </ConditionalView>

          <a className="cart-link" href="/en/cart">
            <ConditionalView condition={cartQty > 0}>
              <span className="quantity">{cartQty}</span>
            </ConditionalView>
          </a>
        </div>
        <div id="static_minicart_notification" />
      </div>
    );
  }
}

export default StaticMinicart;
