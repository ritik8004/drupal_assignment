import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import DynamicFormField from '../DynamicFormField';
import { prepareRequest } from '../../../../utilities/write_review_util';
import { postAPIData } from '../../../../utilities/api/apiData';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import FormLinks from '../DynamicFormField/Fields/FormLinks';
import { getLanguageCode, doRequest, getbazaarVoiceSettings } from '../../../../utilities/api/request';

export default class WriteReviewForm extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      fieldsConfig: '',
      bazaarVoiceSettings: getbazaarVoiceSettings(),
    };

    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the review post event.
    document.addEventListener('reviewPosted', this.eventListener, false);
    // Load and display write a review form.
    showFullScreenLoader();
    const apiUri = `/${getLanguageCode()}/get-write-review-fields-configs`;
    const apiData = doRequest(apiUri);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.status === 200) {
          removeFullScreenLoader();
          this.setState({
            fieldsConfig: result.data,
          });
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-write-review-form', result.error);
        }
      });
    }
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('reviewPosted', this.eventListener, false);
  }

  handleSubmit = (e) => {
    const { fieldsConfig } = this.state;
    e.preventDefault();

    showFullScreenLoader();
    const request = prepareRequest(e.target.elements, fieldsConfig);
    const apiUri = '/data/submitreview.json';

    // Post the review data to BazaarVoice.
    const apiData = postAPIData(apiUri, request);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          // Dispatch event after review submit.
          const event = new CustomEvent('reviewPosted', { detail: result.data });
          document.dispatchEvent(event);
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-write-review-form', result.error);
        }
      });
    }
  }

  eventListener = (e) => {
    const { closeModal } = this.props;
    if (!this.isComponentMounted) {
      return;
    }

    if (!e.detail.HasErrors) {
      closeModal(e);
    }
  };

  render() {
    const dynamicFields = [];
    const {
      closeModal,
    } = this.props;

    const {
      fieldsConfig,
      bazaarVoiceSettings,
    } = this.state;

    Object.entries(fieldsConfig).forEach(
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
          <a className="close-modal" onClick={(e) => closeModal(e)} />
        </div>
        <BazaarVoiceMessages />
        <div className="product-block">
          <div className="product-image-block">
            <img src={bazaarVoiceSettings.reviews.product.image_url} />
          </div>
          <div className="product-title">
            <span>{bazaarVoiceSettings.reviews.product.title}</span>
          </div>
        </div>
        <div className="write-review-form-sidebar">
          {dynamicFields.length > 0
            && (
            <div className="write-review-form-wrapper">
              <form className="write-review-form-add" onSubmit={this.handleSubmit}>
                <div className="write-review-fields">
                  {dynamicFields}
                  <input type="hidden" name="blackBox" id="ioBlackBox" />
                </div>
                <br />
                <div className="write-review-form-actions" id="review-form-action">
                  <button
                    id="cancel-write-review"
                    className="write-review-form-cancel"
                    type="button"
                    name="cancel"
                    onClick={(e) => closeModal(e)}
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
                <FormLinks
                  tnc={Drupal.t('Terms & Conditions')}
                  reviewGuide={Drupal.t('Review Guidelines')}
                />
              </form>
            </div>
            )}
        </div>
      </div>
    );
  }
}
