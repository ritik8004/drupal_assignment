import React from 'react';
import Select from 'react-select';

const ReturnQuantitySelect = ({
  qtyOptions,
}) => (
  <>
    <div className="return-qty-row">
      <div className="return-reason-label dark">{ Drupal.t('Select quantity', {}, { context: 'online_returns' }) }</div>
      <Select
        classNamePrefix="qtySelect"
        className="return-qty-select"
        options={qtyOptions}
        defaultValue={qtyOptions[0]}
        isSearchable={false}
      />
    </div>
  </>
);

export default ReturnQuantitySelect;
