import React from 'react';

import AreaSelect from '../area-select';
import ParentAreaSelect from '../parent-area-select';

export default class DynamicFormField extends React.Component {

  render() {
    let default_val = '';
    if (this.props.default_val.length !== 0 && this.props.default_val.length !== 'undefined') {
      default_val = this.props.default_val;
    }

    if (this.props.field_key === 'administrative_area') {
      return <AreaSelect default_val={default_val} area_list={this.props.area_list} field_key={this.props.field_key} field={this.props.field}/>
    }
    else if(this.props.field_key === 'area_parent') {
      return <ParentAreaSelect default_val={default_val} field_key={this.props.field_key} field={this.props.field} areasUpdate={this.props.areasUpdate}/>
    }

    return (
      <div>
        <label>
           {this.props.field.label}
        </label>
        <input
          type='text'
          id={this.props.field_key}
          name={this.props.field_key}
          defaultValue={default_val !== '' ? default_val[this.props.field.key] : ''}
        />
        <div id={this.props.field_key + '-error'}></div>
      </div>
    );
  }

}
