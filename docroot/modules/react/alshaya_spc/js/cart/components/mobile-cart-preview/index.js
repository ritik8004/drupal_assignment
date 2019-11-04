import React from 'react';
import Price from "../../../utilities/price";

export default class MobileCartPreview extends React.Component {

  render() {
    return (
    <React.Fragment>
      <div className="spc-mobile-cart-preview">
        <span className="cart-quantity">{Drupal.t('@qty items', {'@qty': this.props.total_items})}</span>
        <span className="cart-text">{Drupal.t('Total (excluding delivery):')}</span>
        <span className="cart-value"><Price price={this.props.totals.base_grand_total} /></span>
      </div>
    </React.Fragment>
    );
  }

}
