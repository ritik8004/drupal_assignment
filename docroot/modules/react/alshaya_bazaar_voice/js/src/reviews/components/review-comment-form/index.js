import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import { getCurrentUserEmail } from '../../../utilities/user_util';
import { getLanguageCode, getbazaarVoiceSettings } from '../../../utilities/api/request';
import { processFormDetails } from '../../../utilities/validate';
import TextField from '../reviews-full-submit/DynamicFormField/Fields/TextField';

class ReviewCommentForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showCommentForm: false,
      showCommentSubmission: false,
      email: '',
      comment: '',
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
    const { comment, nickname, email } = this.state;
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
              <TextField
                required
                id="comment"
                label="Comment"
                defaultValue={comment}
                minLength={100}
                visible
                classLable="form-input focus"
              />
            </div>
            <div className="form-item-two-column">
              <div className="form-item">
                <TextField
                  required
                  id="nickname"
                  label="Nickname"
                  defaultValue={nickname}
                  visible
                  classLable="form-input focus"
                />
              </div>

              <div className="form-item">
                <TextField
                  required
                  id="email"
                  label="Email"
                  defaultValue={email}
                  visible
                  classLable="form-input focus"
                />
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
    const { comment, nickname, submissionTime } = this.state;
    return (
      <ReviewCommentSubmission
        UserNickname={nickname}
        SubmissionTime={submissionTime}
        CommentText={comment}
      />
    );
  }

  handleSubmit = (e) => {
    e.preventDefault();
    this.setState({
      email: e.target.elements.email.value,
      nickname: e.target.elements.nickname.value,
      comment: e.target.elements.comment.value,
    });
    const isError = processFormDetails(e);
    if (!isError) {
      const { ReviewId } = this.props;
      const { comment, nickname, email } = this.state;
      const params = `&Action=submit&CommentText=${comment}&UserEmail=${email}&UserNickname=${nickname}&ReviewId=${ReviewId}`;
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
          }
        });
      }
    }
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
