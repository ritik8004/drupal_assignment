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
    const { selectedDate } = this.props;

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
    };
  }

  /**
   * Array of available_time_slots from get time slot API. We will get this
   * in props once the API integration is done.
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
   * Set the calendar date and get time slots for selected date.
   */
  onDateChanged = (date) => {
    this.setState({
      selectedDate: new Date(date),
      availableTimeSlots: this.getTimeSlotsForDate(date),
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

  /**
   * Prepare a array of date slots objects to show in calendar.
   *
   * @returns {array}
   *  Array of available date slots.
   */
  getAvailableBookingDates = () => {
    let bookingDates = [];
    const bookingSlots = this.getHFDBookingTimeSlots();
    if (typeof bookingSlots !== 'undefined' && bookingSlots.length > 0) {
      bookingDates = bookingSlots.map((daySlot) => (
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
    let timeSlotsForDate = [];
    const dateFormat = 'YYYY-MM-DD';
    const bookingSlots = this.getHFDBookingTimeSlots();
    if (typeof bookingSlots !== 'undefined' && bookingSlots.length > 0) {
      bookingSlots.forEach((daySlot) => {
        // Get the time slots if the given date is matched.
        if (moment(date).format(dateFormat)
          === moment(daySlot.appointment_date).format(dateFormat)) {
          timeSlotsForDate = daySlot.appointment_slots;
        }
      });
    }
    return timeSlotsForDate;
  };

  render() {
    const {
      selectedDate,
      setOpenDate,
      availableTimeSlots,
    } = this.state;
    const { closeScheduleDeliveryModal } = this.props;

    // Set language for datepicker translation. Default to english. If it's not
    // english then change to arabic.
    registerLocale('en', en);
    if (drupalSettings.path.currentLanguage !== 'en') {
      registerLocale('ar', ar);
    }

    // Set wrapper element direction for arabic.
    const dir = (drupalSettings.path.currentLanguage !== 'en') ? 'rtl' : 'ltr';

    // Prepare time slots list items for the current selected date in calendar.
    let timeSlotListItems = null;
    if (availableTimeSlots.length > 0) {
      timeSlotListItems = availableTimeSlots.map((timeSlot) => {
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
              {Drupal.t('Schedule Your Delivery', {}, { context: 'online booking' })}
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
                        {/**
                         * Customise the calendar header to show previous,
                         * current and next months name in the header with
                         * actions to switch month previous and next.
                         */}
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
                  selected={selectedDate}
                  inline
                  onSelect={(date) => this.onDateChanged(date)}
                  locale={(drupalSettings.path.currentLanguage !== 'en') ? 'ar' : 'en'}
                  openToDate={setOpenDate}
                  disabledKeyboardNavigation
                  includeDates={this.getAvailableBookingDates()}
                />
              </div>
            </Swipeable>
            <div className="timeslots-selection-wrapper" dir={dir}>
              <div className="timeslots-selection-heading">{Drupal.t('Delivery Time', {}, { context: 'online booking' })}</div>
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
              {Drupal.t('Apply Date & Time', {}, { context: 'online booking' })}
            </button>
          </div>
        </div>
      </>
    );
  }
}
