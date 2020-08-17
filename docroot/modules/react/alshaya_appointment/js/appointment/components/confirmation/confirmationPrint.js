import React from 'react';
import ConfirmationItems from './components/confirmation-items';
import getStringMessage from '../../../../../js/utilities/strings';
import isRTL from '../../../utilities/rtl';

const AppointmentConfirmationPrint = React.forwardRef((props, ref) => {
  const {
    clientData,
    companion,
    appointmentCategory,
    appointmentType,
    location,
    date,
    time,
  } = props;

  let companionsRender = '';
  if (companion.length > 0) {
    companionsRender = companion.map((item) => (
      <ConfirmationItems
        key={item.label}
        item={{ label: item.label, value: item.value }}
      />
    ));
  }

  const { logo } = drupalSettings.alshaya_appointment;
  const direction = isRTL() === true ? 'rtl' : 'ltr';
  return (
    <div ref={ref} className="appointment-confirmation-print-content" dir={direction}>
      <div className="appointment-confirmation-print-header">
        <img src={logo.logo_url} />
      </div>
      <div className="appointment-confirmation-print-body">
        <div className="inner-header">
          <label>{getStringMessage('appointment_summary_label')}</label>
        </div>
        <div className="inner-body">
          <ConfirmationItems
            item={{ label: getStringMessage('appointment_booked_by_label'), value: `${clientData.firstName} ${clientData.lastName}` }}
          />
          { companionsRender }
          <ConfirmationItems
            item={{ label: getStringMessage('program_label'), value: appointmentCategory.name }}
          />
          <ConfirmationItems
            item={{ label: getStringMessage('activity_label'), value: appointmentType.label }}
          />
          <ConfirmationItems
            item={{ label: getStringMessage('location'), value: location }}
          />
          <ConfirmationItems
            item={{ label: getStringMessage('date'), value: date }}
          />
          <ConfirmationItems
            item={{ label: getStringMessage('time'), value: time }}
          />
        </div>
      </div>
    </div>
  );
});

export default AppointmentConfirmationPrint;
