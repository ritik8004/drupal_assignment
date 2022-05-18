import React from 'react';

class PaymentMethodTabby extends React.Component {
  componentDidMount = () => {
    this.tabbyCardInit();
  };

  componentDidUpdate = () => {
    this.tabbyCardInit();
  };

  tabbyCardInit = () => {
    const { tabby, cart } = this.props;
    Drupal.tabbyCardInit(`#${tabby.widgetInfo.id}`, cart.cart.totals.base_grand_total);
    // Initialize the promo popup for info icon.
    Drupal.tabbyPromoPopup(cart.cart.totals.base_grand_total);
  }

  render() {
    const { tabby } = this.props;
    return (
      <>
        <div className="tabby">
          <div id={tabby.widgetInfo.id} className={tabby.widgetInfo.class} />
        </div>
      </>
    );
  }
}

export default PaymentMethodTabby;
