import React from 'react';
import SectionTitle from '../section-title';
import TotalLineItems from '../total-line-items';
import CheckoutCartItems from '../../checkout/components/checkout-cart-items';

class OrderSummaryBlock extends React.Component {
  render() {
    const promoData = this.props.cart_promo ? this.props.cart_promo : null;
    let orderSummaryTitle = Drupal.t('Order Summary');
    const continueCheckoutLink = (window.drupalSettings.user.uid === 0) ? 'cart/login' : 'checkout';
    // To be used on checkout page.
    if (this.props.item_qty !== undefined) {
      orderSummaryTitle = Drupal.t('order summary (@qty items)', { '@qty': this.props.item_qty });
    }

    return (
      <div className="spc-order-summary-block">
        <SectionTitle>{orderSummaryTitle}</SectionTitle>
        {/* To Be used on Checkout Delivery pages. */}
        {!this.props.show_checkout_button
          && (
          <div className={`product-content product-count-${Object.keys(this.props.items).length}`}>
            <CheckoutCartItems items={this.props.items} />
          </div>
          )}
        <div className="block-content">
          {/* To Be used later on Checkout Delivery pages. */}
          <div className="products" />
          <TotalLineItems totals={this.props.totals} cart_promo={promoData} />
          {/* To Be used on cart page only. */}
          {this.props.show_checkout_button
          && (
          <div className="actions">
            <div className="checkout-link submit">
              <a href={Drupal.url(continueCheckoutLink)} className="checkout-link">{Drupal.t('continue to checkout')}</a>
            </div>
          </div>
          )}
        </div>
      </div>
    );
  }
}

export default OrderSummaryBlock;
