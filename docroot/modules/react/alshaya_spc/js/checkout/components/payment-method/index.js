import React from 'react';
import ConditionalView from "../../../common/components/conditional-view";
import CodSurchargePaymentMethodDescription
  from "../payment-description-cod-surchage";
import PaymentMethodCheckoutCom from "../payment-method-checkout-com";

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodCheckoutCom = React.createRef();

    this.state = {
      'selectedOption': this.props.isSelected
    };
  };

  componentWillReceiveProps(nextProps) {
    this.setState({
      selectedOption: nextProps.isSelected
    });
  }

  validateBeforePlaceOrder () {
    // Do additional process for some payment methods.
    if (this.props.method.code === 'checkout_com') {
      this.paymentMethodCheckoutCom.current.validateBeforePlaceOrder();
    }
  }

  render() {
    let method = this.props.method.code;
    return(
      <>
        <div className={`payment-method payment-method-${method}`} onClick={() => this.props.changePaymentMethod(method)}>
          <div className='payment-method-top-panel'>
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

          <ConditionalView condition={(this.state.selectedOption === 'checkout_com')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method}`}>
              <PaymentMethodCheckoutCom ref={this.paymentMethodCheckoutCom} cart={this.props.cart} />
            </div>
          </ConditionalView>
        </div>
      </>
    );
  }
}
