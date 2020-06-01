import React from 'react';
import TotalLineItem from '../total-line-item';
import VatText from '../vat-text';
import ConditionalView from '../../common/components/conditional-view';
import getStringMessage from '../strings';
import { getAmountWithCurrency, replaceCodTokens } from '../checkout_util';

class TotalLineItems extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      cartPromo: [],
    };
  }

  componentDidMount = () => {
    document.addEventListener('applyDynamicPromotions', this.saveDynamicPromotions, false);
  };

  applyDynamicPromotions = (event) => {
    const {
      applied_rules: cartPromo,
    } = event.detail;

    this.setState({ cartPromo });
  };

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
  };

  render() {
    const { totals } = this.props;
    const { cartPromo } = this.state;
    const discountTooltip = this.discountToolTipContent(cartPromo);

    return (
      <div className="totals">
        <TotalLineItem name="sub-total" title={Drupal.t('subtotal')} value={totals.subtotal_incl_tax} />
        <TotalLineItem tooltip tooltipContent={discountTooltip} name="discount-total" title={Drupal.t('Discount')} value={totals.discount_amount} />

        <ConditionalView condition={totals.shipping_incl_tax !== null}>
          <TotalLineItem
            name="delivery-total"
            title={Drupal.t('Delivery')}
            value={totals.shipping_incl_tax > 0 ? totals.shipping_incl_tax : Drupal.t('FREE')}
          />
        </ConditionalView>

        <ConditionalView condition={totals.surcharge > 0}>
          <TotalLineItem
            tooltip
            name="surcharge-total"
            tooltipContent={replaceCodTokens(
              getAmountWithCurrency(totals.surcharge),
              getStringMessage('cod_surcharge_tooltip'),
            )}
            title={getStringMessage('cod_surcharge_label')}
            value={totals.surcharge}
          />
        </ConditionalView>

        <div className="hero-total">
          <TotalLineItem name="grand-total" title={Drupal.t('Order Total')} value={totals.base_grand_total} />
          <div className="delivery-vat">
            <ConditionalView condition={totals.shipping_incl_tax === null}>
              <span className="delivery-prefix">{Drupal.t('Excluding delivery')}</span>
            </ConditionalView>

            <VatText />
          </div>
        </div>
      </div>
    );
  }
}

export default TotalLineItems;
