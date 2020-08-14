import React from 'react';
import moment from 'moment';
import Popup from 'reactjs-popup';
import { fetchAPIData } from '../../../../../utilities/api/fetchApiData';
import StoreAddress from '../../../appointment-store/components/store-address';
import WithModal
  from '../../../../../../../alshaya_spc/js/checkout/components/with-modal';
import AppointmentEditBox from '../appointmentlist-editbox';
import getStringMessage from '../../../../../../../js/utilities/strings';

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
              companionData: result.data,
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

    let companionsRender = [];
    const { companionData } = this.state;
    if (companionData !== undefined && companionData.length > 0) {
      companionsRender = companionData.map((item) => (
        <div>
          <p>
            {item.firstName}
            <span> </span>
            {item.lastName}
          </p>
        </div>
      ));
    }

    const { confirmationNumber } = appointment;

    return (
      <div className="appointment-list-details fadeInUp">
        <div className="appointment-list-type">
          <span className="appointment-list-label">
            { getStringMessage('activity_label') }
          </span>
          <span>
            { activityName }
          </span>
        </div>
        <div className="appointment-list-date-time">
          <span className="appointment-list-label">{ `${getStringMessage('date')} ${getStringMessage('and')} ${getStringMessage('time')}` }</span>
          <span>{ moment(appointmentStartDate).format('dddd, Do MMMM') }</span>
          <span>{ moment(appointmentStartDate).format('YYYY hh:mm A') }</span>
        </div>
        <div className="appointment-list-store">
          <span className="appointment-list-label">{ getStringMessage('store_location_label') }</span>
          <div>
            <p>{storeName}</p>
            <StoreAddress address={address} />
          </div>
        </div>
        <div className="appointment-list-companion">
          <span className="appointment-list-label">
            {getStringMessage('customer_details_label')}
          </span>
          { companionsRender }
        </div>
        <div className="appointment-actions">
          <WithModal modalStatusKey={`edit${num}`}>
            {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
              <>
                <button type="button" className="action-edit" onClick={() => triggerOpenModal()}>
                  { getStringMessage('edit') }
                </button>
                <Popup open={isModalOpen} closeOnEscape closeOnDocumentClick={false}>
                  <>
                    <button type="button" className="close-modal" onClick={() => triggerCloseModal()}>{ getStringMessage('close') }</button>
                    <AppointmentEditBox
                      appointment={appointment}
                      storeName={storeName}
                      address={address}
                      companionData={companionData}
                    />
                  </>
                </Popup>
              </>
            )}
          </WithModal>
          <WithModal modalStatusKey={`delete${num}`}>
            {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
              <>
                <button
                  type="button"
                  className="action-delete"
                  onClick={() => triggerOpenModal()}
                >
                  {getStringMessage('delete')}
                </button>
                <Popup className="appointment-delete-popup-wrapper" open={isModalOpen} closeOnEscape closeOnDocumentClick={false}>
                  <>
                    <button type="button" className="close-modal" onClick={() => triggerCloseModal()}>{ getStringMessage('close') }</button>
                    <div className="appointment-delete-popup fadeInUp">
                      <div className="appointmentbox title">
                        <span>{ getStringMessage('cancel') }</span>
                      </div>
                      <div className="appointmentbox message">
                        <span>
                          { getStringMessage('cancel_appointment_confirmation_question',
                            { '!type': appointment.activityName }) }
                        </span>
                      </div>
                      <div className="appointmentbox buttons">
                        <button
                          type="button"
                          className="ok-button"
                          onClick={() => {
                            triggerCloseModal();
                            cancelAppointment(confirmationNumber, num);
                          }}
                        >
                          {getStringMessage('ok')}
                        </button>
                        <button type="button" className="cancel-button" onClick={() => triggerCloseModal()}>{ getStringMessage('cancel') }</button>
                      </div>
                    </div>
                  </>
                </Popup>
              </>
            )}
          </WithModal>
        </div>
      </div>
    );
  }
}
