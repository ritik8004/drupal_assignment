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

  render() {
  	return(
      <div className="payment-method">
      	<input
      	  id="payment-method"
      	  className={this.props.method.code}
      	  type="radio"
      	  onChange={this.handleChange}
      	  checked={this.state.selectedOption === this.props.method.code}
      	  value={this.props.method.code}
      	  name="payment-method" />

        <label className="radio-sim radio-label">
          {this.props.method.name}
          <div className="spc-payment-method-desc" dangerouslySetInnerHTML={this.getHtmlMarkup(this.props.method.description)}/>
        </label>
      </div>
    );
  }

}
