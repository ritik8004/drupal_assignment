import React from 'react';
import {
  TextField, TextArea, Checkbox,
} from '../Fields';
import SelectField from '../Fields/SelectField';
import SectionTitle from '../../../../utilities/section-title';
import SelectStar from '../Fields/SelectStar';
import RangeSlider from '../Fields/RangeSlider';

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

  fieldChanged = (e) => {
    e.preventDefault();
    // console.log(fieldId);
    // console.log(value);
  };

  render() {
    const {
      closeModal,
      formData,
    } = this.props;
    return (
      <div className="write-review-form">
        <div className="title-block">
          <SectionTitle>{Drupal.t('Write a Review')}</SectionTitle>
          <a className="close-modal" onClick={() => closeModal()} />
        </div>
        <div className="product-block">
          <div className="product-image-block">
            <img src="https://www.americaneagle.ae/sites/g/files/bndsjb1116/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/AmericanEagle/0153_2107_401/0153_2107_401_f.690738.jpg?itok=YZdkU4f6" />
          </div>
          <div className="product-title">
            <span>product title</span>
          </div>
        </div>
        <div className="write-review-form-sidebar">
          <div className="write-review-form-wrapper">
            <form onSubmit={this.handleSubmit} noValidate>
              {Object.keys(formData).map((fieldData) => {
                switch (formData[fieldData]['#type']) {
                  case 'textfield':
                    if ((formData[fieldData]['#id']) === 'rating') {
                      return (<SelectStar />);
                    }
                    return (
                      <TextField
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        key={formData[fieldData]['#id']}
                      />
                    );

                  case 'textarea':
                    return (
                      <TextArea
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        key={formData[fieldData]['#id']}
                      />
                    );
                  case 'select':
                    if ((formData[fieldData]['#id']) === 'rating_Fit_22') {
                      return (<RangeSlider field={formData[fieldData]} />);
                    }
                    return (
                      <SelectField
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
                        key={formData[fieldData]['#id']}
                      />
                    );

                  case 'checkbox':
                    return (
                      <Checkbox
                        field={formData[fieldData]}
                        fieldChanged={this.fieldChanged}
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
