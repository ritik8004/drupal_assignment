import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import DynamicFormField from '../DynamicFormField';
import { prepareRequest, validateRequest } from '../../../../utilities/write_review_util';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import FormLinks from '../DynamicFormField/Fields/FormLinks';
import {
  getbazaarVoiceSettings, getUserDetails, postAPIData,
} from '../../../../utilities/api/request';
import ConditionalView from '../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { smoothScrollTo } from '../../../../utilities/smoothScroll';
import { setStorageInfo } from '../../../../utilities/storage';
import dispatchCustomEvent from '../../../../../../../js/utilities/events';
import { trackFeaturedAnalytics } from '../../../../utilities/analytics';
import { createUserStorage } from '../../../../utilities/user_util';
import PostReviewMessage from '../post-review-message';

export default class WriteReviewForm extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      fieldsConfig: '',
      postReviewData: '',
      userDetails: {
        user: {
          userId: 0,
          emailId: null,
        },
      },
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
      productId, context,
    } = this.props;
    // Create user token for my account section for each product.
    getUserDetails(productId).then((userDetails) => {
      if (productId !== undefined && context === 'myaccount') {
        createUserStorage(userDetails.user.userId, userDetails.user.emailId, productId);
      }
      this.setState({ userDetails });
    });

    const apiData = window.commerceBackend.getWriteReviewFieldsConfigs(productId);
    apiData.then((result) => {
      if (result.status === 200) {
        removeFullScreenLoader();
        let fieldsData = result.data;
        // Hide fields on write a review form based on category configurations.
        const hiddenFields = window.commerceBackend.getHiddenWriteReviewFields(productId);
        if (hiddenFields.length > 0) {
          fieldsData = this.getProcessedFields(fieldsData, hiddenFields);
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

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('reviewPosted', this.eventListener, false);
    // Fix Warning: Can't perform a React state update on an unmounted component.
    this.setState = () => {};
  }

  handleSubmit = async (e) => {
    const { fieldsConfig } = this.state;
    const { productId, newPdp } = this.props;
    e.preventDefault();

    const isError = validateRequest(e.target.elements, fieldsConfig, e, newPdp);
    if (isError) {
      return;
    }
    showFullScreenLoader();
    const request = await prepareRequest(e.target.elements, fieldsConfig, productId);
    const apiUri = '/data/submitreview.json';

    // Post the review data to BazaarVoice.
    const apiData = postAPIData(apiUri, request.params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          if (result.data.HasErrors && result.data.FormErrors !== null) {
            smoothScrollTo(e, '.write-review-form', 'post_review');
          } else if (request.userStorage !== null) {
            setStorageInfo(request.userStorage, `bvuser_${request.userStorage.id}`);
          }
          // Dispatch event after review submit.
          dispatchCustomEvent('reviewPosted', result.data);
          // Process review submit data as user submits the review.
          const analyticsData = {
            type: 'Used',
            name: 'submit',
            detail1: 'review',
            detail2: 'pdp',
          };
          trackFeaturedAnalytics(analyticsData);
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-write-review-form', result.error);
        }
      });
    }
  }

  eventListener = (e) => {
    const { closeModal, isWriteReview, newPdp } = this.props;
    if (!this.isComponentMounted) {
      return;
    }
    if (!e.detail.HasErrors) {
      if (newPdp && isWriteReview) {
        this.setState({
          postReviewData: e.detail,
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
      closeModal,
      productId,
      isWriteReview,
      newPdp,
    } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
    const {
      fieldsConfig,
      userDetails,
      postReviewData,
    } = this.state;

    Object.entries(fieldsConfig).forEach(
      ([key, field]) => {
        dynamicFields.push(
          <DynamicFormField
            key={key}
            field_key={key}
            field={field}
            productId={productId}
            countryCode={bazaarVoiceSettings.reviews.bazaar_voice.country_code}
            userDetails={userDetails}
          />,
        );
      },
    );

    return (
      <>
        <div className="write-review-form">
          <div className="title-block">
            <SectionTitle>{getStringMessage('write_a_review')}</SectionTitle>
            <a className="close-modal" onClick={(e) => closeModal(e)} />
          </div>
          <ConditionalView condition={postReviewData !== '' && isWriteReview && newPdp}>
            <br />
            <PostReviewMessage postReviewData={postReviewData} />
          </ConditionalView>
          <ConditionalView condition={postReviewData === ''}>
            <BazaarVoiceMessages />
            <div id="product-block" className="product-block">
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
          </ConditionalView>

        </div>
      </>
    );
  }
}
