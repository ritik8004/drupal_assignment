import React from 'react';
import TextField from './Fields/TextField';
import TextArea from './Fields/TextArea';
import SelectField from './Fields/SelectField';
import Slider from './Fields/Slider';
import Checkbox from './Fields/Checkbox';
import Tags from './Fields/Tags';
import StarRating from './Fields/StarRating';
import PhotoUpload from './Fields/PhotoUpload';
import RadioButton from './Fields/RadioButton';
import NetPromoter from './Fields/NetPromoter';

const DynamicFormField = (props) => {
  const fieldProperty = [];

  const { field: defField } = props;
  if (defField.length !== 0
    && defField.length !== 'undefined') {
    Object.entries(defField).forEach(
      ([key, value]) => {
        const cleanKey = key.replace('#', '');
        fieldProperty[cleanKey] = value;
      },
    );
  }

  if (fieldProperty.group_type === 'checkbox'
    && fieldProperty.visible === true) {
    return (
      <Checkbox
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'tags'
    && fieldProperty.visible === true) {
    return (
      <Tags
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'slider'
    && fieldProperty.visible === true) {
    return (
      <Slider
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        options={fieldProperty.options}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'ratings') {
    return (
      <StarRating
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'select'
    && fieldProperty.visible === true) {
    return (
      <SelectField
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        options={fieldProperty.options}
        visible={fieldProperty.visible}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'textarea'
    && fieldProperty.visible === true) {
    return (
      <TextArea
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        maxLength={fieldProperty.maxlength}
        minLength={fieldProperty.minlength}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'photo'
    && fieldProperty.visible === true) {
    return (
      <PhotoUpload
        fieldProperty={fieldProperty}
      />
    );
  }

  if (fieldProperty.group_type === 'boolean'
    && fieldProperty.visible === true) {
    return (
      <RadioButton
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        text={fieldProperty.text}
      />
    );
  }

  if (fieldProperty.group_type === 'netpromoter'
    && fieldProperty.visible === true) {
    return (
      <NetPromoter
        required={fieldProperty.required}
        id={fieldProperty.id}
        label={fieldProperty.title}
        maxLength={fieldProperty.maxlength}
        defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
        text={fieldProperty.text}
      />
    );
  }

  return (
    <TextField
      required={fieldProperty.required}
      id={fieldProperty.id}
      label={fieldProperty.title}
      defaultValue={fieldProperty.defaultVal !== null ? fieldProperty.defaultVal : null}
      maxLength={fieldProperty.maxlength}
      minLength={fieldProperty.minlength}
      visible={fieldProperty.visible}
      text={fieldProperty.text}
      classLable={fieldProperty.class_name}
    />
  );
};

export default DynamicFormField;
