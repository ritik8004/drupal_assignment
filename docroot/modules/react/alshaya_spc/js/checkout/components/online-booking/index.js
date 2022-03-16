import React from 'react';
import Popup from 'reactjs-popup';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import
{
  getBookingDetailByConfirmationNumber,
  getAvailableBookingSlots,
  holdBookingSlot,
} from '../../../../../js/utilities/onlineBookingHelper';
import Loading from '../../../utilities/loading';
import DefaultShippingElement from '../shipping-method/components/DefaultShippingElement';
import OnlineBookingCalendar from './calendar';
import ConditionalView from '../../../common/components/conditional-view';

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
    };
  }

  componentDidMount = async () => {
    const { cart } = this.props;
    let result = { error: true };
    if (this.checkHomeDelivery(cart) && hasValue(cart.cart.shipping.methods)) {
      result = await getAvailableBookingSlots();
      if (!hasValue(result.error)) {
        // Check if the cart is having confirmation number.
        if (hasValue(cart.confirmation_number)) {
          result = await getBookingDetailByConfirmationNumber(cart.confirmation_number);
        } else {
          // If confirmation number is not there in basket,
          // this means user hadn't reserved any slot earlier.
          // In this case, we fetch all the available slots
          // for the booking and reserve/hold the first available slot from this list.
          // If we have first slot available, we will hold that one.
          const [availableSlot] = result.availableSlots;
          const [firstSlot] = availableSlot.appointment_slots;
          if (hasValue(firstSlot)) {
            const params = {
              resource_external_id: firstSlot.resource_external_id,
              appointment_date_time: firstSlot.appointment_date_time,
            };
            result = await holdBookingSlot(params);
            if (hasValue(result.success)) {
              result = {
                ...firstSlot,
                ...result,
                appointment_date: availableSlot.appointment_date,
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

  render() {
    const {
      wait,
      bookingDetails,
      isModalOpen,
    } = this.state;
    const { price, method } = this.props;

    if (wait) {
      return <Loading />;
    }

    // If there is a failure during the API call, we will render the existing component.
    if (bookingDetails.error) {
      return <DefaultShippingElement method={method} price={price} />;
    }

    return (
      <>
        <div className="online-booking">
          <label className="radio-sim radio-label">
            <span className="carrier-title">{Drupal.t('Delivery Schedule', {}, { context: 'online_booking' })}</span>
            <span className="online-booking-title">{Drupal.t('All items in cart are delivered on your preferred date', {}, { context: 'online_booking' })}</span>
            <span className="available-delivery">
              {Drupal.t('Earliest available delivery on @appointment_date between @start_time - @end_time', {
                '@appointment_date': bookingDetails.appointment_date,
                '@start_time': bookingDetails.start_time,
                '@end_time': bookingDetails.end_time,
              }, { context: 'online_booking' })}
            </span>
            <span className="change-delivery-schedule"><a href="#" onClick={() => this.openScheduleDeliveryModal()}>{Drupal.t('Change Delivery Schedule', {}, { context: 'online_booking' })}</a></span>
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
                  selectedDate={bookingDetails.appointment_date}
                  closeScheduleDeliveryModal={this.closeScheduleDeliveryModal}
                  bookingDetails={bookingDetails}
                />
              </>
            </Popup>
            <ConditionalView condition={bookingDetails.success}>
              <span className="hold-delivery">{Drupal.t('We will hold this delivery schedule for next 2 hours', {}, { context: 'online_booking' })}</span>
            </ConditionalView>
            <ConditionalView condition={!bookingDetails.success}>
              <span className="booking-error-message">{bookingDetails.message}</span>
            </ConditionalView>
            <span className="hold-notification">{Drupal.t('Once the order is placed, changes are not allowed three days before the selected schedule.', {}, { context: 'online_booking' })}</span>
            <span className="spc-price">{price}</span>
          </label>
        </div>
      </>
    );
  }
}
