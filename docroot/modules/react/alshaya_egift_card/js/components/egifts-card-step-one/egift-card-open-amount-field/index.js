import React from 'react';

export default class EgiftCardOpenAmountField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openAmountMessage: '',
    };
  }

  /**
   * Remove error message on focus.
   */
  handleErrorMessage() {
    this.setState({
      openAmountMessage: '',
    });
  }

  /**
   * Handle submit open amount.
   */
  handleSubmit = (e) => {
    e.preventDefault();

    // Remove any error message.
    document.getElementById('open-amount-error').innerHTML = '';

    // Set open amount.
    this.handleOpenAmount(e);
  }

  /**
   * Trigger submit when user clicks enter on open amount field.
   */
  handleKeypress = (e) => {
    // It triggers by pressing the enter key
    if (e.key === 'Enter') {
      this.handleSubmit(e);
    }
  };

  /**
   * Check if number is positive integer.
   */
  isAmount = (str) => {
    const n = Math.floor(Number(str));
    return n !== Infinity && String(n) === str && n >= 0;
  };

  /**
   * Validate open amount field on blur.
   */
  handleOpenAmount(e) {
    e.preventDefault();

    // Get open amount input element.
    const element = document.getElementById('open-amount');

    // Get open amount value.
    const openAmount = element.value;

    const { handleAmountSelect } = this.props;

    // Check if amount enter by user is whole number.
    if (this.isAmount(openAmount) === false) {
      document.getElementById('open-amount-error').innerHTML = Drupal.t('Please enter amount or select from above.', {}, { context: 'egift' });
      handleAmountSelect(false, 0);
      return;
    }

    // Min and Max value allowed for open amount.
    const amountFrom = parseFloat(element.getAttribute('data-amount-from'));
    const amountTo = parseFloat(element.getAttribute('data-amount-to'));

    // Compare if user input for open amount lies in the allowed range.
    if (parseFloat(openAmount) < amountFrom || parseFloat(openAmount) > amountTo) {
      document.getElementById('open-amount-error').innerHTML = Drupal.t('Please enter amount in the range of @amountFrom to @amountTo', {
        '@amountFrom': amountFrom,
        '@amountTo': amountTo,
      }, { context: 'egift' });
      handleAmountSelect(false, 0);
    } else {
      // If open amount is in range then select it for step 2.
      handleAmountSelect(true, openAmount);
    }
  }

  render() {
    const { selected } = this.props;
    const { openAmountMessage } = this.state;

    // Get all egift card attributes.
    const attributes = selected.custom_attributes;
    const eGiftCardAttributes = [];
    attributes.forEach((attribute) => {
      eGiftCardAttributes[attribute.attribute_code] = attribute;
    });

    // Don't show open amount field if allow_open_amount_hps attribute
    // from api response for card item is 0.
    if (eGiftCardAttributes.allow_open_amount_hps.value === '0') {
      return null;
    }

    return (
      <div className="egift-open-amount-wrapper">
        <span className="open-amount-currency">
          { drupalSettings.alshaya_spc.currency_config.currency_code }
        </span>
        <input
          id="open-amount"
          className="egift-open-amount-field"
          name="egift-open-amount"
          type="number"
          min={eGiftCardAttributes.amount_open_from_hps.value}
          max={eGiftCardAttributes.amount_open_to_hps.value}
          data-amount-from={eGiftCardAttributes.amount_open_from_hps.value}
          data-amount-to={eGiftCardAttributes.amount_open_to_hps.value}
          onBlur={(e) => this.handleOpenAmount(e)}
          onFocus={() => this.handleErrorMessage()}
          onKeyPress={this.handleKeypress}
        />
        <div className="error" id="open-amount-error">
          { openAmountMessage }
        </div>
        <button type="button" onClick={this.handleSubmit}>
          <span className="open-amount-submit">&nbsp;</span>
        </button>
      </div>
    );
  }
}
