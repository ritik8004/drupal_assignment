import React from 'react';

export default class AppointmentTermsConditions extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
    return (
      <div className="appointment-terms-conditions-wrapper">
        <input
          type="checkbox"
          name="appointmentTermsConditions"
          checked={activeItem}
          onChange={this.handleChange}
        />
        <div className="appointment-terms-conditions-inner-wrapper">
          <label>
            {Drupal.t('Please tick to confirm the following')}
            *
          </label>
          <div className="">
            {window.drupalSettings.alshaya_appointment.appointmentTermsConditionsText}
          </div>
        </div>
      </div>
    );
  }
}
