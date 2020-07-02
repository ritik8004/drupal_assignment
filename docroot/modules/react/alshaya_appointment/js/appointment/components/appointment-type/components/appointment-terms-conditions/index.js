import React from 'react';

export default class AppointmentTermsConditions extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
    return (
      <div className="appointment-terms-conditions-wrapper fadeInUp">
        <input
          type="checkbox"
          name="appointmentTermsConditions"
          id="appointmentTermsConditions"
          checked={activeItem}
          onChange={this.handleChange}
        />
        <label htmlFor="appointmentTermsConditions">
          {Drupal.t('Please tick to confirm the following')}
          *
        </label>
        { window.drupalSettings.alshaya_appointment.appointment_terms_conditions_text
          ? (
            <div className="appointment-terms-conditions-text-wrapper">
              {window.drupalSettings.alshaya_appointment.appointment_terms_conditions_text}
            </div>
          )
          : null}
      </div>
    );
  }
}
