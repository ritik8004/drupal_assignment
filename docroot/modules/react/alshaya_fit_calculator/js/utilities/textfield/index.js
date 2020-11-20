import React from 'react';

const TextField = ({ name, label, focusClass }) => (
  <div className="fitCalc-type-textfield">
    <input
      type="text"
      id={name}
      name={name}
      className={focusClass}
    />
    <div className="c-input__bar" />
    <label>{label}</label>
  </div>
);

export default TextField;
