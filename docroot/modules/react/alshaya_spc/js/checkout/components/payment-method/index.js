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
      <div>
      	<input
      	  id="payment-method"
      	  className={this.props.method.code}
      	  type="radio"
      	  onChange={this.handleChange}
      	  checked={this.state.selectedOption === this.props.method.code}
      	  value={this.props.method.code}
      	  name="payment-method" />
      	{this.props.method.name}
      	<div dangerouslySetInnerHTML={this.getHtmlMarkup(this.props.method.description)}/>
      </div>
    );
  }

}
