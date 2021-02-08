import React from 'react';

const WriteReviewLink = ({ writeReivewText, buttonRef, showModal }) => (
  <a
    className="btn btn-lg btn-danger center modal-button write-review"
    ref={buttonRef}
    onClick={showModal}
  >
    {writeReivewText}
  </a>
);

export default WriteReviewLink;
