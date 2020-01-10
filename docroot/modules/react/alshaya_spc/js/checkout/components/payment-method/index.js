import React from 'react';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    let default_val = this.props.method.default
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
