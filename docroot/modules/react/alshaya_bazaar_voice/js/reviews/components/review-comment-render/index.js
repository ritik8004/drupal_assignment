import React from 'react';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';

class ReviewCommentRender extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const { UserNickname, SubmissionTime, CommentText } = this.props;
    TimeAgo.addLocale(en);
    const timeAgo = new TimeAgo('en-US');
    return (
      <div className="comment-submission">
        <div className="comment-user-details">
          <span className="comment-user-nickname">{UserNickname}</span>
          <span className="comment-user-date">{timeAgo.format(new Date(SubmissionTime))}</span>
        </div>
        <div className="comment-description">
          <span className="comment-description-text">{CommentText}</span>
        </div>
      </div>
    );
  }
}

export default ReviewCommentRender;
