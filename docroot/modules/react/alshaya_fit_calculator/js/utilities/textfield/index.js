import React from 'react';

const TextField = ({ name, label, focusClass }) => {
  const handleEvent = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
  };

  return (
    <div className="fitCalc-type-textfield">
      <input
        type="text"
        onBlur={(e) => handleEvent(e, 'blur')}
        id={name}
        name={name}
        className={focusClass}
      />
      <label>{label}</label>
    </div>
  );
};

export default TextField;
