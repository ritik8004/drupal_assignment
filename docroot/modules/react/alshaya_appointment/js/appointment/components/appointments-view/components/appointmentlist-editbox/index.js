import React from 'react';
import moment from 'moment';
import StoreAddress from '../../../appointment-store/components/store-address';
import { getDateFormat } from '../../../../../utilities/helper';
import getStringMessage from '../../../../../../../js/utilities/strings';

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

    const companionsRender = [];
    const { companionData } = this.props;
    if (companionData !== undefined && companionData.length > 0) {
      let j = 1;
      for (let i = 0; i < companionData.length; i++) {
        companionsRender.push(
          <div key={i}>
            <p>
              <span>{ `${getStringMessage('customer_label')} ${j}:` }</span>
              <span>{companionData[i].firstName}</span>
              <span>{companionData[i].lastName}</span>
            </p>
            <p>
              <span>{ `${getStringMessage('dob_label')}:` }</span>
              <span>{ moment(companionData[i].dob).format(getDateFormat()) }</span>
            </p>
          </div>,
        );
        j += 1;
      }
    }

    const url = `${baseUrl}${pathPrefix}appointment/booking?appointment=${confirmationNumber}`;

    return (
      <div className="appointment-edit-popup fadeInUp">
        <div className="appointmentbox title">
          <span>{ getStringMessage('edit') }</span>
        </div>
        <div className="appointmentbox appointment-type">
          <div>
            <span className="label">{ getStringMessage('activity_label') }</span>
            <span>{ appointment.activityName }</span>
          </div>
        </div>
        <div className="appointmentbox appointment-store">
          <span className="label">{ getStringMessage('store_location_label') }</span>
          <div>
            <p>{storeName}</p>
            <StoreAddress address={address} />
          </div>
          <a className="appointmentbox-action-edit" href={`${url}&step=select-store`}>{ getStringMessage('edit_store') }</a>
        </div>
        <div className="appointmentbox appointment-datetime">
          <span className="label">{ `${getStringMessage('date')} ${getStringMessage('and')} ${getStringMessage('time')}` }</span>
          <div>
            <span>{ moment(appointmentStartDate).format('dddd, Do MMMM') }</span>
            <br />
            <span>{ moment(appointmentStartDate).format('YYYY hh:mm A') }</span>
          </div>
          <a className="appointmentbox-action-edit" href={`${url}&step=select-time-slot`}>{ getStringMessage('edit_time') }</a>
        </div>
        <div className="appointmentbox appointment-companion">
          <span className="label">
            {getStringMessage('customer_details_label')}
          </span>
          <div className="popup-customer-details">{ companionsRender }</div>
          <a className="appointmentbox-action-edit" href={`${url}&step=customer-details`}>{ getStringMessage('edit_companion') }</a>
        </div>
      </div>
    );
  }
}
