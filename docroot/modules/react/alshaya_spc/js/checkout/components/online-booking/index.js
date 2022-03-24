import React from 'react';
import Popup from 'reactjs-popup';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import
{
  getBookingDetailByConfirmationNumber,
  getAvailableBookingSlots,
  holdBookingSlot,
} from '../../../../../js/utilities/onlineBookingHelper';
import Loading from '../../../../../js/utilities/loading';
import DefaultShippingElement from '../shipping-method/components/DefaultShippingElement';
import OnlineBookingCalendar from './calendar';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

export default class OnlineBooking extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      // bookingDetails object holds online booking values.
      // such as booking date, time slots etc.
      bookingDetails: {},
      // wait is used to check if API call to MDC is completed.
      wait: true,
      // isModalOpen used to check if calendar popup is open.
      isModalOpen: false,
      // This will hold all the available booking slots received from the
      // backend API, default to empty.
      availableSlots: [],
    };
  }

  componentDidMount = async () => {
    const { cart } = this.props;
    let result = { api_error: true };
    // We need to show online booking component only if home delivery method
    // is selected and shipping methods are available in cart.
    if (this.checkHomeDelivery(cart) && hasValue(cart.cart.shipping.methods)) {
      // Check if the cart is having confirmation number.
      cart.confirmation_number = 'G2Z7Y67B'
      if (hasValue(cart.confirmation_number)) {
        result = await getBookingDetailByConfirmationNumber(cart.confirmation_number);
      } else {
        // If confirmation number is not there in basket,
        // this means user hadn't reserved any slot earlier.
        // In this case, we fetch all the available slots
        // for the booking and reserve/hold the first available slot from this list.
        // If we have first slot available, we will hold that one.
        result = await getAvailableBookingSlots();
        if (hasValue(result.success)) {
          const [availableSlot] = result.available_time_slots;
          const [firstSlot] = availableSlot.appointment_slots;
          if (hasValue(firstSlot)) {
            const params = {
              resource_external_id: firstSlot.resource_external_id,
              appointment_date_time: firstSlot.appointment_date_time,
            };
            // Hold the first slot for user for first time.
            result = await holdBookingSlot(params);
            if (hasValue(result.success)) {
              result = {
                success: true,
                appointment_details: {
                  ...firstSlot,
                  confirmation_number: result.hold_appointment.confirmation_number,
                  appointment_date: availableSlot.appointment_date,
                },
              };
            }
          }
        }
      }
    }

    // Set booking Details response and wait to false.
    this.setState({ bookingDetails: result, wait: false });
  }

  /**
   * Check if the delivery type is home delivery.
   */
  checkHomeDelivery = (cart) => {
    let type;
    if (typeof cart.delivery_type !== 'undefined') {
      type = cart.delivery_type;
    }
    if (typeof cart.cart.shipping.type !== 'undefined') {
      type = cart.cart.shipping.type;
    }
    return type === 'home_delivery';
  }

  /**
   * Get all available booking slots and open delivery schedule calendar popup.
   */
  openScheduleDeliveryModal = async () => {
    // Update the isModalOpen to true for opening the popup.
    const stateToUpdate = { isModalOpen: true };

    // Get all available slots from the backend via API and set in the states,
    // if API returns success.
    const result = await getAvailableBookingSlots();
    if (hasValue(result.success)) {
      stateToUpdate.availableSlots = result.available_time_slots;

      // Update state with the available data.
      this.setState(stateToUpdate);
    }
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
   * Booking schedule update handlers for the calendar popup.
   */
  handleScheduleDeliveryChangeInModal = async (
    appointmentDate,
    selectedScheduleDetails,
  ) => {
    // Close the popup and return if both params are not defined/empty.
    if (!hasValue(appointmentDate) || !hasValue(selectedScheduleDetails)) {
      this.closeScheduleDeliveryModal();
      return;
    }

    // Get the current booking details from the state.
    const { bookingDetails } = this.state;

    // If not existing booking details found we won't do anything as per
    // assumption first time slot will be hold on page load and calendar
    // will open only after that.
    if (hasValue(bookingDetails.appointment_details)
      && !hasValue(bookingDetails.appointment_details.confirmation_number)
    ) {
      this.closeScheduleDeliveryModal();
      return;
    }

    // Preparing the params for holding the appoitment.
    const params = {
      resource_external_id: selectedScheduleDetails.resource_external_id,
      appointment_date_time: selectedScheduleDetails.appointment_date_time,
      existing_hold_confirmation_number: bookingDetails.appointment_details.confirmation_number,
    };

    // Hold the new slot for the user, overridding the existing slot.
    let result = await holdBookingSlot(params);
    if (hasValue(result.success)) {
      result = {
        success: true,
        appointment_details: {
          ...selectedScheduleDetails,
          confirmation_number: result.hold_appointment.confirmation_number,
          appointment_date: appointmentDate,
        },
      };
    }

    // Set booking Details response and clost the modal.
    this.setState({ bookingDetails: result, isModalOpen: false });
  };

  render() {
    const {
      wait,
      bookingDetails,
      isModalOpen,
      availableSlots,
    } = this.state;

    if (wait) {
      return <Loading />;
    }

    const { price, method } = this.props;

    // If there is a failure during the API call, we will render the existing component.
    if (bookingDetails.api_error) {
      return <DefaultShippingElement method={method} price={price} />;
    }

    return (
      <>
        <div id="online-booking">
          <label className="radio-sim radio-label">
            <span className="carrier-title">{Drupal.t('Delivery Schedule', {}, { context: 'online_booking' })}</span>
            {/**
             * Validate bookingDetails have data, otherwise display the internal error message.
             */}
            { hasValue(bookingDetails.appointment_details) && (
              <>
                <div className="online-booking-title">
                  {
                    Drupal.t(
                      'All items in cart are delivered on your preferred date',
                      {}, { context: 'online_booking' },
                    )
                  }
                </div>
                <div>
                <div className="available-delivery">
                  {
                    parse(
                      Drupal.t(
                        'Earliest available delivery on @appointment_date between !time_slot',
                        {
                          '@appointment_date': bookingDetails.appointment_details.appointment_date,
                          '!time_slot': `<b>${bookingDetails.appointment_details.start_time} - ${bookingDetails.appointment_details.end_time}</b>`,
                        }, { context: 'online_booking' },
                      ),
                    )
                  }
                </div>
                <div className="change-delivery-schedule">
                  <a href="#" onClick={() => this.openScheduleDeliveryModal()}>
                    {Drupal.t('Change Delivery Schedule', {}, { context: 'online_booking' })}
                  </a>
                </div>
                </div>
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
                    <OnlineBookingCalendar
                      availableSlots={availableSlots}
                      bookingDetails={bookingDetails.appointment_details}
                      closeScheduleDeliveryModal={this.closeScheduleDeliveryModal}
                      callback={this.handleScheduleDeliveryChangeInModal}
                    />
      
                  </>
                </Popup>
                <ConditionalView condition={bookingDetails.success}>
                  <div className="hold-delivery">
                    {
                      parse(
                        Drupal.t(
                          'We will hold this delivery schedule for next <b>2 hours</b>',
                          {}, { context: 'online_booking' },
                        ),
                      )
                    }
                  </div>
                </ConditionalView>
                {/**
                 * Placeholder to display the error message when api return success false.
                 */}
                <ConditionalView condition={!bookingDetails.success}>
                  <div className="booking-error-message">{bookingDetails.error_message}</div>
                </ConditionalView>
                <div className="hold-notification">
                  {
                    parse(
                      Drupal.t(
                        'Once the order is placed, changes are not allowed <b>three days</b> before the selected schedule.',
                        {}, { context: 'online_booking' },
                      ),
                    )
                  }
                </div>
              </>
            )}
            {/**
             * Placeholder to display the default internal error message.
             */}
            <ConditionalView condition={!hasValue(bookingDetails.success)}>
              <div className="booking-error-message">
                {
                  Drupal.t(
                    'Online booking: Sorry, something went wrong. Please try again later.',
                    {}, { context: 'online_booking' },
                  )
                }
              </div>
            </ConditionalView>
            <span className="spc-price">{price}</span>
          </label>
        </div>
      </>
    );
  }
}
