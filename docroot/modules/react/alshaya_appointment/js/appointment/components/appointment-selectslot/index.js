import React from 'react';
import moment from 'moment';

export default class AppointmentSlots extends React.Component {
  handler = (data) => {
    const { handler } = this.props;
    handler(data);
  }

  render() {
    let listMorningItems = '';
    let listAfternoonItems = '';
    let listEveningItems = '';
    const timeSlots = {
      morning: [],
      afternoon: [],
      evening: [],
    };
    const { items } = this.props;
    if (items !== null && items !== undefined && Object.prototype.hasOwnProperty.call(items, 'return')) {
      for (let i = 0; i < items.return.length; i++) {
        const hours = moment(items.return[i].appointmentSlotTime).format('HH');
        if (hours < 12) {
          timeSlots.morning.push(items.return[i]);
        } else if (hours > 12 && hours < 17) {
          timeSlots.afternoon.push(items.return[i]);
        } else {
          timeSlots.evening.push(items.return[i]);
        }
      }
    }

    listMorningItems = timeSlots.morning.map((item) => (
      <li className="morning-time-slots">
        <a href data={item} className="time-slots" onClick={() => this.handler(item)}>
          {moment(item.appointmentSlotTime).format('LT')}
        </a>
      </li>
    ));

    listAfternoonItems = timeSlots.afternoon.map((item) => (
      <li className="afternoon-time-slots">
        <a href data={item} className="time-slots" onClick={() => this.handler(item)}>
          {moment(item.appointmentSlotTime).format('LT')}
        </a>
      </li>
    ));

    listEveningItems = timeSlots.evening.map((item) => (
      <li className="evening-time-slots">
        <a href data={item} className="time-slots" onClick={() => this.handler(item)}>
          {moment(item.appointmentSlotTime).format('LT')}
        </a>
      </li>
    ));

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
