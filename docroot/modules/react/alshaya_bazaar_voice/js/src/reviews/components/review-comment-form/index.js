import React from 'react';
import TextareaAutosize from 'react-autosize-textarea';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import {
  getLanguageCode, getbazaarVoiceSettings, getUserDetails, postAPIData,
} from '../../../utilities/api/request';
import { processFormDetails } from '../../../utilities/validate';
import { validateInputLang, validEmailRegex } from '../../../utilities/write_review_util';
import getStringMessage from '../../../../../../js/utilities/strings';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';

let bazaarVoiceSettings = null;

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
      userDetails: {
        user: {
          userId: 0,
          userEmail: null,
        },
      },
    };
    bazaarVoiceSettings = getbazaarVoiceSettings();
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount = () => {
    getUserDetails().then((result) => {
      this.setState({ userDetails: result });
    });
  }

  showCommentForm = () => {
    const { ReviewId } = this.props;
    const {
      commentbox, nickname, email, userDetails,
    } = this.state;
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
                id={`comment-${ReviewId}`}
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
              <div id={`comment-${ReviewId}-error`} className="error" />
            </div>

            <div className="form-item-two-column">
              <div className="form-item">
                <input
                  type="text"
                  id={`nickname-${ReviewId}`}
                  name="nickname"
                  minLength={bazaarVoiceSettings.reviews.bazaar_voice.screen_name_min_length}
                  maxLength="25"
                  onChange={this.handleNicknameChange}
                  className="form-input"
                  defaultValue={decodeURIComponent(nickname)}
                />
                <div className="c-input__bar" />
                <label className={`form-label ${nickname ? 'active-label' : ''}`}>
                  {getStringMessage('screen_name')}
                  {'*'}
                </label>
                <div id={`nickname-${ReviewId}-error`} className="error" />
              </div>

              <div className="form-item">
                <input
                  type="email"
                  id={`email-${ReviewId}`}
                  name="email"
                  onChange={this.handleEmailChange}
                  className="form-input"
                  defaultValue={email}
                  readOnly={userDetails.user.emailId !== null ? 1 : 0}
                />
                <div className="c-input__bar" />
                <label className={`form-label ${email ? 'active-label' : ''}`}>
                  {getStringMessage('email_address_label')}
                  {'*'}
                </label>
                <div id={`email-${ReviewId}-error`} className="error" />
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
    const { ReviewId } = this.props;
    const { userDetails } = this.state;
    const isError = processFormDetails(e, ReviewId);
    if (!isError) {
      const { commentbox, nickname, email } = this.state;
      const notifications = bazaarVoiceSettings.reviews.bazaar_voice.notify_comment_published;
      const userStorage = getStorageInfo(`bvuser_${userDetails.user.userId}`);
      let storageUpdated = false;
      let authParams = '';
      // Set auth paramters for anonymous users.
      if (userDetails.user.userId === 0 && userStorage !== null) {
        if (userStorage.bvUserId === undefined
          || (userStorage.email !== undefined && userStorage.email !== email)) {
          authParams += `&HostedAuthentication_AuthenticationEmail=${email}&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
        }
      }
      // Set user authenticated string (UAS).
      if (userStorage !== null) {
        if (userDetails.user.userId !== 0 && userStorage.uasToken !== undefined) {
          authParams += `&user=${userStorage.uasToken}&UserNickname=${nickname}`;
          // Update current user in storage.
          userStorage.nickname = nickname;
          storageUpdated = true;
        } else if (userDetails.user.userId === 0 && userStorage.email !== undefined
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
      const params = `&sendemailalertwhenpublished=${notifications}&Action=submit&CommentText=${commentbox}&ReviewId=${ReviewId}${authParams}`;
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
                commentbox: response.Comment.CommentText,
              });
              if (storageUpdated) {
                setStorageInfo(userStorage, `bvuser_${userDetails.user.userId}`);
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
    const label = getStringMessage('email_address_label');
    if (e.target.value.length > 0 && !validEmailRegex.test(e.target.value)) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('valid_email_error', { '%mail': e.target.value });
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else if (e.target.value.length === 0) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': label });
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
      document.getElementById(`${e.target.id}`).classList.remove('error');
    }
    this.setState({ email: encodeURIComponent(e.target.value) });
  }

  handleNicknameChange = (e) => {
    const label = getStringMessage('screen_name');
    if (e.target.value.length > 0 && e.target.value.length < e.target.minLength) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': e.target.minLength, '%fieldTitle': label });
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else if (e.target.value.length === 0) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': label });
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
      document.getElementById(`${e.target.id}`).classList.remove('error');
    }
    this.setState({ nickname: encodeURIComponent(e.target.value) });
  }

  handleCommentboxChange = (e) => {
    const label = getStringMessage('comment');
    if (e.target.value.length > 0 && !validateInputLang(e.target.value)) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('text_input_lang_error');
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else if (e.target.value.length > 0 && e.target.value.length < e.target.minLength) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': e.target.minLength, '%fieldTitle': label });
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else if (e.target.value.length === 0) {
      document.getElementById(`${e.target.id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': label });
      document.getElementById(`${e.target.id}`).classList.add('error');
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
      document.getElementById(`${e.target.id}`).classList.remove('error');
    }
    this.setState({ commentbox: encodeURIComponent(e.target.value) });
  }

  render() {
    const { ReviewId } = this.props;
    const { showCommentForm, showCommentSubmission, userDetails } = this.state;
    const userStorage = getStorageInfo(`bvuser_${userDetails.user.userId}`);
    let emailValue = userDetails.user.emailId;
    let nicknameValue = '';
    // Set default value for user email and nickname.
    if (userStorage !== null) {
      emailValue = userStorage.email !== undefined && emailValue !== '' ? userStorage.email : '';
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
