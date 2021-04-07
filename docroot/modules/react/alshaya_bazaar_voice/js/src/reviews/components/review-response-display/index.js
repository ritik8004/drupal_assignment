import React from 'react';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';

const ReviewResponseDisplay = ({
  reviewResponses,
}) => {
  if (reviewResponses === null) {
    return null;
  }
  TimeAgo.addLocale(en);
  const timeAgo = new TimeAgo('en-US');
  let keyIndex = 0;
  const reviewResponseDisplay = Object.values(reviewResponses).map((responseObj) => {
    if (responseObj.Response !== null) {
      return ([
        <div className="response-submission-wrapper" key={keyIndex}>
          <div className="response-submission-box">
            <div className="response-user-details">
              <span className="response-user-name">{responseObj.Name}</span>
              <span className="response-submission-date">{timeAgo.format(new Date(responseObj.Date))}</span>
            </div>
            <div className="response-description">
              <span className="response-description-text">{responseObj.Response}</span>
            </div>
          </div>
        </div>,
      ]);
    }
    keyIndex += 1;
    return '';
  });

  if (reviewResponseDisplay && reviewResponseDisplay.length > 0) {
    return reviewResponseDisplay;
  }
  return (null);
};

export default ReviewResponseDisplay;
