import React from 'react';
import { getbazaarVoiceSettings, getLanguageCode } from '../../../utilities/api/request';
import { getTimeAgoDate } from '../../../../../../js/utilities/dateUtility';

class ReviewCommentRender extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const { UserNickname, SubmissionTime, CommentText } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const countryCode = bazaarVoiceSettings.reviews.bazaar_voice.country_code;
    return (
      <div className="comment-submission-box">
        <div className="comment-user-details">
          <span className="comment-user-nickname">{UserNickname}</span>
          <span className="comment-user-date">{getTimeAgoDate(SubmissionTime, countryCode, getLanguageCode())}</span>
        </div>
        <div className="comment-description">
          <span className="comment-description-text">{CommentText}</span>
        </div>
      </div>
    );
  }
}

export default ReviewCommentRender;
