import React from 'react';
import TotalLineItem from '../total-line-item';
import VatText from '../vat-text';
import FreeDeliveryText from '../free-delivery-text';
import ConditionalView from '../../common/components/conditional-view';
import getStringMessage from '../strings';

class TotalLineItems extends React.Component {
  /**
   * Get the content of discount tooltip.
   */
  discountToolTipContent = (cartPromo) => {
    let promoData = `<div class="applied-discounts-title">${Drupal.t('Discount applied')}</div>`;
    if (cartPromo.length > 0) {
      Object.entries(cartPromo).forEach(([, promo]) => {
        if (promo.label.length > 0) {
          promoData += `<div class="promotion-label"><strong>${promo.label}</strong></div>`;
        }

        if (promo.description.length > 0) {
          promoData += `<div class="promotion-description">${promo.description}</div><br/>`;
        }
      });
    }

    return promoData;
  }

  render() {
    const { cart_promo: cartPromo, totals } = this.props;
    const discountTooltip = this.discountToolTipContent(cartPromo);

    return (
      <div className="totals">
        <TotalLineItem name="sub-total" title={Drupal.t('subtotal')} value={totals.subtotal_incl_tax} />
        <TotalLineItem tooltip tooltipContent={discountTooltip} name="discount-total" title={Drupal.t('discount')} value={totals.discount_amount} />

        <ConditionalView condition={totals.shipping_incl_tax > 0}>
          <TotalLineItem
            name="surcharge-total"
            title={Drupal.t('Delivery')}
            value={totals.shipping_incl_tax}
          />
        </ConditionalView>

        <ConditionalView condition={totals.surcharge > 0}>
          <TotalLineItem
            tooltip
            name="surcharge-total"
            tooltipContent={getStringMessage('cod_surcharge_tooltip')}
            title={getStringMessage('cod_surcharge_label')}
            value={totals.surcharge}
          />
        </ConditionalView>

        <TotalLineItem tooltip tooltipContent={discountTooltip} name="discount-total" title={Drupal.t('Discount')} value={totals.discount_amount} />

        <div className="hero-total">
          <TotalLineItem name="grand-total" title={Drupal.t('Order Total')} value={totals.base_grand_total} />
          <div className="delivery-vat">
            <FreeDeliveryText freeDelivery={totals.free_delivery} text={Drupal.t('excluding delivery')} />
            <VatText />
          </div>
        </div>
      </div>
    );
  }
}

export default TotalLineItems;
