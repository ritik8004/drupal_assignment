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
import smoothScrollTo from '../../../../utilities/smoothScroll';
import { setStorageInfo } from '../../../../utilities/storage';
import PostReviewMessage from '../post-review-message';

export default class WriteReviewForm extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      fieldsConfig: '',
      myaccountReview: '',
    };

    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the review post event.
    document.addEventListener('reviewPosted', this.eventListener, false);
    // Load and display write a review form.
    showFullScreenLoader();
    const {
      productId,
    } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
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
    // Fix Warning: Can't perform a React state update on an unmounted component.
    this.setState = () => {};
  }

  handleSubmit = (e) => {
    const { fieldsConfig } = this.state;
    const { productId } = this.props;
    e.preventDefault();

    const isError = validateRequest(e.target.elements, fieldsConfig, e);
    if (isError) {
      return;
    }
    showFullScreenLoader();
    const request = prepareRequest(e.target.elements, fieldsConfig, productId);
    const apiUri = '/data/submitreview.json';

    // Post the review data to BazaarVoice.
    const apiData = postAPIData(apiUri, request.params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          if (result.data.HasErrors && result.data.FormErrors !== null) {
            smoothScrollTo(e, '.title-block', 'post_review');
          } else if (request.userStorage !== null) {
            setStorageInfo(request.userStorage, `bvuser_${request.userStorage.id}`);
          }
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

    const { context } = this.props;

    if (!e.detail.HasErrors) {
      if (context === 'myaccount') {
        this.setState({
          myaccountReview: e.detail,
        });
      } else {
        closeModal(e);
      }
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
      closeModal, productId,
    } = this.props;

    const {
      fieldsConfig, myaccountReview,
    } = this.state;

    Object.entries(fieldsConfig).forEach(
      ([key, field]) => {
        dynamicFields.push(
          <DynamicFormField
            key={key}
            field_key={key}
            field={field}
            productId={productId}
          />,
        );
      },
    );
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
    if (myaccountReview !== '') {
      return (
        <>
          <div className="write-review-form">
            <div className="title-block">
              <SectionTitle>{getStringMessage('write_a_review')}</SectionTitle>
              <a className="close-modal" onClick={(e) => closeModal(e)} />
            </div>
            <PostReviewMessage postReviewData={myaccountReview} />
          </div>
        </>
      );
    }
    return (
      <>
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
                  <div className="write-review-fields clearfix">
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
                    productId={productId}
                  />
                </form>
              </div>
            </ConditionalView>
          </div>
        </div>
      </>
    );
  }
}
