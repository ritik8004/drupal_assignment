import React from 'react';

import axios from 'axios';
import SectionTitle from '../../../utilities/section-title';
import DynamicFormField from '../dynamic-form-field';
import FixedFields from '../fixed-fields';
import {fixedFieldValidation} from '../fixed-fields/validation';

export default class AddressForm extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'area_list': null,
    };
  }

  // Submit handler for form.
  handleSubmit = (e) => {
    e.preventDefault();
    let form_data = this.validateForm(e);

    if (form_data !== false) {
      // Caller component pass the method as props so that
      // we return form info and thus that can be utilized
      // as per requirements.
      this.props.handleAddressData(form_data);
    }
  }

  // Refresh areas list.
  refreshAreas = (area_list) => {
    let data = new Array();
    Object.entries(area_list).forEach(([tid, tname]) => {
      data[tid] = {
        value: tid,
        label: tname,
      };
    });

    this.setState({
      area_list: data
    });
  }

  // Validation handler for the form.
  validateForm = (e) => {
  	// Validation for fixed fields.
  	let valid_form = fixedFieldValidation(e);

    let form_data = {};
    // Validation for dynamic fields.
  	Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      // If field is required.
      if (field.required === true ||
        (key === 'administrative_area' || key === 'area_parent')) {
        let ele_val = e.target.elements[key].value;
        if (ele_val.trim().length === 0){
          document.getElementById(key + '-error').innerHTML = Drupal.t('Please add @field.', {'@field': field.label});
          document.getElementById(key + '-error').classList.add('error');
          valid_form = false;
        }
        else {
          // Remove error class and any error message.
          document.getElementById(key + '-error').innerHTML = '';
          document.getElementById(key + '-error').classList.remove('error');
        }
      }

      form_data[key] = e.target.elements[key].value
    });

  	// If there is any validation fail for form.
  	if (!valid_form) {
  	  return;
  	}

    form_data['static'] = {
      'firstname': e.target.elements.fname.value,
      'lastname': e.target.elements.lname.value,
      'email': e.target.elements.email.value,
      'city': 'Dummy Value',
      'telephone': e.target.elements.mobile.value,
      'country_id': window.drupalSettings.country_code
    };

    return form_data;

  };

  render() {
    let dynamicFields = [];
    let default_val = [];
    if (this.props.default_val) {
      default_val = this.props.default_val;
    }

    Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      dynamicFields.push(<DynamicFormField default_val={default_val} areasUpdate={this.refreshAreas} area_list={this.state.area_list} field_key={key} field={field}/>);
    });

    return(
      <div className="spc-address-form">
        <div className='spc-address-form-map'/>
        <div className='spc-address-form-sidebar'>
          <SectionTitle>{Drupal.t('Delivery information')}</SectionTitle>
          <div className='spc-address-form-wrapper'>
            <div className='spc-deliver-button'>{Drupal.t('Deliver to my location')}</div>
            <form className='spc-address-add' onSubmit={this.handleSubmit}>
              {dynamicFields}
              <FixedFields default_val={default_val} />
              <input type="submit" value={Drupal.t('Save')} />
            </form>
          </div>
        </div>
      </div>
    );
  }

}
