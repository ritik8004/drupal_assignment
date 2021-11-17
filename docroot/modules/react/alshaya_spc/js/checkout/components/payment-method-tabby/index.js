import React from 'react';

class PaymentMethodTabby extends React.Component {
  componentDidMount = () => {
    this.tabbyCardInit();
  };

  componentDidUpdate = () => {
    this.tabbyCardInit();
  };

  tabbyCardInit = () => {
    const { widgetInfo, cart } = this.props;
    Drupal.tabbyCardInit(`#${widgetInfo.id}`, cart.cart.totals.base_grand_total);
  }

  render() {
    const { widgetInfo } = this.props;
    return (
      <>
        <div className="tabby">
          <div id={widgetInfo.id} className={widgetInfo.class} />
        </div>
      </>
    );
  }
}

export default PaymentMethodTabby;
