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
import { getUserDetails } from '../../../../utilities/api/request';
import { getStorageInfo } from '../../../../utilities/storage';

const DynamicFormField = (props) => {
  const fieldProperty = [];
  let readonly = false;

  const { field: defField, productId } = props;
  if (defField.length !== 0
    && defField.length !== 'undefined') {
    Object.entries(defField).forEach(
      ([key, value]) => {
        const cleanKey = key.replace('#', '');
        fieldProperty[cleanKey] = value;
      },
    );
  }

  // Set default value for user nickname and email.
  // For anonymous user, default value is from user cookies.
  const userDetails = getUserDetails(productId);
  const userStorage = getStorageInfo(`bvuser_${userDetails.user.userID}`);
  if (fieldProperty.group_type === 'textfield') {
    if (fieldProperty.id === 'useremail') {
      if (userDetails.user.userEmailID !== '') {
        fieldProperty.defaultVal = userDetails.user.userEmailID;
        readonly = true;
      } else if (userStorage !== null) {
        if (userStorage.email !== undefined) {
          fieldProperty.defaultVal = userStorage.email;
        }
      }
    } else if (fieldProperty.id === 'usernickname' && userStorage !== null) {
      if (userStorage.nickname !== undefined) {
        fieldProperty.defaultVal = userStorage.nickname;
      }
    }
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
        placeholder={fieldProperty.placeholder}
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
        placeholder={fieldProperty.placeholder}
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
      readonly={readonly}
    />
  );
};

export default DynamicFormField;
