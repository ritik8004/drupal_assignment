import React from 'react';

import Price from '../../../utilities/price';
import VatText from '../../../utilities/vat-text';

export default class CartTotalSubTotal extends React.Component {

  render() {
    const in_stock = this.props.in_stock;
    const vat_text = window.drupalSettings.alshaya_spc.vat_text;
    const discount = this.props.totals.discount_amount;

    return (
      <div>
        <h2>{Drupal.t('order summary')}</h2>
        <div>
          <span>{Drupal.t('Sub Total')}</span>
          <span><Price price={this.props.totals.subtotal_incl_tax} /></span>
        </div>
        <div>
          <span>{Drupal.t('Order Total')}</span>
          <span><Price price={this.props.totals.base_grand_total} /></span>
          <VatText vat_text={vat_text} />
        </div>
        <div>
          <span>{Drupal.t('Continue to Checkout')}</span>
        </div>
      </div>
    );
  }

}