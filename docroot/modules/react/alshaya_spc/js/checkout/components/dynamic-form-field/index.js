import React from 'react';

import AreaSelect from '../area-select';
import ParentAreaSelect from '../parent-area-select';

export default class DynamicFormField extends React.Component {

  render() {
    let default_val = '';
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      default_val = this.props.default_val;
    }

    const { field_key, field, area_list, areasUpdate } = this.props;
    if (field_key === 'administrative_area') {
      return <AreaSelect default_val={default_val} area_list={area_list} field_key={field_key} field={field}/>
    }
    else if(field_key === 'area_parent') {
      return <ParentAreaSelect default_val={default_val} field_key={field_key} field={field} areasUpdate={areasUpdate}/>
    }

    return (
      <div className='spc-type-textfield'>
        <input id={this.props.field_key} type='text' required='required' name={this.props.field_key} defaultValue={default_val !== '' ? default_val[this.props.field.key] : ''}/>
        <div className='c-input__bar'/>
        <label>{this.props.field.label}</label>
        <div id={this.props.field_key + '-error'} className='error'></div>
      </div>
    );
  }

}
