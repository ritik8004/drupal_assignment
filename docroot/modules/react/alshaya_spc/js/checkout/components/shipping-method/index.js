import React from 'react';
import Popup from 'reactjs-popup';
import PriceElement from '../../../utilities/special-price/PriceElement';
import {
  addShippingInCart, removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import {
  prepareAddressDataForShipping,
} from '../../../utilities/address_util';
import ConditionalView from '../../../common/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import OrderBookingCalendar from '../order-booking/calendar';

export default class ShippingMethod extends React.Component {
  constructor(props) {
    super(props);
    const { selected } = this.props;
    this.state = {
      selectedOption: selected,
      isModalOpen: false,
    };
  }

  componentDidMount = () => {
    document.addEventListener('onShippingAddressUpdate', this.refreshShippingMethodState, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onShippingAddressUpdate', this.refreshShippingMethodState, false);
  }

  /**
   * Handles shipping method update.
   */
  refreshShippingMethodState = (e) => {
    const data = e.detail;

    if (hasValue(data.shipping)
      && hasValue(data.shipping.methods)
      && hasValue(data.shipping.method)) {
      // Get the selected shipping method from shipping methods list,
      // update state for selected method and set radio button as checked.
      Object.entries(data.shipping.methods).forEach(([, method]) => {
        if (data.shipping.method.indexOf(method.method_code) !== -1) {
          this.setState({
            selectedOption: method.method_code,
          });
          // Add delay to ensure selected shipping method markup is rendered
          // before setting the radio button as checked.
          setTimeout(() => {
            document.getElementById(`shipping-method-${method.method_code}`).checked = true;
          }, 10);
        }
      });
    }
  }

  /**
   * Handles shipping method change.
   */
  changeShippingMethod = (method) => {
    const { cart, refreshCart } = this.props;
    const selectCarrierInfo = `${method.carrier_code}_${method.method_code}`;

    // If mathod is already selected in cart.
    // After recent change in api response, need to check if method is applicable.
    if (cart.cart.shipping.method === selectCarrierInfo && method.available) {
      return;
    }

    // If method is not available.
    if (!method.available) {
      return;
    }

    // Show the loader.
    showFullScreenLoader();

    // Prepare shipping data.
    const tempShippingData = cart.cart.shipping.address;
    Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
      tempShippingData[key] = cart.cart.shipping.address[field.key];
    });
    tempShippingData.mobile = cart.cart.shipping.address.telephone;

    // Get prepared address data for shipping address update.
    const data = prepareAddressDataForShipping(tempShippingData);
    data.carrier_info = {
      carrier: method.carrier_code,
      method: method.method_code,
    };

    // Extra info for logged in user.
    if (drupalSettings.user.uid > 0) {
      data.static.customer_address_id = cart.cart.shipping.address.customer_address_id;
      data.static.customer_id = cart.cart.shipping.address.customer_id;
    }

    // Update shipping address.
    const cartData = addShippingInCart('update shipping', data);
    if (cartData instanceof Promise) {
      cartData.then((cartResult) => {
        let cartInfo = {
          cart: cartResult,
        };
        // If no error.
        if (cartResult.error === undefined) {
          // Update state and radio button.
          this.setState({
            selectedOption: method.method_code,
          });
          document.getElementById(`shipping-method-${method.method_code}`).checked = true;

          // Custom Event for shipping method change.
          const event = new CustomEvent('changeShippingMethod', {
            bubbles: true,
            detail: {
              data: method,
            },
          });
          document.dispatchEvent(event);
        } else {
          // In case of error, prepare error info
          // and call refresh cart so that message is shown.
          cartInfo = {
            error_message: cartResult.error_message,
          };
        }

        // Remove loader.
        removeFullScreenLoader();

        // Refresh cart.
        refreshCart(cartInfo);
      });
    }
  };

  /**
   * Array of available_time_slots from get time slot API.
   *
   * @returns {array}
   *  Array of time slots object with date.
   */
  getHFDBookingTimeSlots = () => (
    // Value of available_time_slots from get time slot API.
    // @todo: need to make it dynamic when APIs are available.
    [
      {
        appointment_date: '2022-02-27',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-02-27T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '3:00 PM',
            end_time: '4:00 PM',
            appointment_date_time: '2022-02-27T15:00:00.000Z',
            resource_external_id: 'EveningShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-02-28',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-02-28T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-03-01',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-03-01T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '1:00 PM',
            end_time: '2:00 PM',
            appointment_date_time: '2022-03-01T08:00:00.000Z',
            resource_external_id: 'AfernoonShiftZone1KSA',
          },
        ],
      },
    ]
  );

  /**
   * Open delivery schedule calendar popup.
   */
  openScheduleDeliveryModal = () => {
    this.setState({
      isModalOpen: true,
    });
  };

  /**
   * Close delivery schedule calendar popup.
   */
  closeScheduleDeliveryModal = () => {
    this.setState({
      isModalOpen: false,
    });
  };

  render() {
    const { method } = this.props;
    const { selectedOption, isModalOpen } = this.state;
    const methodClass = method.available ? 'active' : 'disabled';
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <PriceElement amount={method.amount} />;
    }
    return (
      <div className={`shipping-method ${methodClass}`} onClick={() => this.changeShippingMethod(method)}>
        <input
          id={`shipping-method-${method.method_code}`}
          className={method.method_code}
          type="radio"
          defaultChecked={selectedOption === method.method_code}
          value={method.method_code}
          name="shipping-method"
        />

        <label className="radio-sim radio-label">
          <span className="carrier-title">{method.carrier_title}</span>
          <span className="method-title">{method.method_title}</span>
          {/**
           * @todo: Need to change this while working on checkout page till
           * line number 262.
           */}
          <span
            className="method-text"
            onClick={() => this.openScheduleDeliveryModal()}
          >
            {Drupal.t('Change Schedule')}
          </span>
          <Popup
            className="schedule-delivery-calendar-popup"
            open={isModalOpen}
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <>
              {/**
               * @todo: Change the selectDate with first slot held.
               */}
              <OrderBookingCalendar
                selectDate="2022-02-27T08:00:00.000Z"
                bookingSlots={this.getHFDBookingTimeSlots()}
                closeScheduleDeliveryModal={this.closeScheduleDeliveryModal}
              />
            </>
          </Popup>
          <span className="spc-price">{price}</span>
        </label>
        <ConditionalView condition={!method.available}>
          <div className="method-error-message">{method.error_message}</div>
        </ConditionalView>
      </div>
    );
  }
}
