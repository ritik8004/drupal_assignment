import React from 'react';
import Cleave from 'cleave.js/react';
import luhn from "../../../utilities/luhn";

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
      acceptedCards: ['visa', 'mastercard'],
    };
  };

  handleCardNumberChange(event) {
    const prevState = this.state;
    let valid = true;
    const type = document.getElementById('payment-card-type').value;

    if (event.target.rawValue.length < 15) {
      valid = false;
    }
    else if (luhn.validate(event.target.rawValue) === false) {
      valid = false;
    }
    else if (this.state.acceptedCards.indexOf(type) === -1) {
      valid = false;
    }

    console.log(luhn.validate(event.target.rawValue));

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

    console.log(dateParts);

    if (dateParts.length < 2 || dateParts[0] <= 0 || dateParts[1] <= 0) {
      valid = false;
    }
    else {
      let date = new Date();
      let century = parseInt(date.getFullYear().toString().substr(2) + '00');
      date.setFullYear(century + dateParts[1], dateParts[0], 1);
      let today = new Date();
      if (date < today) {
        console.log(1232);
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

  render() {
    return (
      <div id="PaymentCreditCardForm">
        <input type="hidden" id="payment-card-type" value={this.state.cardType} />
        <Cleave placeholder="Enter your credit card number"
                options={{
                  creditCard: true,
                  onCreditCardTypeChanged: this.handleCardTypeChanged.bind(this),
                }}
                onChange={this.handleCardNumberChange.bind(this)}
        />

        <Cleave placeholder="mm/yy"
                htmlRef={(ref) => this.ccExpiry = ref }
                options={{
                  date: true,
                  dateMin: this.dateMin,
                  datePattern: ['m', 'y'],
                  delimiter: '/',
                }}
                onChange={this.handleCardExpiryChange.bind(this)}
        />

        <input
          type="tel"
          ref={this.ccCvv}
          placeholder="CVV"
          pattern="\d{3,4}"
          required
          onChange={this.handleCardCvvChange.bind(this)}
        />
      </div>
    );
  };
}

export default PaymentMethodCheckoutCom;
