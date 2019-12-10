import React from "react";
import TotalLineItem from "../total-line-item";
import VatText from '../../../utilities/vat-text';
import FreeDeliveryText from '../../../utilities/free-delivery-text';

class TotalLineItems extends React.Component {

  /**
   * Get the content of discount tooltip.
   */
  discountToolTipContent = (cart_promo) => {
    var promo_data = '<div class="applied-discounts-title">' + Drupal.t('Discount applied') + '</div>';
    if (cart_promo.length > 0) {
      Object.entries(cart_promo).forEach(([key, promo]) => {
        if (promo.label.length > 0) {
          promo_data += '<div class="promotion-label"><strong>' + promo.label + '</strong></div>';
        }

        if (promo.description.length > 0) {
          promo_data += '<div class="promotion-description">' + promo.description + '</div><br/>';
        }
      });
    }

    return promo_data;
  }

  render() {
    const discount_tooltip = this.discountToolTipContent(this.props.cart_promo);

    return (
      <div className="totals">
        <TotalLineItem name="sub-total" title={Drupal.t('Subtotal')} value={this.props.totals.subtotal_incl_tax}/>
        <TotalLineItem tooltip={true} tooltipContent={discount_tooltip} name="discount-total" title={Drupal.t('Discount')} value={this.props.totals.discount_amount}/>
        <div className="hero-total">
          <TotalLineItem name="grand-total" title={Drupal.t('Order Total')} value={this.props.totals.base_grand_total}/>
          <div className="delivery-vat">
            <FreeDeliveryText freeDelivery={this.props.totals.free_delivery} text={Drupal.t('excluding delivery')} />
            <VatText />
          </div>
        </div>
      </div>
    );
  };
}

export default TotalLineItems;
