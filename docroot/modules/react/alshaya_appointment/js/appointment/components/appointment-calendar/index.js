import React from 'react';
import moment from 'moment-timezone';
import { extendMoment } from 'moment-range';
import { getDateFormat } from '../../../utilities/helper';
import ConditionalView from '../../../common/components/conditional-view';
import { showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';

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
    };
  }

  toggleNext = (date) => {
    const nextDate = moment(date).add(1, 'days').format();
    const week = this.getWeekDates(new Date(nextDate), 'next');
    this.setState({
      week,
      previousDisabled: false,
    });
  }

  togglePrev = (date) => {
    const prevDate = moment(date).subtract(1, 'days').format();
    const week = this.getWeekDates(new Date(prevDate), 'prev');
    this.setState({
      week,
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
    });
  };

  render() {
    const {
      week, selectDate, arrayOfDates, previousDisabled,
    } = this.state;

    const weekdays = week.map((date) => (
      <li
        className={(moment(selectDate).format(getDateFormat()) === moment(date).format(getDateFormat())) ? 'date-item active' : 'date-item'}
        onClick={() => this.dateChanged(date)}
      >
        <span className="calendar-day">{moment(date).format('ddd')}</span>
        <span className="calendar-date">{moment(date).format('D')}</span>
      </li>
    ));

    const allDates = arrayOfDates.map((date) => (
      <li
        className={(moment(selectDate).format(getDateFormat()) === moment(date).format(getDateFormat())) ? 'date-item active' : 'date-item'}
        onClick={() => this.dateChanged(date)}
      >
        <span className="calendar-day">{moment(date).format('ddd')}</span>
        <span className="calendar-date">{moment(date).format('D')}</span>
      </li>
    ));

    return (
      <>
        <ConditionalView condition={window.innerWidth > 1023}>
          <div className="appointment-calendar daypicker-desktop">
            <button
              type="button"
              className="appointment-calendar-prev-btn"
              disabled={(previousDisabled)}
              onClick={() => this.togglePrev(week[0])}
            >
              { Drupal.t('Prev') }
            </button>
            <ul className="calendar-wrapper">
              { weekdays }
            </ul>
            <button type="button" className="appointment-calendar-next-btn" onClick={() => this.toggleNext(week.slice(-1).pop())}>{ Drupal.t('Next') }</button>
          </div>
        </ConditionalView>
        <ConditionalView condition={window.innerWidth < 1024}>
          <div className="appointment-calendar daypicker-mobile">
            <ul className="calendar-wrapper">
              { allDates }
            </ul>
          </div>
        </ConditionalView>
      </>
    );
  }
}
