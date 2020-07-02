import React from 'react';
import SectionTitle from '../../../section-title';

export default class AppointmentForYou extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
    return (
      <div className="appointment-for-you-wrapper appointment-type-item">
        <SectionTitle>
          {Drupal.t('Is one of these appointments for you?')}
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
              {Drupal.t('Yes')}
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
              {Drupal.t('No')}
            </label>
          </div>
        </div>
      </div>
    );
  }
}
