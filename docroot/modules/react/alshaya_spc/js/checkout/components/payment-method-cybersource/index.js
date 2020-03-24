import React from 'react';
import Cleave from 'cleave.js/react';
import axios from 'axios';
import luhn from '../../../utilities/luhn';
import CardTypeSVG from '../../../svg-component/card-type-svg';
import { i18nMiddleWareUrl } from '../../../utilities/i18n_url';
import { removeCartFromStorage } from '../../../utilities/storage';
import ToolTip from '../../../utilities/tooltip';
import CVVToolTipText from '../cvv-text';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import { dispatchCustomEvent } from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import { handleValidationMessage } from '../../../utilities/form_item_helper';

class PaymentMethodCybersource extends React.Component {
  constructor(props) {
    super(props);

    this.ccExpiry = React.createRef();
    this.ccCvv = React.createRef();

    const date = new Date();
    this.dateMin = `${date.getMonth() + 1}-${date.getFullYear().toString().substr(-2)}`;
    this.acceptedCards = drupalSettings.cybersource.acceptedCards;

    this.state = {
      cvv: '',
      expiry: '',
      number: '',
      cardType: '',
      numberValid: false,
      expiryValid: false,
      cvvValid: false,
    };
  }

  componentDidMount = () => {
    document.addEventListener('cybersourcePaymentUpdate', this.eventListener, false);
    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  };

  componentWillUnmount = () => {
    document.removeEventListener('cybersourcePaymentUpdate', this.eventListener, false);
  };

  eventListener = (e) => {
    if (e.detail.redirectUrl !== undefined) {
      // Remove cart info from storage.
      removeCartFromStorage();

      window.location = Drupal.url(e.detail.redirectUrl);
      return;
    }

    dispatchCustomEvent('spcCheckoutMessageUpdate', {
      type: 'error',
      message: e.detail.error_message === 'failed'
        ? getStringMessage('transaction_failed')
        : getStringMessage('payment_error'),
    });

    removeFullScreenLoader();
  };

  handleCardNumberChange = (event, handler) => {
    const { numberValid: prevNumberValid } = this.state;
    let valid = true;
    const type = document.getElementById('spc-cy-payment-card-type').value;
    this.labelEffect(event, handler);

    if (this.acceptedCards.indexOf(type) === -1) {
      valid = false;
    } else if (luhn.validate(event.target.rawValue, type) === false) {
      valid = false;
    }

    handleValidationMessage(
      'cy-cc-number-error',
      event.target.rawValue,
      valid,
      getStringMessage('invalid_cybersource_card'),
    );

    this.setState({
      numberValid: valid,
      number: event.target.rawValue,
      cardType: type,
    });

    if (prevNumberValid !== valid && valid) {
      this.ccExpiry.focus();
    }
  };

  handleCardTypeChanged = (type) => {
    document.getElementById('spc-cy-payment-card-type').value = type;
  };

  handleCardExpiryChange = (event, handler) => {
    const { expiryValid: prevExpiryValid } = this.state;
    let valid = true;
    this.labelEffect(event, handler);
    const dateParts = event.target.value.split('/').map((x) => {
      if (!(x) || Number.isNaN(Number(x))) {
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
      'spc-cy-cc-expiry-error',
      event.target.value,
      valid,
      getStringMessage('invalid_expiry'),
    );

    this.setState({
      expiryValid: valid,
      expiry: event.target.value,
    });

    if (prevExpiryValid !== valid && valid) {
      this.ccCvv.current.focus();
    }
  };

  handleCardCvvChange = (event, handler) => {
    const cvv = parseInt(event.target.value, 10);
    const valid = (cvv >= 100 && cvv <= 9999);
    this.labelEffect(event, handler);
    handleValidationMessage(
      'spc-cy-cc-cvv-error',
      event.target.value,
      valid,
      getStringMessage('invalid_cvv'),
    );

    this.setState({
      cvvValid: valid,
      cvv: event.target.value,
    });
  };

  validateBeforePlaceOrder = () => {
    const { numberValid, expiryValid, cvvValid } = this.state;
    if (!(numberValid && expiryValid && cvvValid)) {
      console.error('client side validation failed for credit card info');
      return false;
    }

    showFullScreenLoader();

    const { cardType } = this.state;
    const apiUrl = i18nMiddleWareUrl('payment/cybersource/get-token');
    axios.post(apiUrl, { type: cardType }).then((response) => {
      // Handle exception.
      if (response.data.error !== undefined) {
        console.error(response.data);

        dispatchCustomEvent('spcCheckoutMessageUpdate', {
          type: 'error',
          message: getStringMessage('payment_error'),
        });

        removeFullScreenLoader();

        return;
      }

      const { number, expiry, cvv } = this.state;

      response.data.data.card_number = number;
      response.data.data.card_cvn = parseInt(cvv.toString().trim(), 10);

      const expiryInfo = expiry.split('/');
      const date = new Date();
      const century = parseInt(`${date.getFullYear().toString().substr(2)}00`, 10);
      response.data.data.card_expiry_date = `${expiryInfo[0].toString()}-${(century + parseInt(expiryInfo[1], 10)).toString()}`;

      const cybersourceForm = document.getElementById('cybersource_form_to_iframe');
      cybersourceForm.setAttribute('action', response.data.url);
      cybersourceForm.innerHTML = '';

      Object.entries(response.data.data).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', name);
        input.setAttribute('value', value);
        cybersourceForm.appendChild(input);
      });

      cybersourceForm.submit();
    }).catch((error) => {
      console.error(error);

      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });

      removeFullScreenLoader();
    });

    return false;
  };

  labelEffect = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
  };

  handleCheckoutResponse = (data) => {
    // @TODO: Handle errors.
    const paymentData = {
      payment: {
        method: 'cybersource',
        additional_data: data,
      },
    };

    const { finalisePayment } = this.props;
    finalisePayment(paymentData);
  };

  render() {
    const { cardType: selectedCardType } = this.state;
    const cardTypes = Object.entries(this.acceptedCards).map(([, type]) => (
      <CardTypeSVG key={type} type={type} class={`${type} ${selectedCardType === type ? 'is-active' : ''}`} />
    ));

    return (
      <>
        <div className="payment-form-wrapper">
          <input type="hidden" id="spc-cy-payment-card-type" value={selectedCardType} />
          <div className="spc-type-textfield spc-type-cc-number spc-cy-cc-number">
            <Cleave
              options={{
                creditCard: true,
                onCreditCardTypeChanged: this.handleCardTypeChanged,
              }}
              onBlur={(e) => this.handleCardNumberChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('card number')}</label>
            <div id="cy-cc-number-error" className="error" />
          </div>
          <div className="spc-type-textfield spc-type-expiry spc-cy-cc-expiry">
            <Cleave
              htmlRef={(ref) => { this.ccExpiry = ref; }}
              options={{
                date: true,
                dateMin: this.dateMin,
                datePattern: ['m', 'y'],
                delimiter: '/',
              }}
              onBlur={(e) => this.handleCardExpiryChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('expiry')}</label>
            <div id="spc-cy-cc-expiry-error" className="error" />
          </div>
          <div className="spc-type-textfield spc-type-cvv spc-cy-cc-cvv">
            <input
              type="tel"
              className="secure-input"
              ref={this.ccCvv}
              pattern="\d{3,4}"
              required
              onBlur={(e) => this.handleCardCvvChange(e, 'blur')}
            />
            <div className="c-input__bar" />
            <label>{Drupal.t('CVV')}</label>
            <div id="spc-cy-cc-cvv-error" className="error" />
            <ToolTip enable question><CVVToolTipText /></ToolTip>
          </div>
        </div>

        <div className="spc-card-types-wrapper">
          {cardTypes}
        </div>

        <form id="cybersource_form_to_iframe" target="cybersource_iframe" method="post" style={{ display: 'none' }} className="hidden-important" />
        <iframe id="cybersource_iframe" name="cybersource_iframe" style={{ display: 'none' }} className="hidden-important" />
      </>
    );
  }
}

export default PaymentMethodCybersource;
