import React from 'react';

const AuraFormFieldItem = ({
  selectedOption,
  fieldKey,
  fieldValue,
  fieldText,
  selectOptionCallback,
}) => (
  <div key={fieldKey} className="linking-option" onClick={() => selectOptionCallback(fieldValue)}>
    <input
      type="radio"
      id={fieldKey}
      name="linking-options"
      value={fieldValue}
      className="linking-option-radio"
      defaultChecked={selectedOption === fieldValue}
    />
    <label
      className="radio-sim radio-label"
      htmlFor={fieldKey}
    >
      {fieldText}
    </label>
  </div>
);

export default AuraFormFieldItem;
