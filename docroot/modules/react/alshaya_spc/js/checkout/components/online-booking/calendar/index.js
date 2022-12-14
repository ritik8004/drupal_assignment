import React from 'react';
import DatePicker, { registerLocale } from 'react-datepicker';
import moment from 'moment-timezone';
import { Swipeable } from 'react-swipeable';
import en from '../../../../../../node_modules/date-fns/locale/en-US';
import ar from '../../../../../../node_modules/date-fns/locale/ar-SA';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getTranslatedTime } from '../../../../../../js/utilities/onlineBookingHelper';

export default class OnlineBookingCalendar extends React.Component {
  constructor(props) {
    super(props);
    const { bookingDetails } = this.props;

    // Get the current booking date from the provided booking details else
    // consider the current date.
    const selectedDate = typeof bookingDetails.appointment_date !== 'undefined'
      ? new Date(bookingDetails.appointment_date)
      : new Date();

    this.state = {
      // selectedDate is used for keeping the current selected booking date in
      // calendar. This will change upon users selection/change of the date
      // from calendar. We will always receive this from parent component.
      selectedDate: new Date(selectedDate),
      // setOpenDate is used to keep the first opening date in state and will
      // change only with swipe actions to change the month.
      setOpenDate: new Date(selectedDate),
      // This variable keep the available time slots for the current selected
      // date in calendar and will change on each available booking date change.
      availableTimeSlots: this.getTimeSlotsForDate(selectedDate),
      // Set the current time slot identifier in the state. This will update
      // everytime when customer change the timeslot from the list of available
      // time slots. We are using combination of 'appointment_slot_time' and
      // 'start_time' key as a unique identifier for the time slot.
      selectedTimeSlot: typeof bookingDetails.appointment_date !== 'undefined'
        ? this.getSelectedTimeSlotKey(
          bookingDetails.appointment_date,
          bookingDetails.start_time,
        )
        : null,
      // This state is used to enable or disable apply/submit button in
      // calendar after selecting a different timeslot. This is to force
      // customer selecting a time slot below. If the default is selected,
      // button will still remain in disable state.
      disableApplyBtn: true,
    };
  }

  /**
   * Function returns a date format used for conditional processing of dates.
   *
   * @returns {string}
   *  A date format for processing dates.
   */
  dateFormat = () => 'YYYY-MM-DD';

  /**
   * This function creates a unique timeslot identifier with the combination
   * of given date and start time of the booking.
   *
   * @param {string} date
   *  Provide the booking date in string format.
   * @param {string} startTime
   *  Provide the start time of the booking slot in string format.
   *
   * @returns {string}
   *  A unique identifier for the time slot based on given values.
   */
  getSelectedTimeSlotKey = (date, startTime) => (
    `${moment(date).format(this.dateFormat())}${startTime}`
  );

  /**
   * Prepare a array of date slots objects to show in calendar.
   *
   * @returns {array}
   *  Array of available date slots.
   */
  getAvailableBookingDates = () => {
    // Get all available booking slots from the props.
    const { availableSlots } = this.props;
    let bookingDates = [];
    if (typeof availableSlots !== 'undefined' && availableSlots.length > 0) {
      bookingDates = availableSlots.map((daySlot) => (
        new Date(daySlot.appointment_date)
      ));
    }
    return bookingDates;
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
    // Get all available booking slots from the props.
    const { availableSlots } = this.props;
    let timeSlotsForDate = [];
    if (typeof availableSlots !== 'undefined' && availableSlots.length > 0) {
      availableSlots.forEach((daySlot) => {
        // Get the time slots if the given date is matched.
        if (moment(date).format(this.dateFormat())
          === moment(daySlot.appointment_date).format(this.dateFormat())) {
          timeSlotsForDate = daySlot.appointment_slots;
        }
      });
    }
    return timeSlotsForDate;
  };

  /**
   * Set the calendar date and get time slots for selected date.
   */
  onDateChanged = (date) => {
    const { bookingDetails } = this.props;
    this.setState({
      selectedDate: new Date(date),
      // Get the available time slots for the new date.
      availableTimeSlots: this.getTimeSlotsForDate(date),
      // Reset the time slot identifier everytime date is changed.
      selectedTimeSlot: typeof bookingDetails.appointment_date !== 'undefined'
        ? this.getSelectedTimeSlotKey(
          bookingDetails.appointment_date,
          bookingDetails.start_time,
        )
        : null,
      // Disable apply/submit button when date changed. This is to force
      // customer selecting a time slot below. If the default is selected,
      // button will still remain in disable state.
      disableApplyBtn: true,
    });
  };

  /**
   * Change the month in state on change in calendar to sync custom functions.
   * This is a react datepicker month change handler.
   *
   * @param {string} monthBeingViewed
   */
  onMonthChange = (monthBeingViewed) => {
    this.setState({
      setOpenDate: new Date(moment(monthBeingViewed)),
    });
  };

  /**
   * Change the current month to either next or previous month based on the
   * action. Default to increase month and `decrease` action will reduce month.
   */
  changeMonthDisplay = (action = 'increase') => {
    const { setOpenDate } = this.state;

    // For increasing month current display month shouldn't be same as the min
    // date boundary month. We shouldn't allow increasing month beyond min date.
    if (action === 'increase'
      && moment(setOpenDate).isSame(this.getMinMaxDateForCalendar('max'), 'month')) {
      return;
    }

    // For decreasing month current display month shouldn't be same as the min
    // date boundary month. We shouldn't allow decreasing month beyond min date.
    if (action === 'decrease'
      && moment(setOpenDate).isSame(this.getMinMaxDateForCalendar(), 'month')) {
      return;
    }

    // For default action increase the month display, we will set the open date
    // of next month and for the decrease the month display we will set the open
    // date to the previous month.
    this.setState({
      setOpenDate: (action === 'decrease')
        ? new Date(moment(setOpenDate).subtract(1, 'month'))
        : new Date(moment(setOpenDate).add(1, 'month')),
    });
  };

  /**
   * Get the minimun and maximun date boundries for the calendar. Default this
   * will return the minimum date boundry. `max` parameter will return the max
   * date boundry.
   */
  getMinMaxDateForCalendar = (boundry = 'min') => {
    // Get all available booking slots from the props.
    const { availableSlots } = this.props;

    // First check if we have booking slots are available at all.
    if (typeof availableSlots !== 'undefined'
      && availableSlots.length > 0) {
      // Last date from the available booking slots is the maximum date for
      // the calendar.
      if (boundry === 'max') {
        const lastSlotIndex = parseInt(availableSlots.length, 10) - 1;
        return new Date(availableSlots[lastSlotIndex].appointment_date);
      }

      // First date from the available booking slots is the minimum date for
      // the calendar.
      return new Date(availableSlots[0].appointment_date);
    }

    // If no booking slots are available then return today's date.
    return new Date();
  };

  /**
   * This is to change the apply button state when user selects a specific
   * time slot by clicking it.
   *
   * @param {string} startTime
   *  Selected time slot start_time value as to generate unique identifier.
   */
  onTimeSlotChange = (e, startTime) => {
    e.preventDefault();
    const { bookingDetails } = this.props;
    const { selectedDate } = this.state;

    // Get time slot based on the available date and start time.
    const selectedTimeSlot = this.getSelectedTimeSlotKey(
      selectedDate,
      startTime,
    );
    // If selected time slot is different then allow customer to use apply
    // button and change the schedule.
    this.setState({
      // Change the selected time slot ID with the clicked time slot ID.
      selectedTimeSlot,
      // Check if the selected time slot is similar to current booking details,
      // and keep apply button disabled.
      disableApplyBtn: (this.getSelectedTimeSlotKey(
        bookingDetails.appointment_date,
        bookingDetails.start_time,
      ) === selectedTimeSlot),
    });
  };

  /**
   * This is to handle the apply button action with selected time slot.
   */
  onApplyTimeSlot = (e) => {
    // Prevent default click handlers.
    e.preventDefault();

    // Check for the callback in props and trigger it with the params.
    const { callback } = this.props;
    if (typeof callback !== 'undefined') {
      const {
        selectedDate,
        selectedTimeSlot,
        availableTimeSlots,
      } = this.state;

      // Get the seleccted booking slot details.
      const selectedTimeSlotDetails = availableTimeSlots.find(
        (timeSlot) => this.getSelectedTimeSlotKey(
          selectedDate,
          timeSlot.start_time,
        ) === selectedTimeSlot,
      );

      // Trigger callback function for the parent component to do the necessary
      // actions/operations with selected time slot details.
      if (hasValue(selectedTimeSlotDetails)) {
        callback(
          moment(selectedDate).format(this.dateFormat()),
          selectedTimeSlotDetails,
        );
      }
    }
  };

  render() {
    const {
      selectedDate,
      setOpenDate,
      availableTimeSlots,
      selectedTimeSlot,
      disableApplyBtn,
    } = this.state;
    const { closeScheduleDeliveryModal } = this.props;

    // Set language for datepicker translation. Default to english. If it's not
    // english then change to arabic.
    registerLocale('en', en);
    let dir = 'ltr';
    if (drupalSettings.path.currentLanguage !== 'en') {
      registerLocale('ar', ar);
      // Set wrapper element direction for arabic.
      dir = 'rtl';
    }

    // Prepare time slots list items for the current selected date in calendar.
    let timeSlotListItems = null;
    if (availableTimeSlots.length > 0) {
      timeSlotListItems = availableTimeSlots.map((timeSlot) => {
        const className = (this.getSelectedTimeSlotKey(
          selectedDate,
          timeSlot.start_time,
        ) === selectedTimeSlot)
          ? 'timeslots-options-list-item active'
          : 'timeslots-options-list-item';

        let element = null;
        element = (
          <div
            key={timeSlot.appointment_slot_time}
            className={className}
            onClick={(e) => this.onTimeSlotChange(
              e,
              timeSlot.start_time,
            )}
          >
            {`${getTranslatedTime(timeSlot.start_time)} - ${getTranslatedTime(timeSlot.end_time)}`}
          </div>
        );
        return element;
      });
    }

    // Add datepicker class depend on date selected.
    let datepickerMonthLeft = 'datepicker-month-left';
    let datepickerMonthRight = 'datepicker-month-right';
    if (moment(setOpenDate).isSame(this.getMinMaxDateForCalendar(), 'month')) {
      datepickerMonthLeft = 'datepicker-month-left disabled';
    }
    if (moment(setOpenDate).isSame(this.getMinMaxDateForCalendar('max'), 'month')) {
      datepickerMonthRight = 'datepicker-month-right disabled-right';
    }

    return (
      <>
        <div className="schedule-delivery-datepicker__wrapper">
          <div className="schedule-delivery-datepicker__header">
            <div className="popup-heading">
              {Drupal.t(
                'Schedule Your Delivery',
                {},
                { context: 'online_booking' },
              )}
            </div>
            <div
              className="close"
              onClick={() => closeScheduleDeliveryModal()}
            />
          </div>
          <div className="schedule-delivery-datepicker__main">
            <Swipeable
              onSwipedLeft={() => (((drupalSettings.path.currentLanguage === 'en')) ? this.changeMonthDisplay() : this.changeMonthDisplay('decrease'))}
              onSwipedRight={() => (((drupalSettings.path.currentLanguage === 'en')) ? this.changeMonthDisplay('decrease') : this.changeMonthDisplay())}
              preventDefaultTouchmoveEvent
            >
              <div className="datetime-picker-wrapper" dir={dir}>
                <div
                  className={datepickerMonthLeft}
                  onClick={() => this.changeMonthDisplay('decrease')}
                  disabled={moment(setOpenDate).isSame(
                    this.getMinMaxDateForCalendar(),
                    'month',
                  )}
                >
                  <span>{'<'}</span>
                </div>
                <DatePicker
                  renderCustomHeader={({
                    date,
                    prevMonthButtonDisabled,
                    nextMonthButtonDisabled,
                  }) => (
                    <>
                      <div className="datepicker-heading">{Drupal.t('Delivery Date', {}, { context: 'online_booking' })}</div>
                      <div className="datepicker-month-select">
                        <div className="datepicker-month-calendar-sides">
                          {/**
                           * Customise the calendar header to show previous,
                           * current and next months name in the header with
                           * actions to switch month previous and next.
                           */}
                          <span
                            className={prevMonthButtonDisabled ? 'month-calendar-sides previous disabled' : 'month-calendar-sides previous'}
                            disabled={prevMonthButtonDisabled}
                          >
                            {moment(date).subtract('1', 'month').locale(drupalSettings.path.currentLanguage).format('MMMM')}
                          </span>
                          <span className="month-calendar-datepicker current">
                            {`${moment(date).locale(drupalSettings.path.currentLanguage).format('MMMM')} ${moment(date).format('YYYY')}`}
                          </span>
                          <span
                            className={nextMonthButtonDisabled ? 'month-calendar-sides next disabled' : 'month-calendar-sides next'}
                            disabled={nextMonthButtonDisabled}
                          >
                            {moment(date).add('1', 'month').locale(drupalSettings.path.currentLanguage).format('MMMM')}
                          </span>
                        </div>
                      </div>
                    </>
                  )}
                  selected={selectedDate}
                  minDate={this.getMinMaxDateForCalendar()}
                  maxDate={this.getMinMaxDateForCalendar('max')}
                  inline
                  onSelect={(date) => this.onDateChanged(date)}
                  onMonthChange={this.onMonthChange}
                  locale={drupalSettings.path.currentLanguage}
                  openToDate={setOpenDate}
                  disabledKeyboardNavigation
                  includeDates={this.getAvailableBookingDates()}
                  formatWeekDay={(nameOfDay) => nameOfDay.substr(0, 3)}
                />
                {/**
                 * Add two arrow icons for controlling the months increase
                 * and decrease action.
                 */}

                <div
                  className={datepickerMonthRight}
                  onClick={() => this.changeMonthDisplay()}
                  disabled={moment(setOpenDate).isSame(
                    this.getMinMaxDateForCalendar('max'),
                    'month',
                  )}
                >
                  <span>{'>'}</span>
                </div>
              </div>
            </Swipeable>
          </div>
          <div className="timeslots-selection-wrapper" dir={dir}>
            <div className="timeslots-selection-heading">{Drupal.t('Delivery Time', {}, { context: 'online_booking' })}</div>
            <div className="timeslots-selection-options">
              <div className="timeslots-options-list">
                <div className="timeslots-options-list-items">
                  {timeSlotListItems}
                </div>
              </div>
            </div>
          </div>
          <div className="schedule-delivery-datepicker__footer">
            <a
              className={disableApplyBtn ? 'schedule-delivery-datepicker-submit disabled-btn' : 'schedule-delivery-datepicker-submit'}
              disabled={disableApplyBtn}
              onClick={(e) => this.onApplyTimeSlot(e)}
            >
              {Drupal.t('Apply Date & Time', {}, { context: 'online_booking' })}
            </a>
          </div>
        </div>
      </>
    );
  }
}
