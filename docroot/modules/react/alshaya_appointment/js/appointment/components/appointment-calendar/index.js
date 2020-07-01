import React from 'react';
import moment from "moment-timezone";

export default class AppointmentCalendar extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      weekdays: '',
      week: this.getweekdates(new Date(this.props.selectDate)),
      selectDate: new Date(this.props.selectDate),
    };
  }

  toggleNext = (date) => {
    date = moment(date).add(1, 'days').format();
    const week = this.getweekdates(new Date(date));
    this.setState({
      week: week,
    });
  }

  togglePrev = (date) => {
    date = moment(date).subtract(1, 'days').format();
    const week = this.getweekdates(new Date(date));
    this.setState({
      week: week,
    });
  }

  getweekdates(currDate) {
    const weekdates = [];
    for (let i = 1; i <= 7; i++) {
      const first = currDate.getDate() - currDate.getDay() + i
      const day = new Date(currDate.setDate(first)).toISOString().slice(0, 10);
      weekdates.push(day);
    }
    return weekdates;
  }

  dateChanged = (date) => {
    this.props.dateChanged(new Date(date));
    this.setState({
      selectDate: new Date(date),
    });
  }

  render() {

    this.state.weekdays = this.state.week.map((date) =>
      <li className={(moment(this.state.selectDate).format('YYYY-MM-DD') === moment(date).format('YYYY-MM-DD')) ? 'date-item active' : 'date-item'}
          onClick={() => this.dateChanged(date)}
      >
        <span className="calendar-day">{moment.tz(date, 'Europe/London').format('ddd')}</span>
        <span className="calendar-date">{moment.tz(date, 'Europe/London').format('D')}</span>
      </li>
    );

    return (
      <div className="appointment-calendar">
        <button onClick={() => this.togglePrev(this.state.week[0])} >Prev</button>
        <ul className="calendar-wrapper">
          {this.state.weekdays}
        </ul>
        <button onClick={() => this.toggleNext(this.state.week.slice(-1).pop())} >Next</button>
      </div>
    );
  }
}
