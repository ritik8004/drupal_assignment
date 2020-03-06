import React from 'react';
import ConditionalView from "../../../common/components/conditional-view";
import CodSurchargePaymentMethodDescription
  from "../payment-description-cod-surchage";

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      'selectedOption': this.props.isSelected
    };
  };

  componentWillReceiveProps(nextProps) {
    this.setState({
      selectedOption: nextProps.isSelected
    });
  }

  getHtmlMarkup(content) {
    return { __html: content };
  };

  getSurchargeShortDescription = () => {
    try {
      let {amount} = this.props.cart.cart.surcharge;
      let description = getStringMessage('cod_surcharge_short_description');
      if (description.length > 0) {
        return reactStringReplace(description, '[surcharge]', this.getSurchargePriceElement);
      }
    }
    catch (e) {
    }

    return '';
  };

  getSurchargePriceElement = () => {
    let {amount} = this.props.cart.cart.surcharge;
    return <PriceElement key="cod_surcharge_short_description" amount={amount} />;
  };

  render() {
    let method = this.props.method.code;
    return(
      <div className='payment-method' onClick={() => this.props.changePaymentMethod(method)}>
      	<input
      	  id={'payment-method-' + method}
      	  className={method}
      	  type='radio'
      	  defaultChecked={this.state.selectedOption === method}
      	  value={method}
      	  name='payment-method' />

        <label className='radio-sim radio-label'>
          {this.props.method.name}
          <ConditionalView condition={method === 'cashondelivery' && this.props.cart.cart.surcharge.amount > 0}>
            <CodSurchargePaymentMethodDescription surcharge={this.props.cart.cart.surcharge}/>
          </ConditionalView>
        </label>
      </div>
    );
  }
}
