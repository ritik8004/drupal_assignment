import React from 'react';
import Cleave from 'cleave.js/react';
import ConditionalView from '../../../../common/components/conditional-view';
import luhn from '../../../../utilities/luhn';
import CardTypeSVG from '../../../../svg-component/card-type-svg';
import ToolTip from '../../../../utilities/tooltip';
import CVVToolTipText from '../../cvv-text';
import getStringMessage from '../../../../utilities/strings';
import { handleValidationMessage } from '../../../../utilities/form_item_helper';
import { CheckoutComUpapiContext } from '../../../../context/CheckoutComUpapi';
import { getBinValidationConfig, binValidation } from '../../../../utilities/checkout_util';
import { allowSavedCcForTopUp } from '../../../../utilities/egift_util';

class NewCard extends React.Component {
  static contextType = CheckoutComUpapiContext;

  constructor(props) {
    super(props);

    this.ccExpiry = React.createRef();
    this.ccCvv = React.createRef();

    const date = new Date();
    this.dateMin = `${date.getMonth() + 1}-${date.getFullYear().toString().substr(-2)}`;
    this.acceptedCards = drupalSettings.checkoutComUpapi.acceptedCards;
  }

  updateCurrentContext = (obj) => {
    const { updateState } = this.context;
    updateState(obj);
  };

  showCardType = () => {
    let type = document.getElementById('payment-card-type').value;

    // Also add support for show MasterCard Active for Maestro family.
    if (type === 'maestro') {
      type = 'mastercard';
    }

    this.updateCurrentContext({
      cardType: type,
    });
  };

  handleCardNumberChange = (event, handler) => {
    const { labelEffect } = this.props;
    const cardNumber = event.target.rawValue;

    labelEffect(event, handler);

    // Validate bin if bin validation is enabled else just validate card number.
    const { cardBinValidationEnabled } = getBinValidationConfig();

    if (cardBinValidationEnabled === true && cardNumber.length >= 6) {
      this.handleBinValidation(cardNumber);
    } else {
      this.handleCardNumberValidation(cardNumber);
    }
  };

  handleCardNumberValidation = (cardNumber) => {
    const { numberValid } = this.context;
    let valid = true;
    const type = document.getElementById('payment-card-type').value;

    if (this.acceptedCards.indexOf(type) === -1) {
      valid = false;
    } else if (luhn.validate(cardNumber, type) === false) {
      valid = false;
    }

    handleValidationMessage(
      'spc-cc-number-error',
      cardNumber,
      valid,
      getStringMessage('invalid_card'),
    );

    this.updateCurrentContext({
      numberValid: valid,
      number: cardNumber,
    });

    if (numberValid !== valid && valid) {
      this.ccExpiry.focus();
    }
  };

  // Bin validation - First 6 digits of a card is the bin number.
  handleBinValidation = (cardNumber) => {
    const cardBin = cardNumber.substring(0, 6);
    const validation = binValidation(cardBin);

    if (validation.error !== undefined) {
      const errorKey = validation.error_message || 'invalid_card';

      handleValidationMessage(
        'spc-cc-number-error',
        cardNumber,
        false,
        getStringMessage(errorKey),
      );
      return;
    }

    if (validation === true) {
      // Validate full card number if bin is valid.
      this.handleCardNumberValidation(cardNumber);
    }
  };

  handleCardTypeChanged = (type) => {
    document.getElementById('payment-card-type').value = type;
  };

  handleCardExpiryChange = (event, handler) => {
    const { labelEffect } = this.props;
    let valid = true;
    labelEffect(event, handler);
    const dateParts = event.target.value.split('/').map((x) => {
      if (!(x) || Number.isNaN(x)) {
        return 0;
      }
      return parseInt(x, 10);
    });

    if (dateParts.length < 2 || dateParts[0] <= 0 || dateParts[1] <= 0) {
      valid = false;
    } else {
      const date = new Date();
      const century = date.getFullYear().toString().substr(0, 2);
      date.setFullYear(century + dateParts[1], dateParts[0], 1);
      const today = new Date();
      if (date < today) {
        valid = false;
      }
    }

    handleValidationMessage(
      'spc-cc-expiry-error',
      event.target.value,
      valid,
      getStringMessage('invalid_expiry'),
    );

    const { expiryValid } = this.context;
    this.updateCurrentContext({
      expiryValid: valid,
      expiry: event.target.value,
    });

    if (expiryValid !== valid && valid) {
      this.ccCvv.current.focus();
    }
  };

  render() {
    const { cardType } = this.context;
    const { handleCardCvvChange, enableCheckoutLink } = this.props;

    const cardTypes = Object.entries(this.acceptedCards).map(([, type]) => (
      <CardTypeSVG key={type} type={type} class={`${type} is-active`} />
    ));

    return (
      <>
        <div className="payment-form-wrapper">
          <input type="hidden" id="payment-card-type" value={cardType} />
          <div className="spc-type-textfield spc-type-cc-number">
            <Cleave
              id="spc-cc-number"
              options={{
                creditCard: true,
                onCreditCardTypeChanged: this.handleCardTypeChanged,
              }}
              required
              type="tel"
              name="spc-no-autocomplete-name"
              autoComplete="off"
              onChange={() => this.showCardType()}
              onBlur={(e) => this.handleCardNumberChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('Card Number')}</label>
            <div id="spc-cc-number-error" className="error" />
          </div>
          <div className="spc-type-textfield spc-type-expiry">
            <Cleave
              id="spc-cc-expiry"
              type="tel"
              htmlRef={(ref) => { this.ccExpiry = ref; }}
              options={{
                date: true,
                dateMin: this.dateMin,
                datePattern: ['m', 'y'],
                delimiter: '/',
              }}
              required
              name="spc-no-autocomplete-expiry"
              autoComplete="off"
              onChange={(e) => this.handleCardExpiryChange(e, 'change')}
              onBlur={(e) => this.handleCardExpiryChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('Expiry')}</label>
            <div id="spc-cc-expiry-error" className="error" />
          </div>
          <div className="spc-type-textfield spc-type-cvv">
            <input
              type="password"
              id="spc-cc-cvv"
              ref={this.ccCvv}
              pattern="\d{3,4}"
              maxLength="4"
              required
              name="spc-no-autocomplete-cvv"
              autoComplete="off"
              onChange={(e) => enableCheckoutLink(e)}
              onBlur={(e) => handleCardCvvChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('CVV')}</label>
            <div id="spc-cc-cvv-error" className="error" />
            <ToolTip enable question><CVVToolTipText /></ToolTip>
          </div>
        </div>
        <div className="spc-card-types-wrapper">
          {cardTypes}

          <ConditionalView condition={drupalSettings.checkoutComUpapi.processMada}>
            <CardTypeSVG key="mada-svg" type="mada" class="mada is-active" />
          </ConditionalView>
        </div>

        <ConditionalView condition={drupalSettings.user.uid > 0 && allowSavedCcForTopUp()}>
          <ConditionalView condition={drupalSettings.checkoutComUpapi.tokenize === true}>
            <div className="spc-payment-save-card">
              <input type="checkbox" value={1} id="payment-card-save" name="save_card" />
              <label htmlFor="payment-card-save">
                {Drupal.t('Save this card for faster payment next time you shop. (CVV number will not be saved)')}
              </label>
            </div>
          </ConditionalView>

          <ConditionalView condition={drupalSettings.checkoutComUpapi.tokenize === false}>
            <input type="hidden" value={0} id="payment-card-save" name="save_card" />
          </ConditionalView>
        </ConditionalView>
      </>
    );
  }
}

export default NewCard;
