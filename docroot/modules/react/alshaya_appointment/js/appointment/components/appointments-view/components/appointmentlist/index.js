import React from 'react';
import moment from 'moment';
import Popup from 'reactjs-popup';
import { fetchAPIData } from '../../../../../utilities/api/fetchApiData';
import StoreAddress from '../../../appointment-store/components/store-address';
import WithModal
  from '../../../../../../../alshaya_spc/js/checkout/components/with-modal';
import AppointmentEditBox from '../appointmentlist-editbox';

export default class AppointmentListItem extends React.Component {
  constructor(props) {
    super(props);
    const { appointment } = this.props;
    this.state = {
      locationData: {},
      companionData: {},
      appointment,
    };
  }

  componentDidMount() {
    const { appointment } = this.state;
    const { locationExternalId, confirmationNumber } = appointment;
    const { id } = drupalSettings.alshaya_appointment.user_details;

    if (locationExternalId) {
      const apiUrl = `/get/store/criteria?location=${locationExternalId}`;
      const apiData = fetchAPIData(apiUrl);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              locationData: result.data.return.locations,
            });
          }
        });
      }
    }

    if (confirmationNumber) {
      const apiUrl = `/get/companions?appointment=${confirmationNumber}&id=${id}`;
      const apiData = fetchAPIData(apiUrl);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              companionData: result.data.return,
            });
          }
        });
      }
    }
  }

  render() {
    const { appointment, cancelAppointment, num } = this.props;
    let activityName = '';
    if (appointment !== undefined) {
      activityName = appointment.activityName;
    }

    let address = {};
    let storeName = '';
    const { locationData } = this.state;
    if (locationData !== undefined) {
      address = locationData.companyAddress;
      storeName = locationData.locationName;
    }

    let appointmentStartDate = '';
    if (appointment !== undefined) {
      appointmentStartDate = appointment.appointmentStartDate;
    }

    const companions = [];
    let companionsRender = [];
    const { companionData } = this.state;
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
            };
          } else {
            companions[k] = {
              firstName: '',
              lastName: '',
              dob: '',
            };
          }
        }
        if (item.question.includes('Last')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: item.answer,
              dob: '',
            };
          } else {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: '',
              dob: '',
            };
          }
        }
        if (item.question.includes('Date')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: companions[k].lastName,
              dob: item.answer,
            };
          } else {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: companions[k].lastName,
              dob: '',
            };
          }
          k += 1;
        }
      }

      companionsRender = companions.map((item) => (
        <div>
          <span>{item.firstName}</span>
          <span>{item.lastName}</span>
        </div>
      ));
    }

    const { confirmationNumber } = appointment;

    return (
      <div className="appointment-list-details fadeInUp" style={{ animationDelay: '0.6s' }}>
        <div className="appointment-list-type">
          <span className="appointment-list-label">
            { Drupal.t('Appointment type') }
          </span>
          <span>
            { activityName }
          </span>
        </div>
        <div className="appointment-list-date-time">
          <span className="appointment-list-label">{ Drupal.t('Date and Time') }</span>
          <span>{ moment(appointmentStartDate).format('dddd, Do MMMM') }</span>
          <br />
          <span>{ moment(appointmentStartDate).format('YYYY hh:mm A') }</span>
        </div>
        <div className="appointment-list-store">
          <span className="appointment-list-label">{ Drupal.t('Store Location') }</span>
          <div>
            <p>{storeName}</p>
            <StoreAddress address={address} />
          </div>
        </div>
        <div className="appointment-list-companion">
          <span className="appointment-list-label">
            {Drupal.t('Customer Details')}
          </span>
          { companionsRender }
        </div>
        <div className="appointment-actions">
          <WithModal modalStatusKey={`edit${num}`}>
            {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
              <>
                <button type="button" className="action-edit" onClick={() => triggerOpenModal()}>
                  { Drupal.t('Edit') }
                </button>
                <Popup open={isModalOpen} closeOnDocumentClick closeOnEscape>
                  <>
                    <AppointmentEditBox
                      appointment={appointment}
                      storeName={storeName}
                      address={address}
                      companionData={companionData}
                    />
                    <button type="button" className="close-modal" onClick={() => triggerCloseModal()}>{ Drupal.t('close') }</button>
                  </>
                </Popup>
              </>
            )}
          </WithModal>
          <button
            type="button"
            className="action-delete"
            onClick={() => cancelAppointment(confirmationNumber, num)}
          >
            {Drupal.t('Delete')}
          </button>
        </div>
      </div>
    );
  }
}
