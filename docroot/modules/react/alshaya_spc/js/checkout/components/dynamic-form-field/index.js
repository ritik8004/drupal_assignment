import React from 'react';

import AreaSelect from '../area-select';
import ParentAreaSelect from '../parent-area-select';

export default class DynamicFormField extends React.Component {

  render() {
    if (this.props.field_key === 'administrative_area') {
      return <AreaSelect  area_list={this.props.area_list} field_key={this.props.field_key} field={this.props.field}/>
    }
    else if(this.props.field_key === 'area_parent') {
      return <ParentAreaSelect field_key={this.props.field_key} field={this.props.field} areasUpdate={this.props.areasUpdate}/>
    }

    return (
      <div>
        <label>
           {this.props.field.label}
        </label>
        <input type='text' name={this.props.field_key}/>
        <div id={this.props.field_key + '-error'}></div>
      </div>
    );
  }

}
