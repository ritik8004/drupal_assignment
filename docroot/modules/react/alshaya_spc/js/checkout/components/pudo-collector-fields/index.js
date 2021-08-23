import React, { useEffect } from 'react';

import SectionTitle from '../../../utilities/section-title';
import TextField from '../../../utilities/textfield';
import getStringMessage from '../../../utilities/strings';
import { smoothScrollTo } from '../../../utilities/smoothScroll';

const PudoCollectorFields = ({
  collectorForm,
}) => {
  useEffect(() => {
    smoothScrollTo('.spc-cnc-address-form-sidebar .spc-checkout-collector-information');
  }, [collectorForm]);

  return (
    <div className="spc-checkout-collector-information subtitle-yes" id="spc-checkout-collector-info">
      <div className="spc-contact-information-header">
        <SectionTitle>{getStringMessage('cnc_collection_contact_info_title')}</SectionTitle>
      </div>
      <div className="spc-checkout-collector-information-fields">
        <TextField
          type="text"
          name="collectorFullname"
          label={getStringMessage('ci_full_name')}
        />
        <TextField
          type="email"
          name="collectorEmail"
          label={getStringMessage('ci_email')}
        />
        <TextField
          type="tel"
          name="collectorMobile"
          label={getStringMessage('ci_mobile_number')}
        />
      </div>
    </div>
  );
};

export default PudoCollectorFields;
