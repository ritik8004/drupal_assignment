import React from 'react';

export default class AppointmentAck extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
    return (
      <div className="appointment-ack-wrapper">
        <input
          type="checkbox"
          name="appointmentAck"
          checked={activeItem}
          onChange={this.handleChange}
        />
        <div className="appointment-ack-inner-wrapper">
          <label>
            {Drupal.t('Please tick to confirm the following')}
            *
          </label>
          <div className="">
            {window.drupalSettings.alshaya_appointment.getAppointmentAckText}
          </div>
        </div>
      </div>
    );
  }
}
