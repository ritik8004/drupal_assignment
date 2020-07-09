import React from 'react';
import SectionTitle from '../../../section-title';
import AppointmentSelect from '../appointment-select';

export default class AppointmentCompanion extends React.Component {
  onSelectChange = (e, name) => {
    const { onSelectChange } = this.props;
    onSelectChange(e, name);
  };

  render() {
    const { activeItem, appointmentCompanionItems } = this.props;

    const options = [];
    appointmentCompanionItems.forEach((v, key) => {
      options[key] = {
        value: v.value,
        label: v.label,
      };
    });

    return (
      <div className="appointment-companion-wrapper appointment-type-item">
        <SectionTitle>
          {Drupal.t('How many people do you want to book the appointment for?')}
          *
        </SectionTitle>
        <AppointmentSelect
          options={options}
          onSelectChange={this.onSelectChange}
          activeItem={activeItem}
          name="appointmentCompanion"
          aptSelectClass="appointment-companion-select"
        />
      </div>
    );
  }
}
