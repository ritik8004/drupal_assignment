import React from 'react';
import moment from 'moment';
import { getDateFormat, getTimeFormat } from '../../../utilities/helper';
import getStringMessage from '../../../../../js/utilities/strings';

export default class AppointmentSlots extends React.Component {
  constructor(props) {
    super(props);

    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');
    const { selectedSlot, appointmentId } = localStorageValues;
    if (selectedSlot) {
      this.state = {
        timeSlot: selectedSlot,
        appointmentId,
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
    const { notFound } = this.props;
    let { items } = this.props;
    const { timeSlot, appointmentId } = this.state;

    // Push selected timeslot when editing appointment.
    if (appointmentId) {
      if (items.return === undefined) {
        items = {
          return: [timeSlot],
        };
      } else {
        let found = false;
        const selectedDate = moment(timeSlot.appointmentSlotTime).format(getDateFormat());
        const slotDate = moment(items.return[0].appointmentSlotTime).format(getDateFormat());
        // Check if date is same and slot doesn't exist.
        if (selectedDate === slotDate) {
          for (let i = 0; i < items.return.length; i++) {
            if (selectedDate !== slotDate) {
              found = true;
              break;
            }
            if (items.return[i].appointmentSlotTime === timeSlot.appointmentSlotTime) {
              found = true;
              break;
            }
          }
        } else {
          found = true;
        }
        if (!found) {
          items.return.push(timeSlot);
          items.return.sort(
            (a, b) => new Date(a.appointmentSlotTime) - new Date(b.appointmentSlotTime),
          );
        }
      }
    }

    if (items !== null && items !== undefined && Object.prototype.hasOwnProperty.call(items, 'return')) {
      for (let i = 0; i < items.return.length; i++) {
        const h = moment(items.return[i].appointmentSlotTime).format('HH');
        const hours = parseFloat(h);
        if (hours < 12) {
          timeSlots.morning.push(items.return[i]);
        } else if (hours >= 12 && hours < 17) {
          timeSlots.afternoon.push(items.return[i]);
        } else {
          timeSlots.evening.push(items.return[i]);
        }
      }
    }

    listMorningItems = timeSlots.morning.map((item) => (
      <li className="morning-time-slots" key={item.appointmentSlotTime}>
        <span
          href="#"
          className={(timeSlot.appointmentSlotTime === item.appointmentSlotTime) ? 'time-slots active' : 'time-slots'}
          onClick={() => this.handler(item)}
        >
          {moment(item.appointmentSlotTime).format(getTimeFormat())}
        </span>
      </li>
    ));

    listAfternoonItems = timeSlots.afternoon.map((item) => (
      <li className="afternoon-time-slots" key={item.appointmentSlotTime}>
        <span
          className={(timeSlot.appointmentSlotTime === item.appointmentSlotTime) ? 'time-slots active' : 'time-slots'}
          onClick={() => this.handler(item)}
        >
          {moment(item.appointmentSlotTime).format(getTimeFormat())}
        </span>
      </li>
    ));

    listEveningItems = timeSlots.evening.map((item) => (
      <li className="evening-time-slots" key={item.appointmentSlotTime}>
        <span
          className={(timeSlot.appointmentSlotTime === item.appointmentSlotTime) ? 'time-slots active' : 'time-slots'}
          onClick={() => this.handler(item)}
        >
          {moment(item.appointmentSlotTime).format(getTimeFormat())}
        </span>
      </li>
    ));

    return (
      <div className="appointment-time-slots">

        { listMorningItems.length > 0
        && (
          <div className="morning-items-wrapper">
            <div className="morning-items-title">
              {getStringMessage('morning')}
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
              {getStringMessage('afternoon')}
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
              {getStringMessage('evening')}
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
          <div className="appointment-slots-empty">{ notFound }</div>
        )}
      </div>
    );
  }
}
