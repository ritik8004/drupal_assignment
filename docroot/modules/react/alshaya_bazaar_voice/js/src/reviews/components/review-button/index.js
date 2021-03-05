import React from 'react';

const ReviewButton = ({
  buttonText,
}) => (
  <div className="button-wrapper">
    <button type="submit" className="write-review-button">{buttonText}</button>
  </div>
);

export default ReviewButton;
