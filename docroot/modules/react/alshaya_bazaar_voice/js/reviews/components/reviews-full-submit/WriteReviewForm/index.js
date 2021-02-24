import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import DynamicFormField from '../DynamicFormField';
import { validateWriteReviewFormInfo, prepareRequest } from '../../../../utilities/write_review_util';
import { postAPIData } from '../../../../utilities/api/apiData';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';

export default class WriteReviewForm extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the review post event.
    document.addEventListener('reviewPosted', this.eventListener, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('reviewPosted', this.eventListener, false);
  }

  handleSubmit = (e) => {
    const { formFieldMeta } = this.props;
    e.preventDefault();

    if (!validateWriteReviewFormInfo(e.target.elements, formFieldMeta)) {
      showFullScreenLoader();
      const request = prepareRequest(e.target.elements, formFieldMeta);
      const apiUri = '/data/submitreview.json';

      // Post the review data to BazaarVoice.
      const apiData = postAPIData(apiUri, request);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            removeFullScreenLoader();
            const evt = new CustomEvent('reviewPosted', { detail: result.data });
            document.dispatchEvent(evt);
          } else {
            removeFullScreenLoader();
            Drupal.logJavascriptError('write-review', result.error);
          }
        });
      }
    } else {
      removeFullScreenLoader();
      Drupal.logJavascriptError('write-review', 'invalid form');
    }
  }

  eventListener = () => {
    const { closeModal } = this.props;
    if (!this.isComponentMounted) {
      return;
    }

    // Todo - to handle to display the post review data on pdp.
    // console.log(e.detail);
    closeModal();
  };

  render() {
    const dynamicFields = [];
    const {
      closeModal,
      formFieldMeta,
    } = this.props;

    Object.entries(formFieldMeta).forEach(
      ([key, field]) => {
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
            <form className="write-review-form-add" onSubmit={this.handleSubmit}>
              <div className="write-review-fields">
                {' '}
                {dynamicFields}
                {' '}
              </div>
              <br />
              <div className="write-review-form-actions" id="review-form-action">
                <button
                  id="cancel-write-review"
                  className="write-review-form-cancel"
                  type="button"
                  name="cancel"
                  onClick={() => closeModal()}
                >
                  {Drupal.t('Cancel')}
                </button>
                <button
                  id="preview-write-review"
                  className="write-review-form-preview"
                  name="submit"
                  type="submit"
                >
                  {Drupal.t('Post review')}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    );
  }
}
