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
        <div className="response-submission-wrapper" key={responseObj.Date}>
          <div className="response-submission-box">
            <div className="response-user-details">
              <span className="response-user-name">{responseObj.Name}</span>
              <span className="response-submission-date">{getTimeAgoDate(responseObj.Date, countryCode, getLanguageCode())}</span>
            </div>
            <div className="response-description">
              <span className="response-description-text">{responseObj.Response}</span>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ReviewResponseDisplay;
