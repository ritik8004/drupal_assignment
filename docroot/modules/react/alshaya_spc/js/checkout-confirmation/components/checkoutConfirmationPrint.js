import React from 'react';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import OrderSummary from './OrderSummary';
import VatFooterText from '../../utilities/vat-footer';

const CheckoutConfirmationPrint = React.forwardRef((ref) => (
  <div ref={ref} className="spc-order-confirmation-wrapper">
    <div className="spc-print-header">
      <img src={drupalSettings.site_details.logo} />
      <span className="spc-checkout-confirmation-title">{Drupal.t('Order Confirmation')}</span>
    </div>
    <div className="spc-pre-content">
      <div className="impress-msg">{Drupal.t('Thanks for shopping with us.')}</div>
      <div className="impress-subtitle">{Drupal.t('Here\'s a confirmation of your order and all the details you may need.')}</div>
    </div>
    <div className="spc-main">
      <div className="spc-content">
        <OrderSummary />
        <VatFooterText />
      </div>
      <div className="spc-sidebar">
        <OrderSummaryBlock
          item_qty={drupalSettings.order_details.number_of_items}
          items={drupalSettings.order_details.items}
          totals={drupalSettings.order_details.totals}
          cart_promo={[]}
          show_checkout_button={false}
        />
      </div>
    </div>
    <div className="spc-checkout-confirmation-footer">
      <div className="customer-service-text">
        <div className="title">{Drupal.t('customer service')}</div>
        <div className="content" dangerouslySetInnerHTML={{ __html: drupalSettings.site_details.customer_service_text.value }} />
      </div>
      <div className="logos">
        <img src="/themes/custom/transac/alshaya_white_label/imgs/cards/veri-sign-black.svg" />
        <img src="/themes/custom/transac/alshaya_white_label/imgs/cards/verifiedby-visa-black.svg" />
        <img src="/themes/custom/transac/alshaya_white_label/imgs/cards/master-card-secure-code-black.svg" />
      </div>
    </div>
  </div>
));

export default CheckoutConfirmationPrint;
