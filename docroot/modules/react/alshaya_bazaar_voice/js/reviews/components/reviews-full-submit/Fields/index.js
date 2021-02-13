import React from 'react';

const TextField = ({
  field, fieldChanged,
}) => (
  <div className="bv-type-textfield" key={field['#id']}>
    <input
      type="text"
      id={field['#id']}
      name={field['#id']}
      required={field['#required']}
      minLength={field['#minlength']}
      maxLength={field['#maxlength']}
      default_value={field['#default_value']}
      hidden={field['#hidden']}
      onChange={(e) => {
        fieldChanged(field['#id'], e.target.value);
      }}
    />
    <div className="c-input__bar" />
    <label htmlFor={field['#id']}>{field['#title']}</label>
    <div id="bv-error" className="error" />
  </div>
);

const TextArea = ({
  field, fieldChanged,
}) => (
  <div className="bv-type-textarea" key={field['#id']}>
    <textarea
      id={field['#id']}
      name={field['#id']}
      required={field['#required']}
      minLength={field['#minlength']}
      maxLength={field['#maxlength']}
      default_value={field['#default_value']}
      hidden={field['#hidden']}
      onChange={(e) => {
        fieldChanged(field['#id'], e.value);
      }}
    />
    <div className="c-input__bar" />
    <label htmlFor={field['#id']}>{field['#title']}</label>
    <div id="bv-error" className="error" />
  </div>
);

const Checkbox = ({
  field, fieldChanged,
}) => (
  <div key={field['#id']}>
    <label htmlFor={field['#id']}>{field['#title']}</label>
    <input
      type="checkbox"
      id={field['#id']}
      label={field['#title']}
      name={field['#id']}
      required={field['#required']}
      default_value={field['#default_value']}
      hidden={field['#hidden']}
      onChange={(e) => {
        fieldChanged(field['#id'], e.value);
      }}
    />
  </div>
);

export {
  TextField,
  TextArea,
  Checkbox,
};
