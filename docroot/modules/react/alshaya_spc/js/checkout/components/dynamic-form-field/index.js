import React from 'react';

import AreaSelect from '../area-select';
import ParentAreaSelect from '../parent-area-select';
import TextField from '../../../utilities/textfield';

const DynamicFormField = (props) => {
  let defaultVal = '';
  const { default_val: defVal, enableFields } = props;
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
        enableFields={enableFields}
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
        enableFields={enableFields}
      />
    );
  }

  return (
    <TextField isAddressField required={field.required} id={fieldKey} type="text" label={field.label} name={fieldKey} defaultValue={defaultVal !== '' ? defaultVal[field.key] : ''} maxLength={field.maxLength} disabled={(enableFields && enableFields.length > 0 && enableFields.indexOf(fieldKey) === -1) ? 'disabled' : undefined} />
  );
};

export default DynamicFormField;
