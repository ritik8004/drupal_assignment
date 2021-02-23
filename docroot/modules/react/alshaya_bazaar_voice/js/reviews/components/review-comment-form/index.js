import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../common/components/bazaarvoice-messages';
import ReviewCommentSubmission from '../review-comment-submission';
import { getCurrentUserEmail } from '../../../utilities/utility';

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
    return (
      <div>
        <form id="comment-form" onSubmit={this.handleSubmit}>
          <div className="comment-form-title">
            {Drupal.t('Post a Comment')}
            <div className="comment-form-fields">
              <label className="comment-form-commentbox-label">{Drupal.t('Comment')}</label>
              <input type="text" id="commentbox" value={commentbox || ''} onChange={this.handleCommentboxChange} name="commentbox" />

              <label className="comment-form-nickname">{Drupal.t('Screen name')}</label>
              <input type="text" id="nickname" value={nickname || ''} onChange={this.handleNicknameChange} name="nickname" />

              <label className="comment-form-email">{Drupal.t('Email Address')}</label>
              <input type="email" id="email" placeholder="Email" value={email || ''} onChange={this.handleEmailChange} name="email" />
              <div className="terms-conditions">
                <input type="checkbox" name="terms" id="terms" />
                {Drupal.t('I agree with terms and conditions.')}
              </div>
              <button onClick={() => this.setState({ showCommentForm: false })} type="button">{Drupal.t('CANCEL')}</button>
              <button type="submit" id="review-comments-submit">{Drupal.t('POST COMMENT')}</button>
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
        <div className="review-feedback-comment">
          <span className={`feedback-comment ${showCommentForm ? 'feedback-comment-disabled' : 'feedback-comment-active'}`}>
            <button onClick={() => this.setState({ showCommentForm: true })} type="button" disabled={showCommentForm}>{Drupal.t('comment')}</button>
            {showCommentForm ? this.showCommentForm() : null}
            {showCommentSubmission ? this.showCommentSubmission() : null}
          </span>
          <BazaarVoiceMessages />
        </div>
      );
    }
    return (null);
  }
}

export default ReviewCommentForm;
