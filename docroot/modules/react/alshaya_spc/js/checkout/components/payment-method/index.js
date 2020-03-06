import React from 'react';

import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {showFullScreenLoader} from "../../../utilities/checkout_util";
import ConditionalView from "../../../common/components/conditional-view";
import reactStringReplace from "react-string-replace";
import PriceElement from "../../../utilities/special-price/PriceElement";
import {getStringMessage} from "../../../utilities/strings";

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

      if (amount === undefined || amount === null || amount <= 0) {
        return '';
      }

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

  changePaymentMethod = (method) => {
    showFullScreenLoader();

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
  };

  render() {
    let method = this.props.method.code;
    return(
      <div className='payment-method' onClick={() => this.changePaymentMethod(method)}>
      	<input
      	  id={'payment-method-' + method}
      	  className={method}
      	  type='radio'
      	  defaultChecked={this.state.selectedOption === method}
      	  value={method}
      	  name='payment-method' />

        <label className='radio-sim radio-label'>
          {this.props.method.name}

          <ConditionalView condition={method === 'cashondelivery'}>
            <span className="cod-surcharge-short-description">
              {this.getSurchargeShortDescription()}
            </span>
          </ConditionalView>
        </label>

        <ConditionalView condition={this.state.selectedOption === 'cashondelivery'}>
            <div className="cod-surcharge-message">
              {getStringMessage('cod_surcharge_description')}
            </div>
          </ConditionalView>
      </div>
    );
  }
}
