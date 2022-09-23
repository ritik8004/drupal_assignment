import React from 'react';

class TamaraWidget extends React.Component {
  componentDidMount = () => {
    this.tamaraInitWidgets();
  };

  componentDidUpdate = () => {
    this.tamaraInitWidgets();
  };

  // Initialise the Tamara info or installment widget once component loaded.
  tamaraInitWidgets = () => {
    const { context } = this.props;

    // If context is info, initialise the info widget.
    if (context === 'info') {
      Drupal.tamaraInitInfoWidget();
      return;
    }

    // If context is not info then render the installment widget as we only have
    // two context for now 'info' and 'installment'
    Drupal.tamaraInitInstallmentWidget();
  }

  render() {
    const {
      context, amount,
    } = this.props;

    const { tamara } = window.drupalSettings;

    switch (context) {
      case 'info':
        return (
          <div
            className="tamara-widget"
            data-lang={drupalSettings.path.currentLanguage}
            data-country-code={drupalSettings.country_code}
            data-payment-type="installment"
            data-inject-template={false}
            data-installment-available-amount={amount}
          >
            <button
              className="payment-method-tamara__info"
              type="button"
              onClick={(e) => e.preventDefault()}
            />
          </div>
        );

      case 'installment':
        return (
          <div className="tamara">
            <div
              className="tamara-installment-plan-widget"
              data-lang={drupalSettings.path.currentLanguage}
              data-price={amount}
              data-currency={drupalSettings.alshaya_spc.currency_config.currency_code}
              data-number-of-installments={tamara.installmentCount}
            />
          </div>
        );
      default:
        return null;
    }
  }
}

export default TamaraWidget;
