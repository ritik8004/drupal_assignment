import React from 'react';
import moment from 'moment';
import StoreAddress from '../../../appointment-store/components/store-address';

export default class AppointmentEditBox extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const { baseUrl, pathPrefix } = drupalSettings.path;
    const { appointment } = this.props;

    const { address } = this.props;
    const { storeName } = this.props;

    const { appointmentStartDate, confirmationNumber } = appointment;

    const companions = [];
    let companionsRender = '';
    const { companionData } = this.props;
    if (companionData !== undefined && companionData.length > 0) {
      let k = 0;
      for (let i = 0; i < companionData.length; i++) {
        const item = companionData[i];
        if (item.question.includes('First')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: item.answer,
              lastName: '',
              dob: '',
              customer: k + 1,
            };
          } else {
            companions[k] = {
              firstName: '',
              lastName: '',
              dob: '',
              customer: k + 1,
            };
          }
        }
        if (item.question.includes('Last')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: item.answer,
              dob: '',
              customer: k + 1,
            };
          } else {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: '',
              dob: '',
              customer: k + 1,
            };
          }
        }
        if (item.question.includes('Date')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: companions[k].lastName,
              dob: item.answer,
              customer: k + 1,
            };
          } else {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: companions[k].lastName,
              dob: '',
              customer: k + 1,
            };
          }
          k += 1;
        }
      }

      companionsRender = companions.map((item) => (
        <div>
          <p>
            <span>{ Drupal.t('Customer !i:', { '!i': item.customer }) }</span>
            <span>{item.firstName}</span>
            <span>{item.lastName}</span>
          </p>
          <p>
            <span>{ Drupal.t('Date of Birth:') }</span>
            <span>{ item.dob }</span>
          </p>
        </div>
      ));
    }

    const url = `${baseUrl}${pathPrefix}appointment/booking?appointment=${confirmationNumber}`;

    return (
      <div className="appointment-edit-popup fadeInUp">
        <div className="appointmentbox title">
          <span>{ Drupal.t('Edit') }</span>
        </div>
        <div className="appointmentbox appointment-type">
          <div>
            <span className="label">{ Drupal.t('Appointment type') }</span>
            <span>{ appointment.activityName }</span>
          </div>
        </div>
        <div className="appointmentbox appointment-store">
          <span className="label">{ Drupal.t('Store Location') }</span>
          <div>
            <p>{storeName}</p>
            <StoreAddress address={address} />
          </div>
          <a className="appointmentbox-action-edit" href={`${url}&step=select-store`}>{ Drupal.t('Edit Store') }</a>
        </div>
        <div className="appointmentbox appointment-datetime">
          <span className="label">{ Drupal.t('Date and Time') }</span>
          <div>
            <span>{ moment(appointmentStartDate).format('dddd, Do MMMM') }</span>
            <br />
            <span>{ moment(appointmentStartDate).format('YYYY hh:mm A') }</span>
          </div>
          <a className="appointmentbox-action-edit" href={`${url}&step=select-time-slot`}>{ Drupal.t('Edit time') }</a>
        </div>
        <div className="appointmentbox appointment-companion">
          <span className="label">
            {Drupal.t('Customer Details')}
          </span>
          <div className="popup-customer-details">{ companionsRender }</div>
          <a className="appointmentbox-action-edit" href={`${url}&step=customer-details`}>{ Drupal.t('Edit Companion') }</a>
        </div>
      </div>
    );
  }
}
