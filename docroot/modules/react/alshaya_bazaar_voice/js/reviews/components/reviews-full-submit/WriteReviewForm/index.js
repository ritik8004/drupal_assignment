import React from 'react';
import {
  TextField, TextArea, Select, Checkbox,
} from '../Fields';
import SectionTitle from '../../../../utilities/section-title';

export default class WriteReviewForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  handleSubmit = (event) => {
    event.preventDefault();
    // console.log(fieldId);
    // console.log(value);
  }

  // fieldChanged = (fieldId, value) => {
  //   // console.log(fieldId);
  //   // console.log(value);
  // };

  render() {
    const {
      closeModal,
      formData,
    } = this.props;
    return (
      <div className="write-review-form">
        <div className="write-review-form-sidebar">
          <SectionTitle>{Drupal.t('Write a review')}</SectionTitle>
          <a className="close" onClick={() => closeModal()}>
            &times;
          </a>
          <div className="write-review-form-wrapper">
            <form onSubmit={this.handleSubmit} noValidate>
              {Object.keys(formData).map((fieldData) => {
                switch (formData[fieldData]['#type']) {
                  case 'textfield':
                    return (
                      <TextField
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        value={formData[fieldData]['#value']}
                        key={formData[fieldData]['#id']}
                      />
                    );
                  case 'textarea':
                    return (
                      <TextArea
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        value={formData[fieldData]['#value']}
                        key={formData[fieldData]['#id']}
                      />
                    );
                  case 'select':
                    return (
                      <Select
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        value={formData[fieldData]['#value']}
                        key={formData[fieldData]['#id']}
                      />
                    );
                  case 'checkbox':
                    return (
                      <Checkbox
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        value={formData[fieldData]['#value']}
                        key={formData[fieldData]['#id']}
                      />
                    );
                  default:
                    return (
                      <div>{Drupal.t('No field type is found.')}</div>
                    );
                }
              })}
              <div className="cancel">
                <button type="button">{Drupal.t('Cancel')}</button>
              </div>
              <div className="preview">
                <button type="submit">{Drupal.t('Preview')}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    );
  }
}
