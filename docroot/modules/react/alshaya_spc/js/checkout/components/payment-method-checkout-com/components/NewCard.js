import React from 'react';
import Cleave from 'cleave.js/react';
import ConditionalView from '../../../../common/components/conditional-view';
import luhn from '../../../../utilities/luhn';
import CardTypeSVG from '../../../../svg-component/card-type-svg';
import { CheckoutComContext } from '../../../../context/CheckoutCom';
import ToolTip from '../../../../utilities/tooltip';
import CVVToolTipText from '../../cvv-text';
import getStringMessage from '../../../../utilities/strings';
import { handleValidationMessage } from '../../../../utilities/form_item_helper';

class NewCard extends React.Component {
  static contextType = CheckoutComContext;

  constructor(props) {
    super(props);

    this.ccExpiry = React.createRef();
    this.ccCvv = React.createRef();

    const date = new Date();
    this.dateMin = `${date.getMonth() + 1}-${date.getFullYear().toString().substr(-2)}`;
    this.acceptedCards = ['visa', 'mastercard', 'diners'];
  }

  updateCurrentContext = (obj) => {
    const { updateState } = this.context;
    updateState(obj);
  };

  handleCardNumberChange = (event, handler) => {
    const { labelEffect } = this.props;
    const { numberValid } = this.context;
    const cardNumber = event.target.rawValue;

    labelEffect(event, handler);

    let valid = true;
    const type = document.getElementById('payment-card-type').value;

    if (this.acceptedCards.indexOf(type) === -1) {
      valid = false;
    } else if (luhn.validate(cardNumber, type) === false) {
      valid = false;
    }

    handleValidationMessage(
      'spc-cc-number-error',
      event.target.rawValue,
      valid,
      getStringMessage('invalid_card'),
    );

    this.updateCurrentContext({
      numberValid: valid,
      number: cardNumber,
      cardType: type,
    });

    if (numberValid !== valid && valid) {
      this.ccExpiry.focus();
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
      const century = parseInt(`${date.getFullYear().toString().substr(2)}00`, 10);
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
    const { handleCardCvvChange } = this.props;

    const cardTypes = Object.entries(this.acceptedCards).map(([, type]) => (
      <CardTypeSVG key={type} type={type} class={`${type} ${cardType === type ? 'is-active' : ''}`} />
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
              onBlur={(e) => this.handleCardNumberChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('card number')}</label>
            <div id="spc-cc-number-error" className="error" />
          </div>
          <div className="spc-type-textfield spc-type-expiry">
            <Cleave
              id="spc-cc-expiry"
              htmlRef={(ref) => { this.ccExpiry = ref; }}
              options={{
                date: true,
                dateMin: this.dateMin,
                datePattern: ['m', 'y'],
                delimiter: '/',
              }}
              required
              onBlur={(e) => this.handleCardExpiryChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('expiry')}</label>
            <div id="spc-cc-expiry-error" className="error" />
          </div>
          <div className="spc-type-textfield spc-type-cvv">
            <input
              type="tel"
              className="secure-input"
              id="spc-cc-cvv"
              ref={this.ccCvv}
              pattern="\d{3,4}"
              required
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
        </div>
        <ConditionalView
          condition={
            window.drupalSettings.user.uid > 0 && drupalSettings.checkoutCom.tokenize === true
          }
        >
          <div className="spc-payment-save-card">
            <input type="checkbox" value={1} id="payment-card-save" name="save_card" />
            <label htmlFor="payment-card-save">
              {Drupal.t('save this card for faster payment next time you shop. (CVV number will not be saved)')}
            </label>
          </div>
        </ConditionalView>
      </>
    );
  }
}

export default NewCard;
