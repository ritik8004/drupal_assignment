import React from 'react';
import Popup from 'reactjs-popup';
import parse from 'html-react-parser';
import moment from 'moment-timezone';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import
{
  getBookingDetailByConfirmationNumber,
  getAvailableBookingSlots,
  holdBookingSlot,
  setHideOnlineBooking,
  getHideOnlineBooking,
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
    // Add event listener to validate booking when user place order.
    document.addEventListener('validateOnlineBookingPurchase', this.validateOnlineBookingPurchase, false);
    // Add event listener for shipping address change,
    // We need to reset the online booking storage and fetch details.
    document.addEventListener('onShippingAddressUpdate', this.onShippingAddressUpdate, false);
    // Add event listener for place order.
    // We need to reset the online booking storage.
    document.addEventListener('orderPlaced', this.handlePlaceOrderEvent, false);
    await this.updateBookingDetails();
  }

  componentWillUnmount() {
    document.removeEventListener('validateOnlineBookingPurchase', this.validateOnlineBookingPurchase, false);
    document.removeEventListener('onShippingAddressUpdate', this.onShippingAddressUpdate, false);
    document.removeEventListener('orderPlaced', this.handlePlaceOrderEvent, false);
  }

  updateBookingDetails = async () => {
    const { cart } = this.props;
    let result = { api_error: true };
    // We need to show online booking component only if home delivery method
    // is selected and shipping methods are available in cart and valid for user.
    if (!getHideOnlineBooking()
      && this.checkHomeDelivery(cart)
      && hasValue(cart.cart.shipping.methods)) {
      // Check if the cart is having confirmation number,
      // this means user has already reserved some slot earlier and
      // thus now we need to show the info of that slot only.
      if (hasValue(cart.extension_attributes)
        && hasValue(cart.extension_attributes.hfd_hold_confirmation_number)) {
        const confirmationNumber = cart.extension_attributes.hfd_hold_confirmation_number;
        result = await getBookingDetailByConfirmationNumber(confirmationNumber);
      } else {
        // If confirmation number is not there in basket,
        // this means user hadn't reserved any slot earlier.
        // In this case, we fetch all the available slots
        // for the booking and reserve/hold the first available slot from this list.
        // If we have first slot available, we will hold that one.
        result = await getAvailableBookingSlots();
        if (hasValue(result.status)) {
          const [availableSlot] = result.hfd_time_slots_details;
          const [firstSlot] = availableSlot.appointment_slots;
          if (hasValue(firstSlot)) {
            const params = {
              resource_external_id: firstSlot.resource_external_id,
              appointment_slot_time: firstSlot.appointment_slot_time,
              appointment_length_time: firstSlot.appointment_length_time,
            };
            // Hold the first slot for user for first time.
            result = await holdBookingSlot(params);
            if (hasValue(result.status)) {
              result = {
                status: true,
                hfd_appointment_details: {
                  ...firstSlot,
                  hold_confirmation_number: result.hfd_appointment_details.hold_confirmation_number,
                  appointment_date: availableSlot.appointment_date,
                },
              };
            }
          }
        }
        // Check if the booking is not successful.
        // Set status to online booking as false.
        if (!hasValue(result.status)) {
          setHideOnlineBooking(true);
        }
      }
    }

    // Set booking Details response and wait to false.
    this.setState({ bookingDetails: result, wait: false });
  }

  /**
   * Reset show-online-booking storage.
   */
  onShippingAddressUpdate = async () => {
    setHideOnlineBooking(false);
    // Update online booking component to fetch
    // details again in case there is no error.
    this.setState({ wait: true });
    await this.updateBookingDetails();
  }

  handlePlaceOrderEvent = () => {
    setHideOnlineBooking(false);
  };

  /**
   * Handle online booking error on place order.
   */
  validateOnlineBookingPurchase = (e) => {
    const { bookingDetails } = e.detail;
    // Set booking Details to show inline error when there is some error
    // while fetching booking details.
    this.setState({ bookingDetails });
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
    const elem = document.getElementById('online-booking')
      .getElementsByClassName('online-booking__change-delivery-schedule')[0];
    const stateToUpdate = { isModalOpen: true };
    // Add loading on Change Delivery Schedule link.
    elem.classList.add('loading');
    // Get all available slots from the backend via API and set in the states,
    // if API returns status.
    const result = await getAvailableBookingSlots();
    if (hasValue(result.status)) {
      stateToUpdate.availableSlots = result.hfd_time_slots_details;

      // Update state with the available data.
      this.setState(stateToUpdate);
      // Remove loading on Change Delivery Schedule link.
      elem.classList.remove('loading');
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
    if (hasValue(bookingDetails.hfd_appointment_details)
      && !hasValue(bookingDetails.hfd_appointment_details.hold_confirmation_number)
    ) {
      this.closeScheduleDeliveryModal();
      return;
    }

    // Preparing the params for holding the appoitment.
    const params = {
      resource_external_id: selectedScheduleDetails.resource_external_id,
      appointment_slot_time: selectedScheduleDetails.appointment_slot_time,
      appointment_length_time: selectedScheduleDetails.appointment_length_time,
      existing_hold_confirmation_number:
      bookingDetails.hfd_appointment_details.hold_confirmation_number,
    };

    // Hold the new slot for the user, overriding the existing slot.
    let result = await holdBookingSlot(params);
    if (hasValue(result.status)) {
      result = {
        status: true,
        hfd_appointment_details: {
          ...selectedScheduleDetails,
          hold_confirmation_number: result.hfd_appointment_details.hold_confirmation_number,
          appointment_date: result.hfd_appointment_details.appointment_date,
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
        <div id="online-booking" className="online-booking">
          <label className="radio-sim radio-label">
            <span className="carrier-title">{Drupal.t('Delivery Schedule', {}, { context: 'online_booking' })}</span>
            {/**
             * Validate bookingDetails have data, otherwise display the internal error message.
             */}
            { hasValue(bookingDetails.hfd_appointment_details) && (
              <>
                <div className="online-booking__title">
                  {
                    Drupal.t(
                      'All items in cart are delivered on your preferred date',
                      {}, { context: 'online_booking' },
                    )
                  }
                </div>
                <div className="online-booking__delivery">
                  <div className="online-booking__available-delivery">
                    {
                      parse(
                        Drupal.t(
                          'Earliest available delivery on !appointment_date between !time_slot',
                          {
                            '!appointment_date': `<div class="online-booking__available-delivery-date">${moment(bookingDetails.hfd_appointment_details.appointment_date).format('YYYY-MMM-DD')}</div>`,
                            '!time_slot': `<div class="online-booking__available-delivery-time">${bookingDetails.hfd_appointment_details.start_time} - ${bookingDetails.hfd_appointment_details.end_time}</div>`,
                          }, { context: 'online_booking' },
                        ),
                      )
                    }
                  </div>
                  <div className="online-booking__change-delivery-schedule" onClick={() => this.openScheduleDeliveryModal()}>
                    {Drupal.t('Change Delivery Schedule', {}, { context: 'online_booking' })}
                  </div>
                </div>
                <Popup
                  className="schedule-delivery-calendar-popup"
                  open={isModalOpen}
                  closeOnDocumentClick={false}
                  closeOnEscape={false}
                >
                  <>
                    <OnlineBookingCalendar
                      availableSlots={availableSlots}
                      bookingDetails={bookingDetails.hfd_appointment_details}
                      closeScheduleDeliveryModal={this.closeScheduleDeliveryModal}
                      callback={this.handleScheduleDeliveryChangeInModal}
                    />
                  </>
                </Popup>
                <ConditionalView condition={bookingDetails.status}>
                  <div className="online-booking__hold-delivery">
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
                <ConditionalView condition={!bookingDetails.status}>
                  <div className="online-booking__error-message">{bookingDetails.error_message}</div>
                </ConditionalView>
                <div className="online-booking__hold-notification">
                  <button type="button" className="online-booking__hold-notification-icon" />
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
            <ConditionalView condition={!hasValue(bookingDetails.hfd_appointment_details)
            && !hasValue(bookingDetails.status)}
            >
              <div className="online-booking__error-message">
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
