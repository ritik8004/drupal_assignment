import React from 'react';
import Popup from 'reactjs-popup';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import
{
  checkAppointmentStatus,
  getAvailableTimeSlots,
  holdAppointment,
} from '../../../../../js/utilities/orderBookingHelper';
import Loading from '../../../utilities/loading';
import DefaultShippingElement from '../shipping-method/components/DefaultShippingElement';
import OrderBookingCalendar from './calendar';

export default class OrderBooking extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      appointment: {},
      wait: true,
      failure: false,
      isModalOpen: false,
    };
  }

  componentDidMount = async () => {
    const { cart } = this.props;
    // Show the loader.
    let appointment = {};
    // Check if the cart is having confirmation number.
    if (hasValue(cart.confirmation_number)) {
      appointment = await checkAppointmentStatus(cart.confirmation_number);
    } else {
      // If confirmation number is not there in basket,
      // then hold the new available appointment for user.
      // First get available slots.
      const firstSlot = await this.getAvailableSlots(true);
      // If we have first slot available, we will hold that one.
      if (hasValue(firstSlot)) {
        const params = {
          resource_external_id: firstSlot.resource_external_id,
          appointment_date_time: firstSlot.appointment_date_time,
        };
        const holdDetails = await holdAppointment(params);
        if (hasValue(holdDetails.confirmation_number)) {
          appointment = firstSlot;
          appointment.confirmation_number = holdDetails.confirmation_number;
        }
      }
    }

    if (hasValue(appointment)) {
      this.setState({
        appointment,
        wait: false,
      });
    } else {
      this.setState({
        failure: true,
        wait: false,
      });
    }
  }

  /**
   * Open delivery schedule calendar popup.
   */
  openScheduleDeliveryModal = () => {
    /**
     * @todo: Before opening the booking modal popup, we need to fetch the
     * available booking dates and slots from the backend API and pass in props.
     * It can be done later when doing the API intergrations. We need to be sure
     * to open the modal only when API returns proper response with data else
     * do nothing to avoid any impact on the checkout process.
     */
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

  /**
   * Get available slot for the current time.
   *
   * @param {boolean} firstSlot
   *   The bearerToken flag.
   *
   * @returns {object}
   *   Returns the result object.
   */
  getAvailableSlots = async (firstSlot = false) => {
    let availableTimeSlots = await getAvailableTimeSlots();
    if (hasValue(availableTimeSlots) && firstSlot) {
      const [availableTimeSlot] = availableTimeSlots;
      const [appointmentSlot] = availableTimeSlot.appointment_slots;
      availableTimeSlots = {
        appointment_date: availableTimeSlot.appointment_date,
        start_time: appointmentSlot.start_time,
        end_time: appointmentSlot.end_time,
        appointment_date_time: appointmentSlot.appointment_date_time,
        resource_external_id: appointmentSlot.resource_external_id,
      };
    }
    return availableTimeSlots;
  }

  render() {
    const {
      wait,
      appointment,
      failure,
      isModalOpen,
    } = this.state;
    const { price, method } = this.props;

    if (wait) {
      return <Loading />;
    }

    // If there is a failure during the API call, we will render the existing component.
    if (failure) {
      return <DefaultShippingElement method={method} price={price} />;
    }

    return (
      <>
        <div className="order-booking">
          <label className="radio-sim radio-label">
            <span className="carrier-title">{Drupal.t('Delivery Schedule', {}, { context: 'order-booking' })}</span>
            <span className="order-booking-title">{Drupal.t('All items in cart are delivered on your preferred date', {}, { context: 'order-booking' })}</span>
            <span className="available-delivery">
              {Drupal.t('Earliest available delivery on @appointment_date between @start_time - @end_time', {
                '@appointment_date': appointment.appointment_date,
                '@start_time': appointment.start_time,
                '@end_time': appointment.end_time,
              }, { context: 'order-booking' })}
            </span>
            <span className="change-delivery-schedule"><a href="#" onClick={() => this.openScheduleDeliveryModal()}>{Drupal.t('Change Delivery Schedule', {}, { context: 'order-booking' })}</a></span>
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
                  selectedDate="2022-02-27T08:00:00.000Z"
                  closeScheduleDeliveryModal={this.closeScheduleDeliveryModal}
                />
              </>
            </Popup>
            <span className="hold-delivery">{Drupal.t('We will hold this delivery schedule for next 2 hours', {}, { context: 'order-booking' })}</span>
            <span className="hold-notification">{Drupal.t('Once the order is placed, changes are not allowed three days before the selected schedule.', {}, { context: 'order-booking' })}</span>
            <span className="spc-price">{price}</span>
          </label>
        </div>
      </>
    );
  }
}
