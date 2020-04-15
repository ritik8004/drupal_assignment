import React from 'react';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import OrderSummary from './OrderSummary';
import VatFooterText from '../../utilities/vat-footer';
import isRTL from '../../utilities/rtl';

const CheckoutConfirmationPrint = React.forwardRef((props, ref) => {
  const { items, totals, number_of_items: itemsTotal } = drupalSettings.order_details;
  const { logo, customer_service_text: customerServiceText } = drupalSettings.site_details;
  const direction = isRTL() === true ? 'rtl' : 'ltr';

  return (
    <div ref={ref} className="spc-order-confirmation-wrapper" dir={direction}>
      <div className="spc-print-header">
        <img src={logo} />
        <span className="spc-checkout-confirmation-title">{Drupal.t('Order confirmation')}</span>
      </div>
      <div className="spc-pre-content">
        <div className="impress-msg">{Drupal.t('Thanks for shopping with us.')}</div>
        <div className="impress-subtitle">{Drupal.t('Here\'s a confirmation of your order and all the details you may need.')}</div>
      </div>
      <div className="spc-main">
        <div className="spc-content">
          <OrderSummary />
        </div>
        <div className="spc-sidebar">
          <OrderSummaryBlock
            item_qty={itemsTotal}
            items={items}
            totals={totals}
            cart_promo={[]}
            show_checkout_button={false}
          />
        </div>
      </div>
      <div className="spc-checkout-confirmation-footer">
        <VatFooterText />
        <div className="customer-service-text">
          <div className="title">{Drupal.t('CUSTOMER SERVICE')}</div>
          <div className="content" dangerouslySetInnerHTML={{ __html: customerServiceText.value }} />
        </div>
        <div className="logos">
          <img src="/themes/custom/transac/alshaya_white_label/imgs/cards/veri-sign-black.svg" />
          <img src="/themes/custom/transac/alshaya_white_label/imgs/cards/verifiedby-visa-black.svg" />
          <img src="/themes/custom/transac/alshaya_white_label/imgs/cards/master-card-secure-code-black.svg" />
        </div>
      </div>
    </div>
  );
});

export default CheckoutConfirmationPrint;
