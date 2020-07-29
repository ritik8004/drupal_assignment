import React from 'react';
import ConfirmationItems from './components/confirmation-items';
import { getArrayFromCompanionData } from '../../../utilities/helper';

const AppointmentConfirmationPrint = React.forwardRef((props, ref) => {
  const {
    clientData,
    companionData,
    appointmentCategory,
    appointmentType,
    location,
    date,
    time,
  } = props;

  // Construct companion array from companionData.
  const companion = getArrayFromCompanionData(companionData);

  let companionsRender = '';
  if (companion.length > 0) {
    companionsRender = companion.map((item) => (
      <ConfirmationItems
        item={{ label: item.label, value: item.value }}
      />
    ));
  }

  const { logo } = drupalSettings.alshaya_appointment;
  return (
    <div ref={ref} className="appointment-confirmation-print-content">
      <div className="appointment-confirmation-print-header">
        <img src={logo.logo_url} />
      </div>
      <div className="appointment-confirmation-print-body">
        <div className="inner-header">
          <label>{Drupal.t('Appointment Summary')}</label>
        </div>
        <div className="inner-body">
          <ConfirmationItems
            item={{ label: Drupal.t('Appointment Booked by'), value: `${clientData.firstName} ${clientData.lastName}` }}
          />
          { companionsRender }
          <ConfirmationItems
            item={{ label: Drupal.t('Appointment category'), value: appointmentCategory.name }}
          />
          <ConfirmationItems
            item={{ label: Drupal.t('Appointment type'), value: appointmentType.label }}
          />
          <ConfirmationItems
            item={{ label: Drupal.t('Location'), value: location }}
          />
          <ConfirmationItems
            item={{ label: Drupal.t('Date'), value: date }}
          />
          <ConfirmationItems
            item={{ label: Drupal.t('Time'), value: time }}
          />
        </div>
      </div>
    </div>
  );
});

export default AppointmentConfirmationPrint;
