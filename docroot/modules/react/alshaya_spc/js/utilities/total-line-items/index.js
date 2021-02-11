import React from 'react';
import TotalLineItem from '../total-line-item';
import VatText from '../vat-text';
import ConditionalView from '../../common/components/conditional-view';
import getStringMessage from '../strings';
import { getAmountWithCurrency, replaceCodTokens } from '../checkout_util';
import PostpayCart from '../../cart/components/cart-postpay';

class TotalLineItems extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      cartPromo: {},
      freeShipping: false,
    };
  }

  componentDidMount = () => {
    document.addEventListener('applyDynamicPromotions', this.applyDynamicPromotions, false);
  };

  applyDynamicPromotions = (event) => {
    // If promo contains no data, set to null.
    if (event.detail.cart_labels === null) {
      this.setState({
        cartPromo: {},
        freeShipping: false,
      });

      return;
    }

    const {
      applied_rules: cartPromo,
      shipping_free: freeShipping,
    } = event.detail.cart_labels;

    this.setState({ cartPromo, freeShipping });
  };

  /**
   * Get the content of discount tooltip.
   */
  discountToolTipContent = (cartPromo) => {
    let promoData = `<div class="applied-discounts-title">${Drupal.t('Discount applied')}</div>`;
    if (cartPromo !== null
      && cartPromo !== undefined
      && Object.keys(cartPromo).length > 0) {
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
    const { totals, isCartPage } = this.props;
    const { cartPromo, freeShipping } = this.state;
    const discountTooltip = this.discountToolTipContent(cartPromo);

    // Using a separate variable(shippingAmount) to update the value
    // not using the variable in props(totals) as it will
    // update the global value.
    let shippingAmount = (totals.shipping_incl_tax === undefined)
      ? null
      : totals.shipping_incl_tax;

    // Show "Delivery: FREE" on basket if cart promo rule applied for it.
    if (shippingAmount === null && freeShipping) {
      shippingAmount = 0;
    }

    // We don't show surcharge info in total on cart page.
    const baseGrandTotal = (isCartPage === false)
      ? totals.base_grand_total
      : totals.base_grand_total_without_surcharge;

    return (
      <div className="totals">
        <TotalLineItem name="sub-total" title={Drupal.t('subtotal')} value={totals.subtotal_incl_tax} />
        <TotalLineItem tooltip tooltipContent={discountTooltip} name="discount-total" title={Drupal.t('Discount')} value={totals.discount_amount} />

        <ConditionalView condition={shippingAmount !== null}>
          <TotalLineItem
            name="delivery-total"
            title={Drupal.t('Delivery')}
            value={shippingAmount > 0 ? shippingAmount : Drupal.t('FREE')}
          />
        </ConditionalView>

        {/* Show surcharge on checkout page only if available. */}
        <ConditionalView condition={totals.surcharge > 0 && isCartPage === false}>
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
          <TotalLineItem name="grand-total" title={Drupal.t('Order Total')} value={baseGrandTotal} />
          <div className="delivery-vat">
            <ConditionalView condition={shippingAmount === null}>
              <span className="delivery-prefix">{Drupal.t('Excluding delivery')}</span>
            </ConditionalView>

            <VatText />
          </div>
          <PostpayCart
            ref={this.PostpayCart}
            amount={totals.base_grand_total}
            isCartPage={isCartPage}
            classNames="spc-postpay"
            mobileOnly={false}
          />
        </div>
      </div>
    );
  }
}

export default TotalLineItems;
