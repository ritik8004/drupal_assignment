import React from 'react';
import { isInDesiredForm } from '../../../utilities';

export default class EgiftCardOpenAmountField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openAmountMessage: '',
    };
  }

  handleErrorMessage() {
    this.setState({
      openAmountMessage: '',
    });
  }

  handleOpenAmount(e) {
    const element = e.target;
    const openAmount = e.target.value;
    if (isInDesiredForm(openAmount) === false) {
      this.setState({
        openAmountMessage: Drupal.t('Please enter amount.'),
      });
      return;
    }
    const amountFrom = parseFloat(element.getAttribute('data-amount-from'));
    const amountTo = parseFloat(element.getAttribute('data-amount-to'));

    if (parseFloat(openAmount) < amountFrom || parseFloat(openAmount) > amountTo) {
      this.setState({
        openAmountMessage: Drupal.t('Please enter amount in the range of @amountFrom to @amountTo', {
          '@amountFrom': amountFrom,
          '@amountTo': amountTo,
        }),
      });
    }
  }

  render() {
    const { selected } = this.props;
    const { openAmountMessage } = this.state;
    const attributes = selected.custom_attributes;
    const eGiftCardAttributes = [];
    attributes.forEach((attribute) => {
      eGiftCardAttributes[attribute.attribute_code] = attribute;
    });

    if (eGiftCardAttributes.allow_open_amount_hps.value === '0') {
      return null;
    }

    return (
      <div className="egift-open-amount-wrapper">
        <span className="open-amount-currency">
          { drupalSettings.alshaya_spc.currency_config.currency_code }
        </span>
        <input
          className="egift-open-amount-field"
          name="egift-open-amount"
          type="number"
          min={eGiftCardAttributes.amount_open_from_hps.value}
          max={eGiftCardAttributes.amount_open_to_hps.value}
          data-amount-from={eGiftCardAttributes.amount_open_from_hps.value}
          data-amount-to={eGiftCardAttributes.amount_open_to_hps.value}
          onBlur={(e) => this.handleOpenAmount(e)}
          onFocus={this.handleErrorMessage}
        />
        <div className="display-error">
          <span className="error-message" ref={this.myRef}>
            { openAmountMessage }
          </span>
        </div>
        <button type="button">
          <span className="open-amount-submit">&nbsp;</span>
        </button>
      </div>
    );
  }
}
