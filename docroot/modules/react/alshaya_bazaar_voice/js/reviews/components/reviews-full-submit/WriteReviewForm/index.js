import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import DynamicFormField from '../DynamicFormField';

export default class WriteReviewForm extends React.Component {
  handleSubmit = () => {
    // e.preventDefault();
    // if (validateForm(this.state.errors)) {
    //   console.info('Valid Form');
    // } else {
    //   console.error('Invalid Form');
    // }
  }

  render() {
    const dynamicFields = [];
    const {
      closeModal,
      formData,
    } = this.props;

    Object.entries(formData).forEach(
      ([key, field]) => {
        // console.log(key);
        // console.log(field);
        dynamicFields.push(
          <DynamicFormField
            key={key}
            field_key={key}
            field={field}
          />,
        );
      },
    );
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
            <form
              className="write-review-form-add"
              onSubmit={(e) => this.handleSubmit(e)}
            >
              <div className="write-review-fields">
                {dynamicFields}
              </div>
              <div className="write-review-form-actions" id="review-form-action">
                <button
                  id="cancel-write-review"
                  className="write-review-form-cancel"
                  type="button"
                >
                  {Drupal.t('Cancel')}
                </button>
                <button
                  id="preview-write-review"
                  className="write-review-form-preview"
                  type="submit"
                >
                  {Drupal.t('Preview')}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    );
  }
}
