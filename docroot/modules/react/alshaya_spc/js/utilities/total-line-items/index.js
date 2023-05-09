import React from 'react';
import TotalLineItem from '../total-line-item';
import VatText from '../vat-text';
import ConditionalView from '../../common/components/conditional-view';
import getStringMessage from '../strings';
import { getAmountWithCurrency, replaceCodTokens } from '../checkout_util';
import AuraCheckoutOrderSummary from '../../aura-loyalty/components/aura-checkout-rewards/components/aura-checkout-order-summary';
import isAuraEnabled from '../../../../js/utilities/helper';
import PostpayCart from '../../cart/components/postpay/postpay';
import Postpay from '../postpay';
import Advantagecard from '../advantagecard';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';
import Tabby from '../../../../js/tabby/utilities/tabby';
import TabbyWidget from '../../../../js/tabby/components';
import { isEgiftCardEnabled } from '../../../../js/utilities/util';
import EgiftCheckoutOrderSummary from '../../egift-card/components/egift-checkout-order-summary';
import { isAuraIntegrationEnabled } from '../../../../js/utilities/helloMemberHelper';
import Tamara from '../../../../js/tamara/utilities/tamara';

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

  componentWillUnmount = () => {
    document.removeEventListener('applyDynamicPromotions', this.applyDynamicPromotions, false);
  }

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
      applied_rules_with_discounts: cartPromo,
      shipping_free: freeShipping,
    } = event.detail.cart_labels;

    this.setState({ cartPromo, freeShipping });
  };

  /**
   * Get the content of discount tooltip.
   */
  discountToolTipContent = (cartPromo) => {
    const { totals, couponCode, hasExclusiveCoupon } = this.props;
    let promoData = `<div class="applied-discounts-title">${Drupal.t('Discount applied')}</div>`;

    // Add the coupon code with the discount title if exclusive coupon code applied on cart.
    if (hasExclusiveCoupon === true) {
      promoData += `<div class="applied-exclusive-couponcode">${couponCode}</div>`;
      return promoData;
    }

    // Change the discount title if hello member offer code exists on cart.
    if (hasValue(totals.hmOfferCode)) {
      promoData = `<div class="applied-hm-discounts-title">${Drupal.t('Member Discount', {}, { context: 'hello_member' })}</div>`;
    }

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
    if (Advantagecard.isAdvantagecardEnabled()) {
      // IF advantageCardApplied add promotion label of Advantage card in Discount Tool tip.
      if ((hasValue(totals.items) && Advantagecard.isAdvantageCardApplied(totals.items))
        || (hasValue(totals.advatage_card))) {
        promoData += `<div class="promotion-label"><strong>${Drupal.t('Advantage Card Discount')}</strong></div>`;
      }
    }
    return promoData;
  };

  render() {
    const {
      totals,
      isCartPage,
      context,
      collectionCharge,
    } = this.props;
    const { cartPromo, freeShipping } = this.state;
    const discountTooltip = this.discountToolTipContent(cartPromo);

    // Check for aura totals.
    let dontShowVatText = false;
    const { paidWithAura } = totals;

    if (paidWithAura > 0) {
      dontShowVatText = true;
    }

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

    let postpay;
    // We don't show postpay if tamara is enabled.
    if (Postpay.isPostpayEnabled() && !Tamara.isTamaraEnabled()) {
      postpay = (
        <PostpayCart
          amount={totals.base_grand_total}
          pageType={isCartPage ? 'cart' : ''}
          classNames="spc-postpay"
          mobileOnly={false}
        />
      );
    }

    // Check if hello member voucher codes are applied and set a flag to show
    // a voucher discount line items separately in summary.
    // - hmAppliedVoucherCodes is a comma separated string containing all
    // voucher codes.
    const showHmVoucherDiscount = (hasValue(totals.hmAppliedVoucherCodes)
      && totals.hmAppliedVoucherCodes !== '');

    return (
      <div className="totals">
        <TotalLineItem name="sub-total" title={Drupal.t('subtotal')} value={totals.subtotal_incl_tax} />
        <TotalLineItem tooltip tooltipContent={discountTooltip} name="discount-total" title={Drupal.t('Discount')} value={totals.discount_amount} />
        {showHmVoucherDiscount && (
          <TotalLineItem
            name="hm-voucher-discount"
            title={Drupal.t(
              '@count Bonus Voucher',
              { '@count': totals.hmAppliedVoucherCodes.split(',').length },
              { context: 'hello_member' },
            )}
            value={totals.hmVoucherDiscount}
          />
        )}

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

        {/* If Collection point feature is enabled, display collection charge if applied. */}
        <ConditionalView condition={collectionPointsEnabled() && hasValue(collectionCharge)}>
          <TotalLineItem
            name="collection-charge"
            title={Drupal.t('Collection Charge')}
            value={collectionCharge > 0 ? parseInt(collectionCharge, 10) : collectionCharge}
          />
        </ConditionalView>

        <ConditionalView condition={hasValue(totals.subtotalWithDiscountInclTax)}>
          <div className="hero-subtotal-after-discount">
            <TotalLineItem
              name="subtotal-after-discount-incl-tax"
              title={Drupal.t('Subtotal After Discount')}
              value={totals.subtotalWithDiscountInclTax}
            />
          </div>
        </ConditionalView>

        <div className="hero-total">
          <TotalLineItem name="grand-total" title={Drupal.t('Order Total')} value={baseGrandTotal} />
          <ConditionalView condition={isEgiftCardEnabled()}>
            <EgiftCheckoutOrderSummary
              totals={totals}
              context={context}
            />
          </ConditionalView>
          <div className="delivery-vat">
            <ConditionalView condition={shippingAmount === null}>
              <span className="delivery-prefix">{Drupal.t('Excluding delivery')}</span>
            </ConditionalView>

            <VatText />
          </div>
          {/* If aura or hm aura integration enabled we need to display aura checkout balance. */}
          <ConditionalView condition={isAuraEnabled() || (isAuraIntegrationEnabled() && context !== 'cart')}>
            <AuraCheckoutOrderSummary
              totals={totals}
              dontShowVatText={dontShowVatText}
              shippingAmount={shippingAmount}
              context={context}
            />
          </ConditionalView>
          {postpay}
          {/** We show tabby if tamara is enabled. */}
          <ConditionalView
            condition={isCartPage && Tabby.isTabbyEnabled() && Tabby.showTabbyWidget()}
          >
            <TabbyWidget
              pageType="cart"
              classNames="spc-tabby"
              id="tabby-promo-cart"
            />
          </ConditionalView>
        </div>
      </div>
    );
  }
}

export default TotalLineItems;
