import React from 'react';

import CheckoutSectionTitle from '../spc-checkout-section-title';
import CartItems from '../cart-items';
import CartRecommendedProducts from '../recommended-products';
import MobileCartPreview from '../mobile-cart-preview';
import OrderSummaryBlock from "../../../utilities/order-summary-block";
import CheckoutMessage from '../../../utilities/checkout-message';
import CartPromoBlock from "../cart-promo-block";
import Loading from "../../../utilities/loading";

export default class Cart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'items': [],
      'totals': [],
      'recommended_products': [],
      'total_items': null,
      'amount': null,
      'coupon_code': null,
      'in_stock': true
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      const data = e.detail.data();

      // If there is no error.
      if (data.error === undefined) {
        this.setState(state => ({
          items: data.items,
          totals: data.totals,
          recommended_products: data.recommended_products,
          total_items: data.items_qty,
          amount: data.cart_total,
          wait: false,
          coupon_code: data.coupon_code,
          in_stock: data.in_stock
        }));

        if (data.cart_total <= 0 || data.items.length === 0) {
          this.setState({
            wait: true
          });
        }
      }

      // To show the success/error message on cart top.
      if (data.message !== undefined) {
        this.setState({
          message_type: data.message.type,
          message: data.message.message
        });
      }
      else if (data.in_stock === false) {
        this.setState({
          message_type: 'error',
          message: Drupal.t('Sorry, one or more products in your basket are no longer available. Please review your basket in order to checkout securely.')
        });
      }
    }, false);
  };

  render() {
      if (this.state.wait) {
        return <Loading loadingMessage={Drupal.t('Loading your cart ...')}/>
      }

      return (
        <React.Fragment>
          <div className="spc-pre-content">
            <CheckoutMessage type={this.state.message_type}>
              {this.state.message}
            </CheckoutMessage>
            <MobileCartPreview total_items={this.state.total_items} totals={this.state.totals} />
          </div>
          <div className="spc-main">
            <div className="spc-content">
              <CheckoutSectionTitle>
                {Drupal.t('My Shopping Bag (@qty items)', {'@qty': this.state.total_items})}
              </CheckoutSectionTitle>
              <CartItems items={this.state.items} />
            </div>
            <div className="spc-sidebar">
              <CartPromoBlock coupon_code={this.state.coupon_code} />
              <OrderSummaryBlock totals={this.state.totals} in_stock={this.state.in_stock}/>
            </div>
          </div>
          <div className="spc-post-content">
            <CartRecommendedProducts recommended_products={this.state.recommended_products} />
          </div>
        </React.Fragment>
      );
  }

}
