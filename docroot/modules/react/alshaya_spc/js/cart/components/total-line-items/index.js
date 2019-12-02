import React from "react";
import TotalLineItem from "../total-line-item";
import VatText from '../../../utilities/vat-text';

class TotalLineItems extends React.Component {
  render() {
    return (
      <div className="totals">
        <TotalLineItem name="sub-total" title={Drupal.t('sub total')} value={this.props.totals.subtotal_incl_tax}/>
        <TotalLineItem tooltip={true} tooltipContent={Drupal.t('Discount applied')} name="discount-total" title={Drupal.t('discount')} value={this.props.totals.discount_amount}/>
        {/*To Be used later on Checkout Delivery pages.*/}
        <TotalLineItem name="delivery-total" title={Drupal.t('delivery')} value={Drupal.t('Free')}/>
        <div className="hero-total">
          <TotalLineItem name="grand-total" title={Drupal.t('order total')} value={this.props.totals.base_grand_total}/>
          <div className="delivery-vat">
            <span className="delivery-prefix">{Drupal.t('excluding delivery')}</span>
            <VatText />
          </div>
        </div>
      </div>
    );
  };
}

export default TotalLineItems;
