import React, { useState } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

const TextField = (props) => {
  const [startDate, setStartDate] = useState(null);

  const handleEvent = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
  };

  const countryMobileCode = drupalSettings.alshaya_appointment.country_mobile_code;
  const countryMobileCodeMaxLength = drupalSettings.alshaya_appointment.mobile_maxlength;
  const {
    defaultValue,
    type,
    name,
    label,
    required,
    handleChange,
  } = props;

  let focusClass = '';
  if (defaultValue !== undefined && defaultValue !== '') {
    focusClass = 'focus';
  }

  const DateCustomInput = ({ value, onClick }) => (
    <div onClick={onClick}>
      <div className="dob-input-wrapper">
        <input
          type="text"
          value={value}
          onBlur={(e) => handleEvent(e, 'blur')}
          placeholder={label}
          required={required}
          readOnly
        />
        <span className="date-custom-input" />
      </div>
      <div className="c-input__bar" />
      <div id={`${name}-error`} className="error" />
    </div>
  );

  if (type === 'email') {
    return (
      <div className="appointment-form-item appointment-type-textfield">
        <input
          type="email"
          id={name}
          name={name}
          defaultValue={defaultValue}
          onBlur={(e) => handleEvent(e, 'blur')}
          className={focusClass}
          onChange={handleChange}
          required={required}
        />
        <div className="c-input__bar" />
        <label>{label}</label>
        <div id={`${name}-error`} className="error" />
      </div>
    );
  }
  if (type === 'mobile') {
    return (
      <div className="appointment-form-item appointment-type-mobile">
        <label>{label}</label>
        <div className="field-wrapper">
          <span className="country-code">{`+${countryMobileCode}`}</span>
          <input
            maxLength={countryMobileCodeMaxLength}
            type="text"
            id={name}
            name={name}
            defaultValue={defaultValue}
            onChange={handleChange}
            required={required}
          />
        </div>
        <div className="c-input__bar" />
        <div id={`${name}-error`} className="error" />
      </div>
    );
  }
  if (type === 'date') {
    return (
      <div className="appointment-form-item appointment-type-date">
        <DatePicker
          dateFormat="yyyy/MM/dd"
          selected={startDate}
          onChange={(date) => setStartDate(date)}
          name={name}
          customInput={<DateCustomInput />}
        />
      </div>
    );
  }

  return (
    <div className="appointment-form-item appointment-type-textfield">
      <input
        type="text"
        id={name}
        name={name}
        defaultValue={defaultValue}
        onChange={handleChange}
        onBlur={(e) => handleEvent(e, 'blur')}
        className={focusClass}
        required={required}
      />
      <div className="c-input__bar" />
      <label>{label}</label>
      <div id={`${name}-error`} className="error" />
    </div>
  );
};

export default TextField;
