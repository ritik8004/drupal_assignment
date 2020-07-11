import React from 'react';
import moment from 'moment-timezone';
import { extendMoment } from 'moment-range';
import { getDateFormat } from '../../../utilities/helper';
import ConditionalView from '../../../common/components/conditional-view';

const momentRange = extendMoment(moment);

export default class AppointmentCalendar extends React.Component {
  constructor(props) {
    super(props);
    const { selectDate } = this.props;
    this.state = {
      week: this.getWeekDates(new Date(selectDate)),
      selectDate: new Date(selectDate),
      arrayOfDates: this.getAllDates(),
    };
  }

  toggleNext = (date) => {
    const nextDate = moment(date).add(1, 'days').format();
    const week = this.getWeekDates(new Date(nextDate));
    this.setState({
      week,
    });
  }

  togglePrev = (date) => {
    const prevDate = moment(date).subtract(1, 'days').format();
    const week = this.getWeekDates(new Date(prevDate));
    this.setState({
      week,
    });
  }

  getWeekDates = (currDate) => {
    const weekdates = [];
    const currentDate = moment(currDate);
    const weekStart = currentDate.clone().startOf('isoWeek');
    for (let i = 0; i <= 6; i++) {
      weekdates.push(moment(weekStart).add(i, 'days').format(getDateFormat()));
    }

    return weekdates;
  }

  getAllDates = () => {
    const startDate = moment().format(getDateFormat());
    const endDate = moment().add('1', 'months').format(getDateFormat());
    const range = momentRange.range(startDate, endDate);
    const arrayOfDates = Array.from(range.by('days'));
    return arrayOfDates;
  }

  dateChanged = (date) => {
    const { dateChanged } = this.props;
    dateChanged(new Date(date));
    this.setState({
      selectDate: new Date(date),
    });
  }

  render() {
    const { week, selectDate, arrayOfDates } = this.state;

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
            <button type="button" className="appointment-calendar-prev-btn" onClick={() => this.togglePrev(week[0])}>{ Drupal.t('Prev') }</button>
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
