import React from 'react';
import Select from 'react-select';

const ReturnReasonsSelect = ({
  returnReasons, handleSelectedReason,
}) => (
  <>
    <div className="return-qty-row">
      <div className="return-reason-label">{ Drupal.t('Select quantity') }</div>
      <Select
        classNamePrefix="qtySelect"
        className="return-qty-select"
        options={returnReasons}
        defaultValue={returnReasons[0]}
        onChange={handleSelectedReason}
      />
    </div>
  </>
);

export default ReturnReasonsSelect;
