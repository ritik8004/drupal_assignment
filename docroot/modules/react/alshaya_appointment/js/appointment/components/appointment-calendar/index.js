import React from 'react';
import moment from 'moment-timezone';

export default class AppointmentCalendar extends React.Component {
  constructor(props) {
    super(props);
    const { selectDate } = this.props;
    this.state = {
      week: this.getWeekDates(new Date(selectDate)),
      selectDate: new Date(selectDate),
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
      weekdates.push(moment(weekStart).add(i, 'days').format('YYYY-MM-DD'));
    }

    return weekdates;
  }

  dateChanged = (date) => {
    const { dateChanged } = this.props;
    dateChanged(new Date(date));
    this.setState({
      selectDate: new Date(date),
    });
  }

  render() {
    const { week, selectDate } = this.state;

    const weekdays = week.map((date) => (
      <li
        className={(moment(selectDate).format('YYYY-MM-DD') === moment(date).format('YYYY-MM-DD')) ? 'date-item active' : 'date-item'}
        onClick={() => this.dateChanged(date)}
      >
        <span className="calendar-day">{moment(date).format('ddd')}</span>
        <span className="calendar-date">{moment(date).format('D')}</span>
      </li>
    ));

    return (
      <div className="appointment-calendar">
        <button type="button" onClick={() => this.togglePrev(week[0])}>{ Drupal.t('Prev') }</button>
        <ul className="calendar-wrapper">
          { weekdays }
        </ul>
        <button type="button" onClick={() => this.toggleNext(week.slice(-1).pop())}>{ Drupal.t('Next') }</button>
      </div>
    );
  }
}
