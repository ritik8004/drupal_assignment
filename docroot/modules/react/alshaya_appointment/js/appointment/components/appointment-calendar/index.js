import React from 'react';
import DatePicker, { registerLocale } from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import moment from 'moment-timezone';
import { extendMoment } from 'moment-range';
import { Swipeable } from 'react-swipeable';
import { getDateFormat } from '../../../utilities/helper';
import ConditionalView from '../../../common/components/conditional-view';
import { showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../js/utilities/strings';
import en from '../../../../../node_modules/date-fns/locale/en-US';
import ar from '../../../../../node_modules/date-fns/locale/ar-SA';
import { smoothScrollToCurrentDate } from '../../../../../js/utilities/smoothScroll';

const momentRange = extendMoment(moment);

export default class AppointmentCalendar extends React.Component {
  constructor(props) {
    super(props);
    const { selectDate } = this.props;
    let previousDisable = false;
    if (moment(selectDate).format(getDateFormat()) === moment().add(1, 'day').format(getDateFormat())) {
      previousDisable = true;
    }
    this.state = {
      week: this.getWeekDates(new Date(selectDate), 'next'),
      selectDate: new Date(selectDate),
      arrayOfDates: this.getAllDates(),
      previousDisabled: previousDisable,
      datePickerToggle: false,
      setOpenDate: new Date(selectDate),
    };
  }

  toggleNext = (date) => {
    const nextDate = moment(date).add(1, 'days').format();
    const week = this.getWeekDates(new Date(nextDate), 'next');
    this.setState({
      week,
      previousDisabled: false,
      setOpenDate: new Date(nextDate),
    });
  }

  togglePrev = (date) => {
    const prevDate = moment(date).subtract(1, 'days').format();
    const week = this.getWeekDates(new Date(prevDate), 'prev');
    this.setState({
      week,
      setOpenDate: new Date(prevDate),
    });
  }

  getWeekDates = (currDate, direction) => {
    let range = [];
    let weekdates = [];

    const startDate = moment(currDate).format(getDateFormat());

    if (direction === 'next') {
      const endDate = moment(currDate).add('6', 'days').format(getDateFormat());
      range = momentRange.range(startDate, endDate);
    } else {
      const endDate = moment(currDate).subtract('6', 'days').format(getDateFormat());
      range = momentRange.range(endDate, startDate);

      // Reset if any date from past.
      weekdates = Array.from(range.by('days'));
      let flag = false;
      weekdates.some((item) => {
        if (item.isBefore()) {
          flag = true;
          return flag;
        }
        return false;
      });
      if (flag) {
        const newStartDate = moment().add(1, 'day').format(getDateFormat());
        const newEndDate = moment(newStartDate).add('6', 'days').format(getDateFormat());
        range = momentRange.range(newStartDate, newEndDate);
        this.setState({
          previousDisabled: true,
        });
      }
    }

    weekdates = Array.from(range.by('days'));
    return weekdates;
  }

  getAllDates = () => {
    const startDate = moment().add(1, 'day').format(getDateFormat());
    const endDate = moment().add('6', 'months').format(getDateFormat());
    const range = momentRange.range(startDate, endDate);
    const arrayOfDates = Array.from(range.by('days'));
    return arrayOfDates;
  }

  dateChanged = (date) => {
    showFullScreenLoader();
    const { dateChanged } = this.props;
    dateChanged(new Date(date));
    this.setState({
      selectDate: new Date(date),
      setOpenDate: new Date(date),
    });
  };

  /**
   * Set mini calendar date and get time slots for selected date.
   */
  datePickerChanged = (date) => {
    showFullScreenLoader();
    const { dateChanged } = this.props;
    // Set mini calendar.
    dateChanged(new Date(date));
    this.setState({
      selectDate: new Date(date),
      week: this.getWeekDates(new Date(date), 'next'),
      previousDisabled: false,
      setOpenDate: new Date(date),
    }, () => {
      smoothScrollToCurrentDate();
    });
    this.hideDatePicker();
  };

  /**
   * Show / hide full calendar on month click.
   */
  showDatePicker = () => {
    const { datePickerToggle } = this.state;
    if (!datePickerToggle) {
      this.setState({
        datePickerToggle: true,
      });
    }
  };

  hideDatePicker = () => {
    const { datePickerToggle } = this.state;
    if (datePickerToggle) {
      this.setState({
        datePickerToggle: false,
      });
    }
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

  render() {
    const {
      week,
      selectDate,
      arrayOfDates,
      previousDisabled,
      datePickerToggle,
      setOpenDate,
    } = this.state;

    const weekdays = week.map((date, i) => (
      <li
        key={i.toString()}
        className={(moment(selectDate).format(getDateFormat()) === moment(date).format(getDateFormat())) ? 'date-item active' : 'date-item'}
        onClick={() => this.dateChanged(date)}
      >
        <span className="calendar-day">{moment(date).format('ddd')}</span>
        <span className="calendar-date">{moment(date).format('D')}</span>
      </li>
    ));

    const allDates = arrayOfDates.map((date, i) => (
      <li
        key={i.toString()}
        className={(moment(selectDate).format(getDateFormat()) === moment(date).format(getDateFormat())) ? 'date-item active' : 'date-item'}
        onClick={() => this.dateChanged(date)}
      >
        <span className="calendar-day">{moment(date).format('ddd')}</span>
        <span className="calendar-date">{moment(date).format('D')}</span>
      </li>
    ));

    // Set language for datepicker translation.
    if (drupalSettings.path.currentLanguage !== 'en') {
      registerLocale('ar', ar);
    } else {
      registerLocale('en', en);
    }
    // Set wrapper element direction for arabic.
    const dir = (drupalSettings.path.currentLanguage !== 'en') ? 'rtl' : 'ltr';

    return (
      <>
        <div className="appointment-datepicker__header">
          <span className="month-calendar-sides previous">
            { moment(setOpenDate).subtract('1', 'month').format('MMMM') }
          </span>
          <button
            type="button"
            className={(datePickerToggle) ? 'month-calendar-datepicker active' : 'month-calendar-datepicker inactive'}
            onClick={() => this.showDatePicker()}
          >
            { moment(setOpenDate).format('MMMM') }
          </button>
          <span className="month-calendar-sides next">
            { moment(setOpenDate).add('1', 'month').format('MMMM') }
          </span>
        </div>
        { datePickerToggle
          && (
          <Swipeable
            onSwipedLeft={() => (((drupalSettings.path.currentLanguage === 'en')) ? this.swipedLeft() : this.swipedRight())}
            onSwipedRight={() => (((drupalSettings.path.currentLanguage === 'en')) ? this.swipedRight() : this.swipedLeft())}
            preventDefaultTouchmoveEvent
          >
            <div className="month-picker-wrapper" dir={dir}>
              <DatePicker
                selected={selectDate}
                inline
                minDate={moment().add('1', 'day').toDate()}
                onSelect={(date) => this.datePickerChanged(date)}
                locale={(drupalSettings.path.currentLanguage !== 'en') ? 'ar' : 'en'}
                openToDate={setOpenDate}
                useWeekdaysShort
                onMonthChange={this.handleMonthChange}
                disabledKeyboardNavigation
                maxDate={moment().add('6', 'months').toDate()}
              />
            </div>
          </Swipeable>
          )}
        <ConditionalView condition={window.innerWidth > 767}>
          { !datePickerToggle
            && (
            <div className="appointment-calendar daypicker-desktop">
              <button
                type="button"
                className="appointment-calendar-prev-btn"
                disabled={(previousDisabled)}
                onClick={() => this.togglePrev(week[0])}
              >
                { getStringMessage('prev') }
              </button>
              <ul className="calendar-wrapper">
                { weekdays }
              </ul>
              <button type="button" className="appointment-calendar-next-btn" onClick={() => this.toggleNext(week.slice(-1).pop())}>{ getStringMessage('next') }</button>
            </div>
            )}
        </ConditionalView>
        <ConditionalView condition={window.innerWidth < 768}>
          { !datePickerToggle
          && (
          <div className="appointment-calendar daypicker-mobile">
            <ul className="calendar-wrapper">
              { allDates }
            </ul>
          </div>
          )}
        </ConditionalView>
      </>
    );
  }
}
