import React from 'react';
import PriceElement from '../../../utilities/special-price/PriceElement';

export default class MobileCartPreview extends React.Component {
  render() {
    const total_text = this.props.totals.free_delivery
      ? Drupal.t('Total')
      : Drupal.t('Total (excluding delivery)');

    return (
      <>
        <div className="spc-mobile-cart-preview">
          <span className="cart-quantity">{Drupal.t('@qty items', { '@qty': this.props.total_items })}</span>
          <span className="cart-text">{`${total_text} :`}</span>
          <span className="cart-value"><PriceElement amount={this.props.totals.base_grand_total} /></span>
        </div>
      </>
    );
  }
}
