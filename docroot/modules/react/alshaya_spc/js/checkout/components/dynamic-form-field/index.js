import React from 'react';

import AreaSelect from '../area-select';
import ParentAreaSelect from '../parent-area-select';
import TextField from '../../../utilities/textfield';

const DynamicFormField = (props) => {
  let defaultVal = '';
  if (props.default_val.length !== 0
    && props.default_val.length !== 'undefined') {
    defaultVal = props.default_val;
  }

  const {
    field_key, field, area_list, areasUpdate,
  } = props;
  if (field_key === 'administrative_area') {
    return (
      <AreaSelect
        cityChanged={props.cityChanged}
        default_val={defaultVal}
        area_list={area_list}
        field_key={field_key}
        field={field}
      />
    );
  }
  if (field_key === 'area_parent') {
    return (
      <ParentAreaSelect
        default_val={defaultVal}
        field_key={field_key}
        field={field}
        areasUpdate={areasUpdate}
      />
    );
  }

  return (
    <TextField isAddressField required={props.field.required} id={props.field_key} type="text" label={props.field.label} name={props.field_key} defaultValue={defaultVal !== '' ? defaultVal[props.field.key] : ''} />
  );
};

export default DynamicFormField;
