import React from 'react';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';
import { postAPIData } from '../../../utilities/api/apiData';

class ReviewCommentForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showCommentForm: false,
      showCommentSubmission: false,
      email: drupalSettings.user.user_email,
      commentbox: '',
      nickname: '',
      submissionTime: '',
    };
  }

  showCommentForm = () => {
    const { commentbox } = this.state;
    const { nickname } = this.state;
    const { email } = this.state;
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

              <input type="hidden" name="blackBox" id="ioBlackBox" />
              <div className="customtext">
                You may receive emails regarding this submission. Any emails will include the ability to opt-out of future communications. Boots UK Limited (“Boots”) will collect and use your personal data that you share with us, such as your name and contact details, for the purposes of processing your product review. Boots may share your information across all Boots services such as Boots Opticians and Boots Pharmacy. We may also use your data to improve our services. These are in our interests but we believe they benefit our customers this includes things like creating customer profiles to tell you about certain products and offers which we think will interest you, based on how you use Boots services. We will store your personal data for as long as necessary, unless a longer retention period is needed or allowed by law. We may also share your data with companies who provide services on our behalf such as Bazaarvoice (our reviews platform), but Boots will never sell your personal data, and keeping it safe is our top priority. For more information about who we may share your data with, how Boots process your data and how to amend or remove your data please see our privacy policy (http://www.boots.com/privacypolicy) or contact Boots.CustomerCare_Team@boots.co.uk.
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
    TimeAgo.addLocale(en);
    const timeAgo = new TimeAgo('en-US');
    const { submissionTime } = this.state;
    const { nickname } = this.state;
    const { commentbox } = this.state;
    return (
      <div className="comment-submission-details">
        <div className="comment-user-details">
          <span className="comment-user-nickname">{nickname}</span>
          <span className="comment-user-date">{timeAgo.format(new Date(submissionTime))}</span>
        </div>
        <div className="comment-description">
          <span className="comment-description-text">{commentbox}</span>
        </div>
        <div className="comment-moderation-block">
          <span className="comment-moderation-text">{Drupal.t('Thank you for submitting a comment! Your comment is being moderated and may take up to a few days to appear.')}</span>
        </div>
      </div>
    );
  }

  handleSubmit = (e) => {
    const { ReviewId: reviewId } = this.props;
    const { commentbox } = this.state;
    const { email } = this.state;
    const { nickname } = this.state;
    e.preventDefault();
    const apiUri = '/data/submitreviewcomment.json';
    const params = `&Action=submit&CommentText=${commentbox}&UserEmail=${email}&UserNickname=${nickname}&ReviewId=${reviewId}`;
    const apiData = postAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
      && result.data !== undefined
      && result.data.error === undefined) {
          const response = result.data;
          if (response.SubmissionId !== null) {
            this.setState({ submissionTime: response.Comment.SubmissionTime });
            this.setState({ showCommentSubmission: true });
            this.setState({ showCommentForm: false });
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
    const { ReviewId: reviewId } = this.props;
    const { showCommentForm } = this.state;
    const { showCommentSubmission } = this.state;
    if (reviewId !== undefined) {
      return (
        <div className="review-feedback-comment">
          <span className={`feedback-comment ${showCommentForm ? 'feedback-comment-disabled' : 'feedback-comment-active'}`}>
            <button onClick={() => this.setState({ showCommentForm: true })} type="button" disabled={showCommentForm}>{Drupal.t('comment')}</button>
            {showCommentForm ? this.showCommentForm() : null}
            {showCommentSubmission ? this.showCommentSubmission() : null}
          </span>
        </div>
      );
    }
    return (null);
  }
}

export default ReviewCommentForm;
