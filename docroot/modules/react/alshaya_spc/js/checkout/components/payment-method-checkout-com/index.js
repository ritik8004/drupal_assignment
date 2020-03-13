import React from 'react';
import Cleave from 'cleave.js/react';
import luhn from "../../../utilities/luhn";
import {showFullScreenLoader} from "../../../utilities/checkout_util";
import ConditionalView from "../../../common/components/conditional-view";
import CardTypeSVG from "../card-type-svg";
import ToolTip from "../../../utilities/tooltip";

class PaymentMethodCheckoutCom extends React.Component {

  constructor(props) {
    super(props);

    this.ccExpiry = React.createRef();
    this.ccCvv = React.createRef();

    let date = new Date();
    this.dateMin = date.getMonth() + 1 + '-' + date.getFullYear().toString().substr(-2);

    this.state = {
      cvc: '',
      expiry: '',
      number: '',
      cardType: '',
      numberValid: false,
      expiryValid: false,
      cvvValid: false,
      acceptedCards: ['visa', 'mastercard', 'diners'],
    };
  };

  labelEffect = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      }
      else {
        e.currentTarget.classList.remove('focus');
      }
    }
  };

  handleCardNumberChange(event) {
    const prevState = this.state;
    let valid = true;
    const type = document.getElementById('payment-card-type').value;

    if (this.state.acceptedCards.indexOf(type) === -1) {
      valid = false;
    }
    else if (luhn.validate(event.target.rawValue, type) === false) {
      valid = false;
    }

    if (valid) {
      event.target.classList.remove('invalid');
    }
    else {
      event.target.classList.add('invalid');
    }

    this.setState({
      ...prevState,
      numberValid: valid,
      number: event.target.rawValue,
      cardType: type
    });

    if (prevState.numberValid !== valid && valid) {
      this.ccExpiry.focus();
    }
  }

  handleCardTypeChanged (type) {
    document.getElementById('payment-card-type').value = type;
  }

  handleCardExpiryChange (event) {
    let valid = true;
    let dateParts = event.target.value.split('/').map(x => {
      if (!(x) || isNaN(x)) {
        return 0;
      }
      return parseInt(x);
    });

    if (dateParts.length < 2 || dateParts[0] <= 0 || dateParts[1] <= 0) {
      valid = false;
    }
    else {
      let date = new Date();
      let century = parseInt(date.getFullYear().toString().substr(2) + '00');
      date.setFullYear(century + dateParts[1], dateParts[0], 1);
      let today = new Date();
      if (date < today) {
        valid = false;
      }
    }

    const prevState = this.state;
    this.setState({
      ...prevState,
      expiryValid: valid,
      expiry: event.target.value,
    });

    if (prevState.expiryValid !== valid && valid) {
      this.ccCvv.current.focus();
    }
  }

  handleCardCvvChange (event) {
    if (window.CheckoutKit === undefined) {
      console.error('CheckoutKit not available');
      throw 500;
    }

    let valid = false;
    let cvv = parseInt(event.target.value);
    if (cvv >= 100 && cvv <= 9999) {
      valid = true;
    }

    const prevState = this.state;
    this.setState({
      ...prevState,
      cvvValid: valid,
      cvv: event.target.value,
    });
  }

  validateBeforePlaceOrder = () => {
    if (!(this.state.numberValid && this.state.expiryValid && this.state.cvvValid)) {
      console.error('Client side validation failed for credit card info');
      throw 'UnexpectedValueException';
    }
    else if (window.CheckoutKit === undefined) {
      console.error('Checkout kit not loaded');
      throw 500;
    }

    showFullScreenLoader();

    let udf3 = (window.drupalSettings.user.uid > 0 && document.getElementById('payment-card-save').checked)
      ? 'storeInVaultOnSuccess'
      : '';

    var ccInfo = {
      'number': this.state.number,
      'expiryMonth': this.state.expiry.split('/')[0],
      'expiryYear': this.state.expiry.split('/')[1],
      'cvv': this.state.cvv,
      'udf3': udf3,
    };

    window.CheckoutKit.configure({
      debugMode: drupalSettings.checkoutCom.debugMode,
      publicKey: drupalSettings.checkoutCom.publicKey,
    });

    window.CheckoutKit.createCardToken(ccInfo, this.handleCheckoutResponse);

    // Throwing 200 error, we want to handle place order in custom way.
    throw 200;
  };

  handleCheckoutResponse = (data) => {
    // @TODO: Handle errors.
    data['udf3'] = (window.drupalSettings.user.uid > 0 && document.getElementById('payment-card-save').checked)
      ? 'storeInVaultOnSuccess'
      : '';

    let paymentData = {
      'payment': {
        'method': 'checkout_com',
        'additional_data': data,
      },
    };

    this.props.finalisePayment(paymentData);
  };

  render() {
    let cartTypes = [];
    Object.entries(this.state.acceptedCards).forEach(([key, type]) => {
      let activeClass = (this.state.cardType === type) ? 'is-active' : '';
      cartTypes.push(<CardTypeSVG key={type} type={type} class={`${type} ${activeClass}`} />);
    });

    let CVVText = Drupal.t('This code is a three or four digit number printed on the front or back of the credit card');

    return (
      <>
        <div className='payment-form-wrapper'>
          <input type='hidden' id='payment-card-type' value={this.state.cardType} />
          <div className='spc-type-textfield spc-type-cc-number'>
            <Cleave
              id='spc-cc-number'
              options={{
                creditCard: true,
                onCreditCardTypeChanged: this.handleCardTypeChanged.bind(this),
              }}
              required
              onChange={this.handleCardNumberChange.bind(this)}
              onBlur={(e) => this.labelEffect(e, 'blur')}
            />
            <div className='c-input__bar'/>
            <label>{Drupal.t('card number')}</label>
            <div id='spc-cc-number-error' className="error" />
          </div>
          <div className='spc-type-textfield spc-type-expiry'>
            <Cleave
              id='spc-cc-expiry'
              htmlRef={(ref) => this.ccExpiry = ref }
              options={{
                date: true,
                dateMin: this.dateMin,
                datePattern: ['m', 'y'],
                delimiter: '/',
              }}
              required
              onChange={this.handleCardExpiryChange.bind(this)}
              onBlur={(e) => this.labelEffect(e, 'blur')}
            />
            <div className='c-input__bar'/>
            <label>{Drupal.t('expiry')}</label>
            <div id='spc-cc-expiry-error' className="error" />
          </div>
          <div className='spc-type-textfield spc-type-cvv'>
            <input
              type='tel'
              id='spc-cc-cvv'
              ref={this.ccCvv}
              pattern="\d{3,4}"
              required
              onChange={this.handleCardCvvChange.bind(this)}
              onBlur={(e) => this.labelEffect(e, 'blur')}
            />
            <div className='c-input__bar'/>
            <label>{Drupal.t('CVV')}</label>
            <div id='spc-cc-cvv-error' className="error" />
            <ToolTip content={CVVText} enable question/>
          </div>
        </div>

        <div className='spc-card-types-wrapper'>
          {cartTypes}
        </div>

        <ConditionalView condition={window.drupalSettings.user.uid > 0}>
          <div className='spc-payment-save-card'>
            <input type="checkbox" value={1} id="payment-card-save" name="save_card" />
            <label htmlFor="payment-card-save">{Drupal.t('save this card for faster payment next time you shop. (CVV number will not be saved)')}</label>
          </div>
        </ConditionalView>
      </>
    );
  };
}

export default PaymentMethodCheckoutCom;
