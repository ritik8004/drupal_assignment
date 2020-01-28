import React from 'react';

import { addPaymentMethodInCart } from '../../../utilities/update_cart';

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

    let data = {
      'payment' : {
        'method': method,
        'additional_data': {}
      }
    };
    let cart = addPaymentMethodInCart('update payment', data);
    if (cart instanceof Promise) {
      cart.then((result) => {
        let cart_data = this.props.cart;
        cart_data['selected_payment_method'] = method;
        cart_data['cart'] = result;
        this.props.refreshCart(cart_data);
      });
    }
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
