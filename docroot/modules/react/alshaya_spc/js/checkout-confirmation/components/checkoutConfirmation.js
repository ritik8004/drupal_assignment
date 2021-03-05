import React from 'react';
import Cookies from 'js-cookie';
import ReactToPrint from 'react-to-print';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import OrderSummary from './OrderSummary';
import { stickySidebar } from '../../utilities/stickyElements/stickyElements';
import { removeCartFromStorage, removeStorageInfo } from '../../utilities/storage';
import VatFooterText from '../../utilities/vat-footer';
import ConditionalView from '../../common/components/conditional-view';
import CheckoutConfirmationPrint from './checkoutConfirmationPrint';

class CheckoutConfirmation extends React.Component {
  constructor(props) {
    super(props);

    const currentUrl = window.location.href;
    const currentTitle = document.title;

    // Push home page in history state.
    window.history.pushState('', Drupal.t('Home'), Drupal.url(''));

    // Push current page url/title again.
    window.history.pushState('', currentTitle, currentUrl);

    window.addEventListener('popstate', () => {
      // Ensure user goes back to home page if tries to use back button.
      window.location.href = Drupal.url('');
    });

    try {
      if (Cookies.get('middleware_order_placed')) {
        removeCartFromStorage();
        removeStorageInfo('shippingaddress-formdata');
        Cookies.remove('middleware_order_placed');
      } else if (drupalSettings.order_details.order_number) {
        removeStorageInfo('shippingaddress-formdata');
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
        <div className="spc-pre-content fadeInUp" style={{ animationDelay: '0.4s' }}>
          <div className="impress-msg">{Drupal.t('Thanks for shopping with us.')}</div>
          <div className="impress-subtitle">{Drupal.t('Here\'s a confirmation of your order and all the details you may need.')}</div>
          <ReactToPrint
            trigger={() => <div className="spc-checkout-confirmation-print-button">{Drupal.t('print confirmation')}</div>}
            content={() => this.componentRef}
          />
        </div>
        <div className="spc-main">
          <div className="spc-content">
            <OrderSummary />
            <ConditionalView condition={window.innerWidth > 768}>
              <div className="checkout-link submit fadeInUp" style={{ animationDelay: '1s' }}>
                <a href={Drupal.url('')} className="checkout-link">
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
              animationDelay="0.4s"
              context="confirmation"
            />
          </div>
        </div>
        <div className="spc-post-content" />
        <ConditionalView condition={window.innerWidth < 768}>
          <div className="checkout-link submit">
            <a href={Drupal.url('')} className="checkout-link">
              {Drupal.t('continue shopping')}
            </a>
          </div>
        </ConditionalView>
        <div className="spc-footer">
          <div style={{ display: 'none' }} className="spc-checkout-confirmation-print"><CheckoutConfirmationPrint ref={(el) => { this.componentRef = el; }} /></div>
          <VatFooterText />
        </div>
      </>
    );
  }
}

export default CheckoutConfirmation;
