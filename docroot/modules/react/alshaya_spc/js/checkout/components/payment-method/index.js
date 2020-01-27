import React from 'react';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      'selectedOption': this.props.isSelected
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      selectedOption: nextProps.isSelected
    });
  }

  getHtmlMarkup(content) {
    return { __html: content };
  }

  changePaymentMethod = (method) => {
    this.setState({
      selectedOption: method
    });

    document.getElementById('payment-method-' + method).checked = true;

    let cart = this.props.cart;
    cart['selected_payment_method'] = method;
    this.props.refreshCart(cart);
  }

  render() {
    let method = this.props.method.code;
    return(
      <div className='payment-method' onClick={() => this.changePaymentMethod(method)}>
      	<input
      	  id={'payment-method-' + method}
      	  className={method}
      	  type='radio'
      	  checked={this.state.selectedOption === method}
      	  value={method}
      	  name='payment-method' />

        <label className='radio-sim radio-label'>
          {this.props.method.name}
        </label>
      </div>
    );
  }

}
