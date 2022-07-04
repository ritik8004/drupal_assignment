import React from 'react';

export default class EgiftCardOpenAmountField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openAmountMessage: '',
      openAmountInputDisabled: false, // Enable field by default.
    };
  }

  componentDidMount() {
    const { field } = this.props;
    if (field.current !== null) {
      field.current.querySelector('button').disabled = true;
    }
  }

  /**
   * Remove error message on focus.
   */
  handleErrorMessage = () => {
    // Reset error message to empty.
    document.getElementById('open-amount-error').innerHTML = '';
  }

  handleChange = () => {
    const openAmount = document.getElementById('open-amount').value;
    if (openAmount !== '') {
      // Remove any error message.
      document.getElementById('open-amount-error').innerHTML = '';
    }
  }

  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
  };

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
    // Enable action button.
    const { field } = this.props;
    if (field.current !== null) {
      field.current.querySelector('button').disabled = false;
    }

    // It triggers by pressing the enter key
    if (e.key === 'Enter') {
      this.handleSubmit(e);
    }
  };

  /**
   * Check if number is positive integer.
   */
  isAmount = (str) => {
    const n = Number(str);
    // If not valid number like string or anything.
    if (Number.isNaN(n)) {
      return false;
    }

    // Checks if number greater than 0 and integer.
    return n > 0 && Number.isInteger(n);
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
    const amountFrom = parseFloat(element.getAttribute('min'));
    const amountTo = parseFloat(element.getAttribute('max'));

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

      // Unset active from any item amounts.
      const amountElements = document.querySelectorAll('.item-amount');
      [].forEach.call(amountElements, (el) => {
        el.classList.remove('active');
      });

      // Lock open amount input field.
      element.readOnly = true;
    }
  }

  /**
   * Block user from pasting e (exponential) into open amount field.
   */
  handlePaste = (e) => {
    // Get data from clipboard.
    const clipboardData = e.clipboardData || window.clipboardData;
    const pastedData = clipboardData.getData('Text').toUpperCase();

    // Prevent user from pasting e (exponential) in input field.
    if (pastedData.indexOf('E') > -1) {
      e.stopPropagation();
      e.preventDefault();
    }
  }

  /**
   * Disallow user from entering e, E, +, -, ., in open amount field.
   */
  handleUserInput = (e) => (e.key === 'e'
    || e.key === 'E'
    || e.key === '+'
    || e.key === '-'
    || e.key === '.')
    && e.preventDefault();

  render() {
    const { selected, field } = this.props;
    const { openAmountMessage, openAmountInputDisabled } = this.state;

    // Get all egift card attributes.
    const attributes = selected.custom_attributes;
    const eGiftCardAttributes = [];
    attributes.forEach((attribute) => {
      eGiftCardAttributes[attribute.attribute_code] = attribute;
    });

    // Don't show open amount field if allow_open_amount_hps attribute
    // from api response for card item is 0,
    // or if allow_open_amount_hps is set 1 and any of amount_open_from_hps or amount_open_to_hps,
    // is not set.
    if (eGiftCardAttributes.allow_open_amount_hps.value === '0'
      || typeof eGiftCardAttributes.amount_open_from_hps === 'undefined'
      || typeof eGiftCardAttributes.amount_open_to_hps === 'undefined') {
      return null;
    }

    return (
      <div className="egift-open-amount-wrapper" ref={field}>
        <div className="egift-input-textfield-item">
          <input
            id="open-amount"
            className="egift-open-amount-field"
            name="egift-open-amount"
            type="number"
            min={eGiftCardAttributes.amount_open_from_hps.value}
            max={eGiftCardAttributes.amount_open_to_hps.value}
            onFocus={() => this.handleErrorMessage()}
            onKeyPress={this.handleKeypress}
            onKeyDown={(e) => this.handleUserInput(e)}
            onPaste={(e) => this.handlePaste(e)}
            onChange={this.handleChange}
            readOnly={openAmountInputDisabled}
            onBlur={(e) => this.handleEvent(e)}
          />
          <label>{Drupal.t('Enter amount', {}, { context: 'egift' })}</label>
          <span className="open-amount-currency">
            { drupalSettings.alshaya_spc.currency_config.currency_code }
          </span>
        </div>
        <button className="open-amount-btn" type="button" onClick={this.handleSubmit} />
        <div className="error egift-error" id="open-amount-error">
          { openAmountMessage }
        </div>
      </div>
    );
  }
}
