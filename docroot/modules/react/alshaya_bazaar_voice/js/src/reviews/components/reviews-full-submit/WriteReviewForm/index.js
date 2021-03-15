import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import DynamicFormField from '../DynamicFormField';
import { prepareRequest, validateRequest } from '../../../../utilities/write_review_util';
import { postAPIData } from '../../../../utilities/api/apiData';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import FormLinks from '../DynamicFormField/Fields/FormLinks';
import { getLanguageCode, doRequest, getbazaarVoiceSettings } from '../../../../utilities/api/request';
import ConditionalView from '../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../js/utilities/strings';

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
    const { bazaarVoiceSettings } = this.state;
    const apiUri = `/${getLanguageCode()}/get-write-review-fields-configs`;
    const apiData = doRequest(apiUri);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.status === 200) {
          removeFullScreenLoader();
          let fieldsData = result.data;
          // Hide fields on write a review form based on category configurations.
          if (bazaarVoiceSettings.reviews.hide_fields_write_review.length > 0) {
            const hideFields = bazaarVoiceSettings.reviews.hide_fields_write_review;
            fieldsData = this.getProcessedFields(fieldsData, hideFields);
          }
          this.setState({
            fieldsConfig: fieldsData,
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

    const isError = validateRequest(e.target.elements, fieldsConfig);
    if (isError) {
      return;
    }
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

  getProcessedFields = (fieldsConfig, hideFields) => {
    const fields = fieldsConfig;
    let i = 0;
    let index = 0;
    Object.entries(fieldsConfig).forEach(([key, field]) => {
      if (hideFields.find((id) => id === field['#id']) !== undefined) {
        if (key !== -1) {
          index = key;
          if (i > 0) {
            index = key - i;
          }
          fields.splice(index, 1);
          i += 1;
        }
      }
    });
    return fields;
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
          <SectionTitle>{getStringMessage('write_a_review')}</SectionTitle>
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
          <ConditionalView condition={dynamicFields.length > 0}>
            <div className="write-review-form-wrapper">
              <form className="write-review-form-add" onSubmit={this.handleSubmit} noValidate>
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
                    {getStringMessage('cancel')}
                  </button>
                  <button
                    id="preview-write-review"
                    className="write-review-form-preview"
                    name="submit"
                    type="submit"
                  >
                    {getStringMessage('post_review')}
                  </button>
                </div>
                <FormLinks
                  tnc={getStringMessage('terms_and_condition')}
                  reviewGuide={getStringMessage('review_guidelines')}
                />
              </form>
            </div>
          </ConditionalView>
        </div>
      </div>
    );
  }
}
