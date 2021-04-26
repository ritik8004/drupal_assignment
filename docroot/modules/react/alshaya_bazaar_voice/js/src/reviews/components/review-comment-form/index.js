import React from 'react';
import TextareaAutosize from 'react-autosize-textarea';
import { postAPIData } from '../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import {
  getCurrentUserEmail, getSessionCookie, setSessionCookie, deleteSessionCookie, getCurrentUserName,
} from '../../../utilities/user_util';
import { getLanguageCode, getbazaarVoiceSettings } from '../../../utilities/api/request';
import { processFormDetails } from '../../../utilities/validate';
import { validEmailRegex } from '../../../utilities/write_review_util';
import getStringMessage from '../../../../../../js/utilities/strings';

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
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const commentTncUri = `/${getLanguageCode()}${bazaarVoiceSettings.reviews.bazaar_voice.comment_form_tnc}`;
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
                  readOnly={(getCurrentUserEmail() !== null) ? 1 : 0}
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
      const bazaarVoiceSettings = getbazaarVoiceSettings();
      if (getSessionCookie('BvUserEmail') !== null && getSessionCookie('BvUserEmail') !== email) {
        const cookieValues = ['BvUserEmail', 'BvUserNickname', 'BvUserId'];
        deleteSessionCookie(cookieValues);
      }
      let authParams = '';
      if (getCurrentUserEmail() === null && getSessionCookie('BvUserEmail') === null) {
        authParams += `&HostedAuthentication_AuthenticationEmail=${email}&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
      }
      const currentUserKey = `uas_token_${bazaarVoiceSettings.reviews.user.user_id}`;
      // Set user authenticated string (UAS).
      if (getCurrentUserEmail() !== null && getSessionCookie(currentUserKey) !== undefined) {
        authParams += `&user=${getSessionCookie(currentUserKey)}`;
        setSessionCookie('BvUserNickname', nickname);
      }

      if (getSessionCookie('BvUserId') !== null && getSessionCookie('BvUserEmail') !== null
        && getSessionCookie('BvUserNickname') !== null) {
        if (getSessionCookie('BvUserNickname') !== nickname) {
          authParams += `&UserNickname=${nickname}`;
          setSessionCookie('BvUserNickname', nickname);
        }
        authParams += `&User=${getSessionCookie('BvUserId')}`;
      } else {
        authParams += `&UserEmail=${email}&UserNickname=${nickname}`;
      }
      // Add device finger printing string.
      if (e.target.elements.blackBox.value !== '') {
        authParams += `&fp=${e.target.elements.blackBox.value}`;
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
    let emailValue = '';
    let nicknameValue = '';
    // Set default value for user email.
    if (getCurrentUserEmail() !== null) {
      emailValue = getCurrentUserEmail();
    } else if (getSessionCookie('BvUserEmail') !== null) {
      emailValue = getSessionCookie('BvUserEmail');
    }
    // Set default value for user nickname.
    if (getSessionCookie('BvUserNickname') !== null) {
      nicknameValue = getSessionCookie('BvUserNickname');
    } else if (getCurrentUserName() !== null) {
      nicknameValue = getCurrentUserName();
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
