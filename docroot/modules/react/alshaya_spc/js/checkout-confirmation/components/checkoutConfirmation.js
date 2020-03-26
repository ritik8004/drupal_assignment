import React from 'react';
import Cookies from 'js-cookie';
import ReactToPrint from 'react-to-print';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import OrderSummary from './OrderSummary';
import { stickySidebar } from '../../utilities/stickyElements/stickyElements';
import { removeCartFromStorage } from '../../utilities/storage';
import VatFooterText from '../../utilities/vat-footer';
import ConditionalView from '../../common/components/conditional-view';
import CheckoutConfirmationPrint from './checkoutConfirmationPrint';

class CheckoutConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.printComponentRef = React.createRef();

    try {
      if (Cookies.get('middleware_order_placed')) {
        removeCartFromStorage();
        Cookies.remove('middleware_order_placed');
      }
    } catch (e) {
      window.location = Drupal.url('cart');
    }
  }

  componentDidMount() {
    // Make sidebar sticky.
    stickySidebar();
  }

  render() {
    const { items, totals, number_of_items: itemsTotal } = drupalSettings.order_details;

    return (
      <>
        <div className="spc-pre-content">
          <div className="impress-msg">{Drupal.t('Thanks for shopping with us.')}</div>
          <div className="impress-subtitle">{Drupal.t('Here\'s a confirmation of your order and all the details you may need.')}</div>
          <ReactToPrint
            trigger={() => <div className="spc-checkout-confirmation-print-button">{Drupal.t('Print Confirmation')}</div>}
            content={() => this.componentRef}
            copyStyles
          />
        </div>
        <div className="spc-main">
          <div className="spc-content">
            <OrderSummary />
            <VatFooterText />
            <ConditionalView condition={window.innerWidth > 768}>
              <div className="checkout-link submit">
                <a href="/" className="checkout-link">
                  {Drupal.t('continue shopping')}
                </a>
              </div>
            </ConditionalView>
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
        <div className="spc-post-content" />
        <ConditionalView condition={window.innerWidth < 768}>
          <div className="checkout-link submit">
            <a href={Drupal.url('<front>')} className="checkout-link">
              {Drupal.t('continue shopping')}
            </a>
          </div>
        </ConditionalView>
        <div style={{ display: 'none' }} className="spc-checkout-confirmation-print"><CheckoutConfirmationPrint ref={(el) => { this.componentRef = el; }} /></div>
      </>
    );
  }
}

export default CheckoutConfirmation;
