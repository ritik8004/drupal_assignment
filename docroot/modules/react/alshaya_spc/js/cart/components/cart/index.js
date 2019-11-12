import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import CartItems from '../cart-items';
import CartRecommendedProducts from '../recommended-products';
import MobileCartPreview from '../mobile-cart-preview';
import OrderSummaryBlock from "../../../utilities/order-summary-block";
import CheckoutMessage from '../../../utilities/checkout-message';
import CartPromoBlock from "../cart-promo-block";
import EmptyResult from "../../../utilities/empty-result";
import Loading from "../../../utilities/loading";
import VatFooterText from "../../../utilities/vat-footer";

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
      'cart_promo': null,
      'in_stock': true
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      const data = e.detail.data();

      if (typeof data === 'undefined' || data.cart_id === null) {
        const prevState = this.state;
        this.setState({ ...prevState, wait: false});
      }
      else {
        this.setState(state => ({
          items: data.items,
          totals: data.totals,
          recommended_products: data.recommended_products,
          total_items: data.items_qty,
          amount: data.cart_total,
          cart_promo: data.cart_promo,
          wait: false,
          coupon_code: data.coupon_code,
          in_stock: data.in_stock
        }));

        // The cart is empty.
        if (data.items.length === 0) {
          this.setState({
            wait: false
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
        return <Loading loadingMessage={Drupal.t('loading your cart.')}/>
      }

      if (!this.state.wait && this.state.items.length === 0) {
        return (
          <React.Fragment>
            <EmptyResult Message={Drupal.t('your shopping basket is empty.')}/>
          </React.Fragment>
        );
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
              <SectionTitle>
                {Drupal.t('my shopping bag (@qty items)', {'@qty': this.state.total_items})}
              </SectionTitle>
              <CartItems items={this.state.items} />
              <VatFooterText />
            </div>
            <div className="spc-sidebar">
              <CartPromoBlock coupon_code={this.state.coupon_code} />
              <OrderSummaryBlock totals={this.state.totals} in_stock={this.state.in_stock} cart_promo={this.state.cart_promo} show_checkout_button={true} />
            </div>
          </div>
          <div className="spc-post-content">
            <CartRecommendedProducts recommended_products={this.state.recommended_products} />
          </div>
        </React.Fragment>
      );
  }

}
