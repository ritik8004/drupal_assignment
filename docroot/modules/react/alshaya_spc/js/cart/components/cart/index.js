import React from 'react';

import EmptyCart from '../empty-cart';
import CheckoutSectionTitle from '../spc-checkout-section-title';
import CartTotalSubTotal from '../cart-total-subtotal';
import CartErrorMessage from '../spc-checkout-error-message';
import CartRecommendedProducts from '../recommended-products';
import MobileCartPreview from '../mobile-cart-preview';

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
      'in_stock': true
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      const data = e.detail.data();
      var in_stock = true;

      Object.keys(data.items).forEach(function(key) {
        if (data.items[key].stock === 0) {
          in_stock = false;
        }
      })

      this.setState(state => ({
        items: data.items,
        totals: data.totals,
        recommended_products: data.recommended_products,
        total_items: data.items_qty,
        amount: data.cart_total,
        wait: false,
        in_stock: in_stock
      }));
    }, false);
  };

  render() {
      if (this.state.wait) {
        return <EmptyCart></EmptyCart>
      }

      return (
        <React.Fragment>
          <div className="spc-pre-content">
            <CartErrorMessage in_stock={this.state.in_stock}>
              {Drupal.t('Sorry, one or more products in your basket are no longer available. Please review your basket in order to checkout securely.')}
            </CartErrorMessage>
            <MobileCartPreview total_items={this.state.total_items} totals={this.state.totals} />
          </div>
          <div className="spc-main">
            <div className="spc-content">
              <CheckoutSectionTitle>
                {Drupal.t('My Shopping Bag (@qty items)', {'@qty': this.state.total_items})}
              </CheckoutSectionTitle>
            </div>
            <div className="spc-sidebar">
              <CartTotalSubTotal totals={this.state.totals} in_stock={this.state.in_stock}></CartTotalSubTotal>
            </div>
          </div>
          <div className="spc-post-content">
            <CartRecommendedProducts recommended_products={this.state.recommended_products} />
          </div>
        </React.Fragment>
      );
  }

}
