import React from 'react';
import Price from "../../../utilities/price";

export default class MobileCartPreview extends React.Component {

  render() {
    var total_text = this.props.totals.free_delivery
      ? Drupal.t('Total')
      : Drupal.t('Total (excluding delivery)');

    return (
    <React.Fragment>
      <div className="spc-mobile-cart-preview">
        <span className="cart-quantity">{Drupal.t('@qty items', {'@qty': this.props.total_items})}</span>
        <span className="cart-text">{total_text + ' :'}</span>
        <span className="cart-value"><Price price={this.props.totals.base_grand_total} /></span>
      </div>
    </React.Fragment>
    );
  }

}
