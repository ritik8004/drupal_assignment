import React from 'react';
import Select from 'react-select';

const ReturnQuantitySelect = ({
  qtyOptions, handleSelectedQuantity, sku, disableQtyBtn,
}) => (
  <>
    <div className="return-qty-row">
      <div className="return-reason-label dark">{ Drupal.t('Select quantity', {}, { context: 'online_returns' }) }</div>
      <Select
        classNamePrefix="qtySelect"
        className="return-qty-select"
        options={qtyOptions}
        defaultValue={disableQtyBtn ? qtyOptions.at(-1) : qtyOptions[0]}
        isSearchable={false}
        isDisabled={disableQtyBtn}
        onChange={(selectedOption) => handleSelectedQuantity(selectedOption, sku)}
      />
    </div>
  </>
);

export default ReturnQuantitySelect;
