import React from 'react';
import TextareaAutosize from 'react-autosize-textarea';
import { postAPIData } from '../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import { getLanguageCode, getbazaarVoiceSettings } from '../../../utilities/api/request';
import { processFormDetails } from '../../../utilities/validate';
import { validEmailRegex } from '../../../utilities/write_review_util';
import getStringMessage from '../../../../../../js/utilities/strings';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';

const bazaarVoiceSettings = getbazaarVoiceSettings();

class ReviewCommentForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showCommentForm: false,
      showCommentSubmission: false,
      email: '',
      commentbox: '',
      nickname: '',
      submissionTime: '',
    };
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  showCommentForm = () => {
    const { commentbox, nickname, email } = this.state;
    const commentTncUri = `/${getLanguageCode()}/${bazaarVoiceSettings.reviews.bazaar_voice.comment_form_tnc}`;
    return (
      <div className="review-comment-form">
        <form id="comment-form" onSubmit={this.handleSubmit} noValidate>
          <div className="comment-form-title">
            {getStringMessage('post_a_comment')}
          </div>
          <BazaarVoiceMessages />
          <div className="comment-form-fields">
            <input type="hidden" name="blackBox" id="ioBlackBox" />
            <div className="form-item">
              <TextareaAutosize
                type="text"
                id="commentbox"
                name="commentbox"
                minLength={bazaarVoiceSettings.reviews.bazaar_voice.comment_box_min_length}
                maxLength={bazaarVoiceSettings.reviews.bazaar_voice.comment_box_max_length}
                onChange={this.handleCommentboxChange}
                className="form-input"
                defaultValue={commentbox}
              />
              <div className="c-input__bar" />
              <label className={`form-label ${commentbox ? 'active-label' : ''}`}>
                {getStringMessage('comment')}
                {'*'}
              </label>
              <div id="commentbox-error" className="error" />
            </div>

            <div className="form-item-two-column">
              <div className="form-item">
                <input
                  type="text"
                  id="nickname"
                  name="nickname"
                  minLength={bazaarVoiceSettings.reviews.bazaar_voice.screen_name_min_length}
                  onChange={this.handleNicknameChange}
                  className="form-input"
                  defaultValue={nickname}
                />
                <div className="c-input__bar" />
                <label className={`form-label ${nickname ? 'active-label' : ''}`}>
                  {getStringMessage('screen_name')}
                  {'*'}
                </label>
                <div id="nickname-error" className="error" />
              </div>

              <div className="form-item">
                <input
                  type="email"
                  id="email"
                  name="email"
                  onChange={this.handleEmailChange}
                  className="form-input"
                  defaultValue={email}
                  readOnly={bazaarVoiceSettings.reviews.user.email !== null ? 1 : 0}
                />
                <div className="c-input__bar" />
                <label className={`form-label ${email ? 'active-label' : ''}`}>
                  {getStringMessage('email_address_label')}
                  {'*'}
                </label>
                <div id="email-error" className="error" />
              </div>
            </div>

            <div className="terms-conditions">
              <a href={commentTncUri} target="_blank" rel="noopener noreferrer">{getStringMessage('terms_and_condition')}</a>
            </div>

            <div className="form-button-wrapper">
              <button className="form-cancel-btn" onClick={() => this.setState({ showCommentForm: false })} type="button">{getStringMessage('cancel')}</button>
              <button type="submit" className="form-submit-btn">{getStringMessage('post_comment')}</button>
            </div>
          </div>
        </form>
      </div>
    );
  }

  showCommentSubmission = () => {
    const { commentbox, nickname, submissionTime } = this.state;
    return (
      <ReviewCommentSubmission
        UserNickname={nickname}
        SubmissionTime={submissionTime}
        CommentText={commentbox}
      />
    );
  }

  handleSubmit = (e) => {
    e.preventDefault();
    const isError = processFormDetails(e);
    if (!isError) {
      const { ReviewId } = this.props;
      const { commentbox, nickname, email } = this.state;
      const userId = bazaarVoiceSettings.reviews.user.id;
      const userStorage = getStorageInfo(`bvuser_${userId}`);
      let storageUpdated = false;
      let authParams = '';
      // Set auth paramters for anonymous users.
      if (userId === 0 && userStorage !== null) {
        if (userStorage.bvUserId === undefined
          || (userStorage.email !== undefined && userStorage.email !== email)) {
          authParams += `&HostedAuthentication_AuthenticationEmail=${email}&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
        }
      }
      // Set user authenticated string (UAS).
      if (userStorage !== null) {
        if (userId !== 0 && userStorage.uasToken !== undefined) {
          authParams += `&user=${userStorage.uasToken}&UserNickname=${nickname}`;
          // Update current user in storage.
          userStorage.nickname = nickname;
          storageUpdated = true;
        } else if (userId === 0 && userStorage.email !== undefined
          && userStorage.bvUserId !== undefined
          && userStorage.nickname !== undefined) {
          if (userStorage.nickname !== nickname) {
            authParams += `&UserNickname=${nickname}`;
            userStorage.nickname = nickname;
            storageUpdated = true;
          }
          authParams += `&User=${userStorage.bvUserId}`;
        } else {
          authParams += `&UserEmail=${email}&UserNickname=${nickname}`;
        }
      } else {
        authParams += `&UserEmail=${email}&UserNickname=${nickname}`;
      }
      // Add device finger printing string.
      if (e.target.elements.blackBox.value !== '') {
        authParams += `&fp=${encodeURIComponent(e.target.elements.blackBox.value)}`;
      }
      const params = `&Action=submit&CommentText=${commentbox}&ReviewId=${ReviewId}${authParams}`;
      const apiData = postAPIData('/data/submitreviewcomment.json', params);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined
        && result.data !== undefined
        && result.data.error === undefined) {
            const response = result.data;
            if (result.status !== 200
              || (response.HasErrors
              && response.FormErrors !== null)) {
              this.setState({
                showCommentSubmission: false,
              });
              return;
            }
            if (response.SubmissionId !== null) {
              this.setState({
                submissionTime: response.Comment.SubmissionTime,
                showCommentSubmission: true,
                showCommentForm: false,
              });
              if (storageUpdated) {
                setStorageInfo(userStorage, `bvuser_${userId}`);
              }
            }
          } else {
            Drupal.logJavascriptError('review-comment-submit', result.error);
          }
        });
      }
    }
  }

  handleEmailChange = (e) => {
    if (e.target.value.length > 0) {
      document.getElementById(`${e.target.id}-error`).innerHTML = validEmailRegex.test(e.target.value)
        ? '' : getStringMessage('valid_email_error', { '%mail': e.target.value });
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
    }
    this.setState({ email: e.target.value });
  }

  handleNicknameChange = (e) => {
    if (e.target.value.length > 0) {
      const label = getStringMessage('screen_name');
      document.getElementById(`${e.target.id}-error`).innerHTML = e.target.value.length < e.target.minLength
        ? getStringMessage('text_min_chars_limit_error', { '%minLength': e.target.minLength, '%fieldTitle': label })
        : '';
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
    }
    this.setState({ nickname: e.target.value });
  }

  handleCommentboxChange = (e) => {
    if (e.target.value.length > 0) {
      const label = getStringMessage('comment');
      document.getElementById(`${e.target.id}-error`).innerHTML = e.target.value.length < e.target.minLength
        ? getStringMessage('text_min_chars_limit_error', { '%minLength': e.target.minLength, '%fieldTitle': label })
        : '';
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
    }
    this.setState({ commentbox: e.target.value });
  }

  render() {
    const { ReviewId } = this.props;
    const { showCommentForm, showCommentSubmission } = this.state;
    const userStorage = getStorageInfo(`bvuser_${bazaarVoiceSettings.reviews.user.id}`);
    let emailValue = '';
    let nicknameValue = '';
    // Set default value for user email.
    if (bazaarVoiceSettings.reviews.user.email !== null) {
      emailValue = bazaarVoiceSettings.reviews.user.email;
    } else if (userStorage !== null) {
      emailValue = userStorage.email !== undefined ? userStorage.email : '';
    }
    // Set default value for user nickname.
    if (userStorage !== null) {
      nicknameValue = userStorage.nickname !== undefined ? userStorage.nickname : '';
    }

    if (ReviewId !== undefined) {
      return (
        <>
          <div className="review-feedback-comment">
            <span className={`feedback-comment ${showCommentForm ? 'feedback-comment-disabled' : 'feedback-comment-active'}`}>
              <button
                className="review-feedback-comment-btn"
                onClick={() => this.setState({
                  showCommentForm: true, showCommentSubmission: false, email: emailValue, nickname: nicknameValue, commentbox: '',
                })}
                type="button"
                disabled={showCommentForm}
              >
                {getStringMessage('comment')}
              </button>
            </span>
          </div>
          {showCommentForm ? this.showCommentForm() : null}
          {showCommentSubmission ? this.showCommentSubmission() : null}
        </>
      );
    }
    return (null);
  }
}

export default ReviewCommentForm;
