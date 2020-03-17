import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import CodSurchargePaymentMethodDescription
  from '../payment-description-cod-surchage';
import PaymentMethodCheckoutCom from '../payment-method-checkout-com';
import PaymentMethodIcon from '../payment-method-svg';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  placeOrder,
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import CheckoutComContextProvider from '../../../context/CheckoutCom';
import { removeStorageInfo } from '../../../utilities/storage';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodCheckoutCom = React.createRef();
  }

  validateBeforePlaceOrder = () => {
    // Do additional process for some payment methods.
    if (this.props.method.code === 'checkout_com') {
      this.paymentMethodCheckoutCom.current.validateBeforePlaceOrder();
    } else if (this.props.method.code === 'knet') {
      showFullScreenLoader();

      const paymentData = {
        payment: {
          method: 'knet',
          additional_data: {},
        },
      };

      this.finalisePayment(paymentData);

      // Throwing 200 error, we want to handle place order in custom way.
      throw 200;
    }
  }

  finalisePayment = (paymentData) => {
    addPaymentMethodInCart('finalise payment', paymentData).then((result) => {
      if (result.error !== undefined && result.error) {
        removeFullScreenLoader();
        console.error(result.error);
      } else if (result.cart_id !== undefined && result.cart_id) {
        // 2D flow success.
        const { cart } = this.props;
        placeOrder(cart.cart.cart_id, cart.selected_payment_method);
        removeStorageInfo('spc_selected_card');
      } else if (result.success === undefined || !(result.success)) {
        // 3D flow error.
        console.error(result);
      } else if (result.redirectUrl !== undefined) {
        // 3D flow success.
        removeStorageInfo('spc_selected_card');
        window.location = result.redirectUrl;
      } else {
        console.error(result);
        removeFullScreenLoader();
      }
    }).catch((error) => {
      removeFullScreenLoader();
      console.error(error);
    });
  }

  render() {
    const { code: method } = this.props.method;
    const { isSelected, changePaymentMethod, cart } = this.props;

    return (
      <>
        <div className={`payment-method payment-method-${method}`} onClick={() => changePaymentMethod(method)}>
          <div className="payment-method-top-panel">
            <input
              id={`payment-method-${method}`}
              className={method}
              type="radio"
              defaultChecked={isSelected}
              value={method}
              name="payment-method"
            />

            <label className="radio-sim radio-label">
              {this.props.method.name}
              <ConditionalView condition={method === 'cashondelivery' && cart.cart.surcharge.amount > 0}>
                <CodSurchargePaymentMethodDescription surcharge={cart.cart.surcharge} />
              </ConditionalView>
            </label>

            <PaymentMethodIcon methodName={method} />
          </div>

          <ConditionalView condition={(isSelected && method === 'checkout_com')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method}`}>
              <CheckoutComContextProvider>
                <PaymentMethodCheckoutCom ref={this.paymentMethodCheckoutCom} cart={cart} finalisePayment={this.finalisePayment} />
              </CheckoutComContextProvider>
            </div>
          </ConditionalView>
        </div>
      </>
    );
  }
}
