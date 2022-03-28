import React from 'react';
import Select from 'react-select';

const ReturnQuantitySelect = ({
  qtyOptions,
}) => (
  <>
    <div className="return-reasons-row">
      <div className="return-reason-label">{ Drupal.t('Reason for Return') }</div>
      <Select
        classNamePrefix="reasonsSelect"
        className="return-reasons-select"
        options={qtyOptions}
        defaultValue={qtyOptions[0]}
      />
    </div>
  </>
);

export default ReturnQuantitySelect;
