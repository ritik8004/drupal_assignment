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
    var currentDate = moment(currDate);
    var weekStart = currentDate.clone().startOf('isoWeek');
    var weekEnd = currentDate.clone().endOf('isoWeek');
    for (var i = 0; i <= 6; i++) {
      weekdates.push(moment(weekStart).add(i, 'days').format("YYYY-MM-DD"));
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
        <span className="calendar-day">{moment(date).format('ddd')}</span>
        <span className="calendar-date">{moment(date).format('D')}</span>
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
