import React from 'react';
import { getbazaarVoiceSettings, getLanguageCode } from '../../../utilities/api/request';
import { getTimeAgoDate } from '../../../../../../js/utilities/dateUtility';

const ReviewResponseDisplay = ({
  reviewResponses,
}) => {
  if (reviewResponses === null) {
    return null;
  }
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const countryCode = bazaarVoiceSettings.reviews.bazaar_voice.country_code;
  return (
    <div>
      {reviewResponses.map((responseObj) => (
        <div class="comment-submission-details">
          <div className="comment-submission-wrapper" key={responseObj.Date}>
            <div className="comment-submission-box">
              <div className="comment-user-details">
                <span className="comment-user-nickname admin">{responseObj.Name}</span>
                <span className="comment-user-date">{getTimeAgoDate(responseObj.Date, countryCode, getLanguageCode())}</span>
              </div>
              <div className="comment-description">
                <span className="comment-description-text">{responseObj.Response}</span>
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ReviewResponseDisplay;
