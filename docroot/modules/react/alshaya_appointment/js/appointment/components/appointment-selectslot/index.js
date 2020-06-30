import React from 'react';
import moment from 'moment-timezone';

export default class AppointmentSlots extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      slot: {}
    };
  }

  handleSelect = (e, data) => {
    this.props.handler(data);
  }

  render() {

    let listMorningItems = '';
    let listAfternoonItems = '';
    let listEveningItems = '';
    const timeSlots = this.props.items;

    if (timeSlots !== undefined && timeSlots.hasOwnProperty('morning')) {
      listMorningItems = timeSlots.morning.map((item) =>
        <li className="morning-time-slots">
          <a href="javascript:void(0)" data={item} className="time-slots" onClick={(e) => this.handleSelect(event, item)}>
            {moment.tz(item.time, 'Europe/London').format('LT')}
          </a>
        </li>
      );
    }

    if (timeSlots !== undefined && timeSlots.hasOwnProperty('afternoon')) {
      listAfternoonItems = timeSlots.afternoon.map((item) =>
        <li className="afternoon-time-slots">
          <a href="javascript:void(0)" data={item} className="time-slots" onClick={(e) => this.handleSelect(event, item)}>
            {moment.tz(item.time, 'Europe/London').format('LT')}
          </a>
        </li>
      );
    }

    if (timeSlots !== undefined && timeSlots.hasOwnProperty('evening')) {
      listEveningItems = timeSlots.evening.map((item) =>
        <li className="evening-time-slots">
          <a href="javascript:void(0)" data={item} className="time-slots" onClick={(e) => this.handleSelect(event, item)}>
            {moment.tz(item.time, 'Europe/London').format('LT')}
          </a>
        </li>
      );
    }

    return (
      <div className="appointment-time-slots">
        <ul className="morning-items">
          <li className="morning-items-title">
            {Drupal.t('Morning')}
          </li>
          {listMorningItems}
        </ul>
        <ul classNames="afternoon-items">
          <li className="afternoon-items-title">
            {Drupal.t('Afternoon')}
          </li>
          {listAfternoonItems}
        </ul>
        <ul classNames="evening-items">
          <li className="evening-items-title">
            {Drupal.t('Evening')}
          </li>
          {listEveningItems}
        </ul>
      </div>
    );
  }
}
