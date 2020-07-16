import React from 'react';
import moment from 'moment';
import { getStorageInfo } from '../../../utilities/storage';

export default class AppointmentSlots extends React.Component {
  constructor(props) {
    super(props);

    const localStorageValues = getStorageInfo();
    const { selectedSlot } = localStorageValues;
    if (selectedSlot) {
      this.state = {
        timeSlot: selectedSlot,
      };
    } else {
      this.state = {
        timeSlot: {},
      };
    }
  }

  handler(data) {
    this.setState({
      timeSlot: data,
    });
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
    const { items, notFound } = this.props;
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

    const { timeSlot } = this.state;

    listMorningItems = timeSlots.morning.map((item) => (
      <li className="morning-time-slots">
        <a
          href
          className={(timeSlot.appointmentSlotTime === item.appointmentSlotTime) ? 'time-slots active' : 'time-slots'}
          onClick={() => this.handler(item)}
        >
          {moment(item.appointmentSlotTime).format('LT')}
        </a>
      </li>
    ));

    listAfternoonItems = timeSlots.afternoon.map((item) => (
      <li className="afternoon-time-slots">
        <a
          href
          className={(timeSlot.appointmentSlotTime === item.appointmentSlotTime) ? 'time-slots active' : 'time-slots'}
          onClick={() => this.handler(item)}
        >
          {moment(item.appointmentSlotTime).format('LT')}
        </a>
      </li>
    ));

    listEveningItems = timeSlots.evening.map((item) => (
      <li className="evening-time-slots">
        <a
          href
          className={(timeSlot.appointmentSlotTime === item.appointmentSlotTime) ? 'time-slots active' : 'time-slots'}
          onClick={() => this.handler(item)}
        >
          {moment(item.appointmentSlotTime).format('LT')}
        </a>
      </li>
    ));

    return (
      <div className="appointment-time-slots">

        { listMorningItems.length > 0
        && (
          <div className="morning-items-wrapper">
            <div className="morning-items-title">
              {Drupal.t('Morning')}
            </div>
            <ul className="morning-items">
              {listMorningItems}
            </ul>
          </div>
        )}

        {listAfternoonItems.length > 0
        && (
          <div className="afternoon-items-wrapper">
            <div className="afternoon-items-title">
              {Drupal.t('Afternoon')}
            </div>
            <ul className="afternoon-items">
              {listAfternoonItems}
            </ul>
          </div>
        )}

        { listEveningItems.length > 0
        && (
          <div className="evening-items-wrapper">
            <div className="evening-items-title">
              {Drupal.t('Evening')}
            </div>
            <ul className="evening-items">
              {listEveningItems}
            </ul>
          </div>
        )}
        { listMorningItems.length === 0
        && listAfternoonItems.length === 0
        && listEveningItems.length === 0
        && (
          <p>{ notFound }</p>
        )}
      </div>
    );
  }
}
