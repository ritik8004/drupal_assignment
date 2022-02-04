import React from 'react';
import parse from 'html-react-parser';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import OrderSummary from './OrderSummary';
import VatFooterText from '../../utilities/vat-footer';
import isRTL from '../../utilities/rtl';
import ConditionalView from '../../common/components/conditional-view';
import CompleteBenefitPayPayment
  from './CompleteBenefitPayPayment';
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const CheckoutConfirmationPrint = React.forwardRef((props, ref) => {
  const {
    items,
    totals,
    number_of_items: itemsTotal,
    payment,
    delivery_type_info: {
      collection_charge: collectionCharge,
    },
  } = drupalSettings.order_details;
  const {
    logo,
    customer_service_text: customerServiceText,
    sub_brand_logo: subBrandLogo,
  } = drupalSettings.site_details;
  const direction = isRTL() === true ? 'rtl' : 'ltr';
  let subBrandLogoMarkup = '';

  // Check for sub brand logo.
  if (subBrandLogo.sub_brand_logo_img !== undefined
    && subBrandLogo.sub_brand_logo_link !== undefined) {
    const { sub_brand_logo_img: brandLogo } = subBrandLogo;
    const pngLogo = brandLogo.replace('svg', 'png');
    subBrandLogoMarkup = <img loading="lazy" className="sub-brand-logo" src={pngLogo} />;
  }

  return (
    <div ref={ref} className="spc-order-confirmation-wrapper" dir={direction}>
      <div className="spc-print-header">
        <div className="spc-print-header--logo">
          <img loading="lazy" src={logo.logo_url} />
          { subBrandLogoMarkup }
        </div>
        <span className="spc-checkout-confirmation-title">{Drupal.t('Order confirmation')}</span>
      </div>
      <div className="spc-pre-content">
        <div className="impress-msg">{Drupal.t('Thanks for shopping with us.')}</div>
        <div className="impress-subtitle">{Drupal.t('Here\'s a confirmation of your order and all the details you may need.')}</div>
      </div>
      <div className="spc-main">
        <div className="spc-content">
          <OrderSummary context="print" />
          <ConditionalView condition={payment.methodCode === 'checkout_com_upapi_benefitpay'}>
            <CompleteBenefitPayPayment payment={payment} totals={totals} />
          </ConditionalView>
        </div>
        <div className="spc-sidebar">
          <OrderSummaryBlock
            item_qty={itemsTotal}
            items={items}
            totals={totals}
            cart_promo={[]}
            show_checkout_button={false}
            context="print"
            {...(collectionPointsEnabled()
              && hasValue(collectionCharge)
              && { collectionCharge }
            )}
          />
        </div>
      </div>
      <div className="spc-checkout-confirmation-footer">
        <VatFooterText />
        <div className="customer-service-text">
          <div className="title">{Drupal.t('CUSTOMER SERVICE')}</div>
          <div className="content">{ parse(customerServiceText.value) }</div>
        </div>
        <div className="logos">
          <img loading="lazy" src="/themes/custom/transac/alshaya_white_label/imgs/cards/veri-sign-black.svg" />
          <img loading="lazy" src="/themes/custom/transac/alshaya_white_label/imgs/cards/verifiedby-visa-black.svg" />
          <img loading="lazy" src="/themes/custom/transac/alshaya_white_label/imgs/cards/master-card-secure-code-black.svg" />
        </div>
      </div>
    </div>
  );
});

export default CheckoutConfirmationPrint;
