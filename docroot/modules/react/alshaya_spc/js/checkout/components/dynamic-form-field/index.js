import React from 'react';

import AreaSelect from '../area-select';
import ParentAreaSelect from '../parent-area-select';
import TextField from '../../../utilities/textfield';
import { isFieldDisabled } from '../../../utilities/checkout_util';

const DynamicFormField = (props) => {
  let defaultVal = '';
  const { default_val: defVal, enabledFieldsWithMessages } = props;
  if (defVal.length !== 0
    && defVal.length !== 'undefined') {
    defaultVal = defVal;
  }

  const {
    field_key: fieldKey,
    field,
    area_list: areaList,
    areasUpdate,
  } = props;
  if (fieldKey === 'administrative_area') {
    return (
      <AreaSelect
        cityChanged={props.cityChanged}
        default_val={defaultVal}
        area_list={areaList}
        field_key={fieldKey}
        field={field}
        // This prop is an object where object keys are field-names which will
        // be enabled in the form and values are default message on the field
        // example {mobile: Please update mobile number}
        enabledFieldsWithMessages={enabledFieldsWithMessages}
      />
    );
  }
  if (fieldKey === 'area_parent') {
    return (
      <ParentAreaSelect
        default_val={defaultVal}
        field_key={fieldKey}
        field={field}
        areasUpdate={areasUpdate}
        // This prop is an object where object keys are field-names which will
        // be enabled in the form and values are default message on the field
        // example {mobile: Please update mobile number}
        enabledFieldsWithMessages={enabledFieldsWithMessages}
      />
    );
  }

  return (
    <TextField isAddressField required={field.required} id={fieldKey} type="text" label={field.label} name={fieldKey} defaultValue={defaultVal !== '' ? defaultVal[field.key] : ''} maxLength={field.maxLength} disabled={isFieldDisabled(enabledFieldsWithMessages, fieldKey)} />
  );
};

export default DynamicFormField;
