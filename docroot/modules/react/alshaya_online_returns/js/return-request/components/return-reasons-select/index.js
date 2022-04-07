import React from 'react';
import Select from 'react-select';

const ReturnReasonsSelect = ({
  returnReasons, handleSelectedReason, sku,
}) => (
  <>
    <div className="return-reasons-row">
      <div className="return-reason-label dark">{ Drupal.t('Reason for Return', {}, { context: 'online_returns' }) }</div>
      <Select
        classNamePrefix="reasonsSelect"
        className="return-reasons-select"
        options={returnReasons}
        defaultValue={returnReasons[0]}
        onChange={(selectedOption) => handleSelectedReason(selectedOption, sku)}
        isSearchable={false}
      />
    </div>
  </>
);

export default ReturnReasonsSelect;
