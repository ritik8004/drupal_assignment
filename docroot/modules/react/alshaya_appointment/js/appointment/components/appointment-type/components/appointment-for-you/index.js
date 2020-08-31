import React from 'react';
import SectionTitle from '../../../section-title';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class AppointmentForYou extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem, appointmentCompanion } = this.props;
    const label = appointmentCompanion.value > 1 ? getStringMessage('appointment_for_you_many') : getStringMessage('appointment_for_you_one');
    return (
      <div className="appointment-for-you-wrapper appointment-type-item">
        <SectionTitle>
          {label}
          *
        </SectionTitle>
        <div className="appointment-for-you-container">
          <div className="appointment-for-you-list fadeInUp">
            <input
              type="radio"
              value="Yes"
              name="appointmentForYou"
              id="appointmentForYou-yes"
              checked={activeItem === 'Yes'}
              onChange={this.handleChange}
            />
            <label htmlFor="appointmentForYou-yes">
              {getStringMessage('yes')}
            </label>
          </div>
          <div className="appointment-for-you-list fadeInUp">
            <input
              type="radio"
              value="No"
              name="appointmentForYou"
              id="appointmentForYou-no"
              checked={activeItem === 'No'}
              onChange={this.handleChange}
            />
            <label htmlFor="appointmentForYou-no">
              {getStringMessage('no')}
            </label>
          </div>
        </div>
      </div>
    );
  }
}
