import React from 'react';
import { getbazaarVoiceSettings, getLanguageCode } from '../../../utilities/api/request';
import { getTimeAgoDate } from '../../../../../../js/utilities/dateUtility';
import getStringMessage from '../../../../../../js/utilities/strings';

class ReviewCommentSubmission extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const countryCode = bazaarVoiceSettings.reviews.bazaar_voice.country_code;
    const { UserNickname, SubmissionTime, CommentText } = this.props;
    return (
      <div className="comment-submission-details">
        <div className="comment-submission-wrapper">
          <div className="comment-user-details">
            <span className="comment-user-nickname">{decodeURIComponent(UserNickname)}</span>
            <span className="comment-user-date">{getTimeAgoDate(SubmissionTime, countryCode, getLanguageCode())}</span>
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
