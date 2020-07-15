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
    const { id } = drupalSettings.alshaya_appointment.user_details;
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

    const url = `${baseUrl}${pathPrefix}appointment/booking?user=${id}&appointment=${confirmationNumber}`;

    return (
      <>
        <div className="appointmentbox appointment-type">
          <p>
            <span>{ Drupal.t('Appointment category:') }</span>
            <span>{ appointment.programName }</span>
          </p>
        </div>
        <div className="appointmentbox appointment-store">
          <span>{ Drupal.t('Store Location') }</span>
          <div>
            <p>{storeName}</p>
            <StoreAddress address={address} />
            <a href={`${url}&store=true`}>{ Drupal.t('Edit Store') }</a>
          </div>
        </div>
        <div className="appointmentbox appointment-datetime">
          <span>{ Drupal.t('Date and Time') }</span>
          <span>{ moment(appointmentStartDate).format('dddd, Do MMMM') }</span>
          <br />
          <span>{ moment(appointmentStartDate).format('YYYY hh:mm A') }</span>
          <a href={`${url}&timeslot=true`}>{ Drupal.t('Edit time') }</a>
        </div>
        <div className="appointmentbox appointment-companion">
          <span>
            {Drupal.t('Customer Details')}
          </span>
          { companionsRender }
          <a href={`${url}?customer=true`}>{ Drupal.t('Edit Companion') }</a>
        </div>
      </>
    );
  }
}
