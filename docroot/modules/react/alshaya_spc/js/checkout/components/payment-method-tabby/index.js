import React from 'react';
import { getPayable } from '../../../utilities/checkout_util';

class PaymentMethodTabby extends React.Component {
  componentDidMount = () => {
    this.tabbyCardInit();
  };

  componentDidUpdate = () => {
    this.tabbyCardInit();
  };

  tabbyCardInit = () => {
    const { tabby, cart } = this.props;
    const amount = getPayable(cart);
    Drupal.tabbyCardInit(`#${tabby.widgetInfo.id}`, amount);
    Drupal.tabbyPromoPopup(amount);
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
