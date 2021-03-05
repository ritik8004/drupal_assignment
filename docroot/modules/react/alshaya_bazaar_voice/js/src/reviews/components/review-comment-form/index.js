import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import { getCurrentUserEmail } from '../../../utilities/user_util';
import { getLanguageCode, getbazaarVoiceSettings } from '../../../utilities/api/request';

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
    this.getUserEmail = this.getUserEmail.bind(this);
  }

  componentDidMount() {
    this.getUserEmail();
  }

  getUserEmail() {
    const emailValue = getCurrentUserEmail();
    this.setState({ email: emailValue });
    return emailValue;
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
            <div className="form-item">
              <input type="text" id="commentbox" required="required" className="form-input" value={commentbox || ''} onChange={this.handleCommentboxChange} name="commentbox" />
              <label className="comment-form-commentbox-label form-label">{Drupal.t('Comment')}</label>
            </div>

            <div className="form-item-two-column">
              <div className="form-item">
                <input type="text" className="form-input" required="required" id="nickname" value={nickname || ''} onChange={this.handleNicknameChange} name="nickname" />
                <label className="comment-form-nickname form-label">{Drupal.t('Screen name')}</label>
              </div>

              <div className="form-item">
                <input type="email" className="form-input" required="required" id="email" value={email || ''} onChange={this.handleEmailChange} name="email" />
                <label className="comment-form-email form-label">{Drupal.t('Email Address')}</label>
              </div>
            </div>

            <div className="terms-conditions">
              <a href={commentTncUri} target="_blank" rel="noopener noreferrer">{Drupal.t('Terms and condition')}</a>
            </div>

            <div className="form-button-wrapper">
              <button className="form-cancel-btn" onClick={() => this.setState({ showCommentForm: false })} type="button">{Drupal.t('cancel')}</button>
              <button type="submit" className="form-submit-btn" id="review-comments-submit">{Drupal.t('post comment')}</button>
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
    const { commentbox, nickname, email } = this.state;
    const params = `&Action=submit&CommentText=${commentbox}&UserEmail=${email}&UserNickname=${nickname}&ReviewId=${ReviewId}`;
    const apiData = postAPIData('/data/submitreviewcomment.json', params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
      && result.data !== undefined
      && result.data.error === undefined) {
          const response = result.data;
          if (response.SubmissionId !== null) {
            this.setState({
              submissionTime: response.Comment.SubmissionTime,
              showCommentSubmission: true,
              showCommentForm: false,
            });
          }
        }
      });
    }
  }

  handleEmailChange = (e) => {
    this.setState({ email: e.target.value });
  }

  handleNicknameChange = (e) => {
    this.setState({ nickname: e.target.value });
  }

  handleCommentboxChange = (e) => {
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
              <button className="review-feedback-comment-btn" onClick={() => this.setState({ showCommentForm: true })} type="button" disabled={showCommentForm}>{Drupal.t('comment')}</button>
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
