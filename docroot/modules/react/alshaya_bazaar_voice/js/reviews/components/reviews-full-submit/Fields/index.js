import React from 'react';

const TextField = ({
  field, fieldChanged, value,
}) => (
  <div key={field['#id']}>
    <label htmlFor={field['#id']}>{field['#title']}</label>
    <input
      type="text"
      id={field['#id']}
      name={field['#id']}
      value={value === null ? '' : value}
      required={field['#required']}
      minLength={field['#minlength']}
      maxLength={field['#maxlength']}
      default_value={field['#default_value']}
      hidden={field['#hidden']}
      onChange={(e) => {
        fieldChanged(field['#id'], e.target.value);
      }}
    />
  </div>
);

const TextArea = ({
  field, fieldChanged, value,
}) => (
  <div key={field['#id']}>
    <label htmlFor={field['#id']}>{field['#title']}</label>
    <textarea
      id={field['#id']}
      name={field['#id']}
      value={value === null ? '' : value}
      required={field['#required']}
      minLength={field['#minlength']}
      maxLength={field['#maxlength']}
      default_value={field['#default_value']}
      hidden={field['#hidden']}
      onChange={(e) => {
        fieldChanged(field['#id'], e.target.value);
      }}
    />
  </div>
);

const Select = ({
  field, fieldChanged, value,
}) => {
  const Options = field['#options'];
  return (
    <div key={field['#id']}>
      <label htmlFor={field['#id']}>{field['#title']}</label>
      <select
        id={field['#id']}
        name={field['#id']}
        value={value === null ? '' : value}
        required={field['#required']}
        default_value={field['#default_value']}
        hidden={field['#hidden']}
        onChange={(e) => {
          fieldChanged(field['#id'], e.target.value);
        }}
      >
        {Object.keys(Options).map((option) => (
          <option key={Options[option]} value={option === '' ? 0 : option}>
            {Options[option] === '' ? Drupal.t('Select') : Options[option]}
          </option>
        ))}
      </select>
    </div>
  );
};

const Checkbox = ({
  field, fieldChanged, value,
}) => (
  <div key={field['#id']}>
    <label htmlFor={field['#id']}>{field['#title']}</label>
    <input
      type="checkbox"
      id={field['#id']}
      label={field['#title']}
      name={field['#id']}
      value={value === null ? '' : value}
      required={field['#required']}
      default_value={field['#default_value']}
      hidden={field['#hidden']}
      onChange={(e) => {
        fieldChanged(field['#id'], e.target.value);
      }}
    />
  </div>
);

export {
  TextField,
  TextArea,
  Select,
  Checkbox,
};
