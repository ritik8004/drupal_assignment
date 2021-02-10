import React from 'react';

class PaymentMethodPostpay extends React.Component {
  componentDidMount = () => {
    window.postpay.ui.refresh();
  };

  render() {
    const { postpay, postpayWidgetInfo, cart } = this.props;
    return (
      <>
        <div
          className={postpayWidgetInfo.class}
          data-type={postpayWidgetInfo['data-type']}
          data-amount={cart.cart.totals.base_grand_total * postpay.currency_multiplier}
          data-currency={postpayWidgetInfo['data-currency']}
          data-num-instalments={postpayWidgetInfo['data-num-instalments']}
          data-locale={postpayWidgetInfo['data-locale']}
        />
      </>
    );
  }
}

export default PaymentMethodPostpay;
