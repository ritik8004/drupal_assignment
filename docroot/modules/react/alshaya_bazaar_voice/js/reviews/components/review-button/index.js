import React from 'react';

const ReviewButton = ({
  ButtonText,
}) => (
  <div className="button-wrapper">
    <button type="submit" className="write-review-button">{ButtonText}</button>
  </div>
);

export default ReviewButton;
