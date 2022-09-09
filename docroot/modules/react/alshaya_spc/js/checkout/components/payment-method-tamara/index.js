import React from 'react';
import { getPayable } from '../../../utilities/checkout_util';

class PaymentMethodTamara extends React.Component {
  componentDidMount = () => {
    this.tamaraCardInit();
  };

  componentDidUpdate = () => {
    this.tamaraCardInit();
  };

  // Initialise the Tamara installment widget once component loaded.
  tamaraCardInit = () => {
    Drupal.tamaraCardInit();
  }

  render() {
    const { tamara, cart } = this.props;
    const amount = getPayable(cart);
    return (
      <>
        <div className="tamara">
          <div
            id={tamara.widgetInfo.id}
            className={tamara.widgetInfo.class}
            data-lang={drupalSettings.path.currentLanguage}
            data-price={amount}
            data-currency={drupalSettings.alshaya_spc.currency_config.currency_code}
            data-number-of-installments={tamara.installmentCount}
          />
        </div>
      </>
    );
  }
}

export default PaymentMethodTamara;
