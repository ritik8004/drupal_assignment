import React from 'react';
import Cookies from 'js-cookie';
import { postAPIData } from '../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import { getCurrentUserEmail, getSessionCookie } from '../../../utilities/user_util';
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
  }

  componentDidMount() {
    if (getCurrentUserEmail() !== null) {
      this.setState({ email: getCurrentUserEmail() });
    }
  }

  showCommentForm = () => {
    const { commentbox, nickname, email } = this.state;
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const commentTncUri = `/${getLanguageCode()}${bazaarVoiceSettings.reviews.bazaar_voice.comment_form_tnc}`;
    return (
      <div className="review-comment-form">
        <form id="comment-form" onSubmit={this.handleSubmit}>
          <div className="comment-form-title">
            {Drupal.t('Post a Comment')}
          </div>
          <div className="comment-form-fields">
            <input type="hidden" name="blackBox" id="ioBlackBox" />
            <div className="form-item">
              <input
                type="text"
                id="commentbox"
                name="commentbox"
                minLength={bazaarVoiceSettings.reviews.bazaar_voice.comment_form_box_length}
                onChange={this.handleCommentboxChange}
                className="form-input"
                defaultValue={commentbox}
              />
              <div className="c-input__bar" />
              <label className={`form-label ${commentbox ? 'active-label' : ''}`}>
                {Drupal.t('Comment')}
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
                  {Drupal.t('Screen name')}
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
                />
                <div className="c-input__bar" />
                <label className={`form-label ${email ? 'active-label' : ''}`}>
                  {Drupal.t('Email Address')}
                  {'*'}
                </label>
                <div id="email-error" className="error" />
              </div>
            </div>

            <div className="terms-conditions">
              <a href={commentTncUri} target="_blank" rel="noopener noreferrer">{Drupal.t('Terms and condition')}</a>
            </div>

            <div className="form-button-wrapper">
              <button className="form-cancel-btn" onClick={() => this.setState({ showCommentForm: false })} type="button">{Drupal.t('cancel')}</button>
              <button type="submit" className="form-submit-btn">{Drupal.t('post comment')}</button>
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
      if (Cookies.get('BvUserEmail') && Cookies.get('BvUserEmail') !== email) {
        Cookies.remove('BvUserEmail');
        Cookies.remove('BvUserNickname');
        Cookies.remove('BVUserId');
      }
      let authParams = '';
      if (getCurrentUserEmail() === null && !(Cookies.get('BvUserEmail'))) {
        authParams += `&HostedAuthentication_AuthenticationEmail=${email}&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
      }
      // Set user authenticated string (UAS).
      if (getCurrentUserEmail() !== null && getSessionCookie() !== undefined) {
        authParams += `&user=${getSessionCookie()}`;
      }

      if (Cookies.get('BVUserId') && Cookies.get('BvUserEmail')
        && Cookies.get('BvUserNickname')) {
        if (Cookies.get('BvUserNickname') !== nickname) {
          authParams += `&UserNickname=${nickname}`;
          Cookies.set('BvUserNickname', nickname);
        }
        authParams += `&User=${Cookies.get('BVUserId')}`;
      } else {
        authParams += `&UserEmail=${email}&UserNickname=${nickname}`;
      }
      // Add device finger printing string.
      // if (e.target.elments.blackBox.value !== '') {
      //   authParams += `&fp=${e.target.elments.blackBox.value}`;
      // }
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
      document.getElementById(`${e.target.id}-error`).innerHTML = e.target.value.length < e.target.minLength
        ? getStringMessage('text_min_chars_limit_error', { '%minLength': e.target.minLength })
        : '';
    } else {
      document.getElementById(`${e.target.id}-error`).innerHTML = '';
    }
    this.setState({ commentbox: e.target.value });
  }

  render() {
    const { ReviewId } = this.props;
    const { showCommentForm, showCommentSubmission } = this.state;
    if (ReviewId !== undefined) {
      return (
        <>
          <div className="review-feedback-comment">
            <span className={`feedback-comment ${showCommentForm ? 'feedback-comment-disabled' : 'feedback-comment-active'}`}>
              <button
                className="review-feedback-comment-btn"
                onClick={() => this.setState({
                  showCommentForm: true, showCommentSubmission: false, email: Cookies.get('BvUserEmail') ? Cookies.get('BvUserEmail') : '', nickname: Cookies.get('BvUserNickname') ? Cookies.get('BvUserNickname') : '', commentbox: '',
                })}
                type="button"
                disabled={showCommentForm}
              >
                {Drupal.t('comment')}
              </button>
            </span>
          </div>
          {showCommentForm ? this.showCommentForm() : null}
          {showCommentSubmission ? this.showCommentSubmission() : null}
          {showCommentForm
           && (<BazaarVoiceMessages />)}
        </>
      );
    }
    return (null);
  }
}

export default ReviewCommentForm;
