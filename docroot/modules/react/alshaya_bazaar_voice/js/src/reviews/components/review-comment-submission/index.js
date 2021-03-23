import React from 'react';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';
import getStringMessage from '../../../../../../js/utilities/strings';

class ReviewCommentSubmission extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    TimeAgo.addLocale(en);
    const timeAgo = new TimeAgo('en-US');
    const { UserNickname, SubmissionTime, CommentText } = this.props;
    return (
      <div className="comment-submission-details">
        <div className="comment-submission-wrapper">
          <div className="comment-user-details">
            <span className="comment-user-nickname">{UserNickname}</span>
            <span className="comment-user-date">{timeAgo.format(new Date(SubmissionTime))}</span>
          </div>
          <div className="comment-description">
            <span className="comment-description-text">{CommentText}</span>
          </div>
          <div className="comment-moderation-block">
            <span className="comment-moderation-text">{getStringMessage('review_comment_submission')}</span>
          </div>
        </div>
      </div>
    );
  }
}

export default ReviewCommentSubmission;
