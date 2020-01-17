import React from 'react';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    let default_val = this.props.selected_payment_method
      ? this.props.method.code
      : '';
    this.state = {
      'selectedOption': default_val
    };
  }

  getHtmlMarkup(content) {
    return { __html: content };
  }

  handleChange = (e) => {
    const { name, value } = e.target;
    this.setState({
      selectedOption: value
    });
  };

  changePaymentMethod = (method) => {
    this.setState({
      selectedOption: method
    });

    document.getElementById('payment-method-' + method).checked = true;

    // If payment method has no form.
    if (window.drupalSettings.payment_methods[method].has_form === false) {
      this.props.payment_method_select(method);
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
          <span>{this.props.method.description}</span>
        </label>
      </div>
    );
  }

}
