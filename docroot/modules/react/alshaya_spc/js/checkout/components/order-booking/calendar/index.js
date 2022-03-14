import React from 'react';
import DatePicker, { registerLocale } from 'react-datepicker';
import '../../../../../../node_modules/react-datepicker/dist/react-datepicker.css';
import moment from 'moment-timezone';
import { Swipeable } from 'react-swipeable';
import en from '../../../../../../node_modules/date-fns/locale/en-US';
import ar from '../../../../../../node_modules/date-fns/locale/ar-SA';

export default class OrderBookingCalendar extends React.Component {
  constructor(props) {
    super(props);
    const { selectDate } = this.props;

    this.state = {
      selectDate: new Date(selectDate),
      setOpenDate: new Date(selectDate),
      orderBookingDates: this.getOrderBookingDates(),
      orderBookingTimeSlots: this.getTimeSlotsForDate(selectDate),
    };
  }

  /**
   * Return date format to be used in calendar.
   *
   * @returns {string}
   *  String date format.
   */
  getDateFormat = () => ('YYYY-MM-DD');

  /**
   * Set the calendar date and get time slots for selected date.
   */
  datePickerChanged = (date) => {
    this.setState({
      selectDate: new Date(date),
      orderBookingTimeSlots: this.getTimeSlotsForDate(date),
    });
  };

  swipedLeft = () => {
    const { setOpenDate } = this.state;
    this.setState({
      setOpenDate: new Date(moment(setOpenDate).add(1, 'month')),
    });
  };

  swipedRight = () => {
    const { setOpenDate } = this.state;
    this.setState({
      setOpenDate: new Date(moment(setOpenDate).subtract(1, 'month')),
    });
  };

  handleMonthChange = (monthBeingViewed) => {
    this.setState({
      setOpenDate: new Date(moment(monthBeingViewed)),
    });
  };

  /**
   * Prepare a array of date slots objects to show in calendar.
   *
   * @returns {array}
   *  Array of available date slots.
   */
  getOrderBookingDates = () => {
    let orderBookingDates = [];
    const { bookingSlots } = this.props;
    if (typeof bookingSlots !== 'undefined' && bookingSlots.length > 0) {
      orderBookingDates = bookingSlots.map((daySlot) => (
        new Date(daySlot.appointment_date)
      ));
    }
    return orderBookingDates;
  };

  /**
   * Get the available time slots for the given date.
   *
   * @param {object} date
   *  Date for time slots to return.
   *
   * @returns {array}
   *  Array of time slots for the given date.
   */
  getTimeSlotsForDate = (date) => {
    let timeSlotsForDate = [];
    const { bookingSlots } = this.props;
    bookingSlots.forEach((daySlot) => {
      // Get the time slots if the given date is matched.
      if (moment(date).format(this.getDateFormat())
        === moment(daySlot.appointment_date).format(this.getDateFormat())) {
        timeSlotsForDate = daySlot.appointment_slots;
      }
    });
    return timeSlotsForDate;
  };

  render() {
    const {
      selectDate,
      setOpenDate,
      orderBookingDates,
      orderBookingTimeSlots,
    } = this.state;
    const { closeScheduleDeliveryModal } = this.props;

    // Set language for datepicker translation.
    if (drupalSettings.path.currentLanguage !== 'en') {
      registerLocale('ar', ar);
    } else {
      registerLocale('en', en);
    }
    // Set wrapper element direction for arabic.
    const dir = (drupalSettings.path.currentLanguage !== 'en') ? 'rtl' : 'ltr';

    // Prepare time slots list items for the current selected date in calendar.
    let timeSlotListItems = null;
    if (orderBookingTimeSlots.length > 0) {
      timeSlotListItems = orderBookingTimeSlots.map((timeSlot) => {
        let element = null;
        element = (
          <li
            key={timeSlot.resource_external_id}
            value={timeSlot.resource_external_id}
          >
            {`${timeSlot.start_time} - ${timeSlot.end_time}`}
          </li>
        );
        return element;
      });
    }

    return (
      <>
        <div className="schedule-delivery-datepicker__wrapper">
          <div className="schedule-delivery-datepicker__header">
            <span className="popup-heading">
              {Drupal.t('Schedule Your Delivery')}
            </span>
            <span
              className="popup-close-icon"
              onClick={() => closeScheduleDeliveryModal()}
            >
              {Drupal.t('close')}
            </span>
          </div>
          <div className="schedule-delivery-datepicker__main">
            <Swipeable
              onSwipedLeft={() => (((drupalSettings.path.currentLanguage === 'en')) ? this.swipedLeft() : this.swipedRight())}
              onSwipedRight={() => (((drupalSettings.path.currentLanguage === 'en')) ? this.swipedRight() : this.swipedLeft())}
              preventDefaultTouchmoveEvent
            >
              <div className="datetime-picker-wrapper" dir={dir}>
                <DatePicker
                  renderCustomHeader={({
                    date,
                    decreaseMonth,
                    increaseMonth,
                    prevMonthButtonDisabled,
                    nextMonthButtonDisabled,
                  }) => (
                    <>
                      <div className="datepicker-heading">{Drupal.t('Delivery Date')}</div>
                      <div className="datepicker-month-calendar-sides">
                        <span
                          className="month-calendar-sides previous"
                          onClick={decreaseMonth}
                          disabled={prevMonthButtonDisabled}
                        >
                          {moment(date).subtract('1', 'month').format('MMMM')}
                        </span>
                        <span className="month-calendar-datepicker current">
                          {moment(date).format('MMMM YYYY')}
                        </span>
                        <span
                          className="month-calendar-sides next"
                          onClick={increaseMonth}
                          disabled={nextMonthButtonDisabled}
                        >
                          {moment(date).add('1', 'month').format('MMMM')}
                        </span>
                      </div>
                    </>
                  )}
                  selected={selectDate}
                  inline
                  onSelect={(date) => this.datePickerChanged(date)}
                  locale={(drupalSettings.path.currentLanguage !== 'en') ? 'ar' : 'en'}
                  openToDate={setOpenDate}
                  onMonthChange={this.handleMonthChange}
                  disabledKeyboardNavigation
                  includeDates={orderBookingDates}
                />
              </div>
            </Swipeable>
            <div className="timeslots-selection-wrapper" dir={dir}>
              <div className="timeslots-selection-heading">{Drupal.t('Delivery Time')}</div>
              <div className="timeslots-selection-options">
                <ul className="timeslots-options-list">{timeSlotListItems}</ul>
              </div>
            </div>
          </div>
          <div className="schedule-delivery-datepicker__footer">
            <button
              type="button"
              className="schedule-delivery-datepicker-submit"
            >
              {Drupal.t('Apply Date & Time')}
            </button>
          </div>
        </div>
      </>
    );
  }
}
