import React from 'react';
import Popup from 'reactjs-popup';
import parse from 'html-react-parser';
import moment from 'moment-timezone';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import {
  getAvailableBookingSlots,
  getBookingDetailByConfirmationNumber,
  getHideOnlineBooking,
  holdBookingSlot,
  setHideOnlineBooking,
  getTranslatedTime,
} from '../../../../../js/utilities/onlineBookingHelper';
import Loading from '../../../../../js/utilities/loading';
import DefaultShippingElement from '../shipping-method/components/DefaultShippingElement';
import OnlineBookingCalendar from './calendar';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import logger from '../../../../../js/utilities/logger';

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
    document.addEventListener('onAddShippingInfoUpdate', this.onShippingAddressUpdate, false);
    // Add event listener for place order.
    // We need to reset the online booking storage.
    document.addEventListener('orderPlaced', this.handlePlaceOrderEvent, false);
    await this.updateBookingDetails();
  }

  componentWillUnmount() {
    document.removeEventListener('validateOnlineBookingPurchase', this.validateOnlineBookingPurchase, false);
    document.removeEventListener('onAddShippingInfoCallback', this.onShippingAddressUpdate, false);
    document.removeEventListener('orderPlaced', this.handlePlaceOrderEvent, false);
  }

  updateBookingDetails = async () => {
    const { cart, shippingInfoUpdated } = this.props;
    let result = { api_error: true };
    // Add log for cart details during online booking.
    logger.notice('Cart details during online booking: @cart.', {
      '@cart': JSON.stringify(cart.cart),
    });
    // We need to show online booking component only if home delivery method
    // is selected and shipping methods are available in cart and valid for user.
    if (!getHideOnlineBooking()
      && this.checkHomeDelivery(cart)
      && hasValue(cart.cart.shipping.methods)) {
      // Check if the user have added shipping address before.
      // and now user is adding new shipping address on checkout page.
      // We need to add new booking for the same.
      if (hasValue(shippingInfoUpdated)) {
        await this.onShippingAddressUpdate();
        return;
      }
      // Check if the cart is having confirmation number,
      // this means user has already reserved some slot earlier and
      // thus now we need to show the info of that slot only.
      if (hasValue(cart.cart.hfd_hold_confirmation_number)) {
        result = await getBookingDetailByConfirmationNumber(cart.cart.hfd_hold_confirmation_number);
        // Add log for online booking if cart has confirmation number.
        logger.notice('Online Booking API response with confirmation number: @response, Confirmation number @confirmationNumber.', {
          '@response': JSON.stringify(result),
          '@confirmationNumber': cart.cart.hfd_hold_confirmation_number,
        });
      } else {
        // If confirmation number is not there in basket,
        // this means user hadn't reserved any slot earlier.
        // In this case, we fetch all the available slots
        // for the booking and reserve/hold the first available slot from this list.
        // If we have first slot available, we will hold that one.
        result = await this.holdOnlineBookingSlot();
        // Check if the booking is not successful.
        // Set status to online booking as false.
        if (!hasValue(result.status)) {
          setHideOnlineBooking(true);
        }
        // Add log for online booking if cart doesn't have confirmation number.
        logger.notice('Online Booking API response without confirmation number: @response.', {
          '@response': JSON.stringify(result),
        });
      }
    }

    // If there is no api error, then we are rendering the component. Then we
    // trigger the view event.
    if (!hasValue(result.api_error)) {
      const viewDeliveryScheduleEvent = new CustomEvent('viewDeliveryScheduleEvent');
      document.dispatchEvent(viewDeliveryScheduleEvent);
    }

    // Set booking Details response and wait to false.
    this.setState({ bookingDetails: result, wait: false });
  }

  /**
   * Hold new online booking slot for user.
   */
  holdOnlineBookingSlot = async () => {
    let result = { api_error: true, status: false };
    // Get all available slots for booking.
    const availableSlots = await getAvailableBookingSlots();
    if (hasValue(availableSlots.status)) {
      const allAvailableSlots = availableSlots.hfd_time_slots_details;
      if (!hasValue(allAvailableSlots[0])) {
        return result;
      }

      // Loop through all the available slots until we find the suitable slot.
      for (let i = 0; i < allAvailableSlots.length; i++) {
        const [firstSlot] = allAvailableSlots[i].appointment_slots;
        if (hasValue(firstSlot)) {
          const params = {
            resource_external_id: firstSlot.resource_external_id,
            appointment_slot_time: firstSlot.appointment_slot_time,
            appointment_length_time: firstSlot.appointment_length_time,
          };
          // Hold the first slot for user for first time.
          /* eslint-disable no-await-in-loop */
          result = await holdBookingSlot(params);
          // If the slot is available for booking, then proceed. Else, loop
          // again to check for next available slot from the list.
          if (hasValue(result.status)) {
            result = {
              status: true,
              hfd_appointment_details: {
                ...firstSlot,
                hold_confirmation_number: result.hfd_appointment_details.hold_confirmation_number,
                appointment_date: allAvailableSlots[i].appointment_date,
              },
            };
            const { cart, refreshCart } = this.props;
            cart.cart.hfd_hold_confirmation_number = result
              .hfd_appointment_details.hold_confirmation_number;
            refreshCart(cart);
            return result;
          }
        }
      }
    }
    return result;
  }

  /**
   * Reset show-online-booking storage and book the new slot.
   */
  onShippingAddressUpdate = async () => {
    // Add loader until API details are fetched.
    this.setState({ wait: true });
    setHideOnlineBooking(false);
    // Always book new slot for shipping address update.
    const result = await this.holdOnlineBookingSlot();
    // If we do not get successful result,
    // we will hide online booking.
    if (!hasValue(result.status)) {
      setHideOnlineBooking(true);
    }

    // Update online booking component to fetch
    // details again in case there is no error.
    this.setState({ bookingDetails: result, wait: false });
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
    // Add loading on Change Delivery Schedule link.
    elem.classList.add('loading');
    // Get all available slots from the backend via API and set in the states,
    // if API returns status.
    const result = await getAvailableBookingSlots();
    if (hasValue(result.status)) {
      // Update state with the available data.
      this.setState({
        isModalOpen: true,
        availableSlots: result.hfd_time_slots_details,
      });
    }
    // Remove loading on Change Delivery Schedule link.
    elem.classList.remove('loading');
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
  updateScheduleDeliveryChangeInModal = async (
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

    // Preparing the params for holding the appointment.
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
      // Dispatch custom event for change in confirmation number.
      if (result.hfd_appointment_details.hold_confirmation_number
        !== bookingDetails.hfd_appointment_details.hold_confirmation_number) {
        const { cart, refreshCart } = this.props;
        cart.cart.hfd_hold_confirmation_number = result
          .hfd_appointment_details.hold_confirmation_number;
        refreshCart(cart);
      }
    } else if (!hasValue(result.error_code) && !hasValue(result.hfd_appointment_details)) {
      // Check if the status is false and error code is 0
      // We need to show the previous booking details
      // so that user can select different slot.
      result.hfd_appointment_details = bookingDetails.hfd_appointment_details;
    }

    const holdBookingSlotEvent = new CustomEvent('holdBookingSlotEvent', {
      detail: {
        data: {
          status: result.status,
        },
      },
    });
    document.dispatchEvent(holdBookingSlotEvent);

    // Set booking Details response and close the modal.
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
      return (
        <div className="delivery-block-loading">
          <Loading />
        </div>
      );
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
            {hasValue(bookingDetails.hfd_appointment_details) && (
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
                  <span className="online-booking__delivery-icon" />
                  <div className="online-booking__available-delivery">
                    {
                      parse(
                        Drupal.t(
                          'Delivery Scheduled on !appointment_date between !time_slot',
                          {
                            '!appointment_date': `<div class="online-booking__available-delivery-slot"><div class="online-booking__available-delivery-date">
                            ${moment(bookingDetails.hfd_appointment_details.appointment_date).format('DD')}-
                            ${moment(bookingDetails.hfd_appointment_details.appointment_date).locale(drupalSettings.path.currentLanguage).format('MMM')}-
                            ${moment(bookingDetails.hfd_appointment_details.appointment_date).format('YYYY')}
                            </div>`,
                            '!time_slot': `<div class="online-booking__available-delivery-time">${getTranslatedTime(bookingDetails.hfd_appointment_details.start_time)} - ${getTranslatedTime(bookingDetails.hfd_appointment_details.end_time)}</div></div>`,
                          }, { context: 'online_booking' },
                        ),
                      )
                    }
                  </div>
                  <div className="online-booking__change-delivery-schedule" onClick={() => this.openScheduleDeliveryModal()}>
                    {Drupal.t('Change Schedule', {}, { context: 'online_booking' })}
                  </div>
                </div>
                <Popup
                  className="schedule-delivery-calendar-popup"
                  open={isModalOpen}
                  closeOnDocumentClick={false}
                >
                  <>
                    <OnlineBookingCalendar
                      availableSlots={availableSlots}
                      bookingDetails={bookingDetails.hfd_appointment_details}
                      closeScheduleDeliveryModal={this.closeScheduleDeliveryModal}
                      callback={this.updateScheduleDeliveryChangeInModal}
                    />
                  </>
                </Popup>
                <ConditionalView condition={bookingDetails.status}>
                  <div className="online-booking__hold-delivery">
                    <span className="online-booking__hold-delivery-icon" />
                    <div className="online-booking__hold-delivery-text">
                      {
                        parse(
                          Drupal.t(
                            'We will hold this delivery schedule for the next 2 hours',
                            {}, { context: 'online_booking' },
                          ),
                        )
                      }
                    </div>
                  </div>
                </ConditionalView>
                {/**
                 * Placeholder to display the error message when api return success false.
                 */}
                <ConditionalView condition={!bookingDetails.status}>
                  <div className="online-booking__error-message">
                    <span className="online-booking__error-message-icon" />
                    <div className="online-booking__error-message-text">
                      {bookingDetails.error_message}
                    </div>
                  </div>
                </ConditionalView>
                <div className="online-booking__hold-notification">
                  <span className="online-booking__hold-notification-icon" />
                  <div className="online-booking__hold-notification-text">
                    {
                      parse(
                        Drupal.t(
                          'Once the order is placed, changes are not allowed <b>three days</b> before the selected schedule.',
                          {}, { context: 'online_booking' },
                        ),
                      )
                    }
                  </div>
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
                <span className="online-booking__error-message-icon" />
                <div className="online-booking__error-message-text">
                  {
                    Drupal.t(
                      'Online booking: Sorry, something went wrong. Please try again later.',
                      {}, { context: 'online_booking' },
                    )
                  }
                </div>
              </div>
            </ConditionalView>
            <span className="spc-price">{price}</span>
          </label>
        </div>
      </>
    );
  }
}
