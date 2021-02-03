import React from 'react';

const DisplayStar = ({
  StarPercentage,
}) => (
  <div className="star-ratings">
    <div
      className="star-ratings-front"
      style={{ width: `${((parseFloat(StarPercentage).toFixed(1) * 100) / 5)}%` }}
    >
      <span className="icon-ic_ratingfull" />
      <span className="icon-ic_ratingfull" />
      <span className="icon-ic_ratingfull" />
      <span className="icon-ic_ratingfull" />
      <span className="icon-ic_ratingfull" />
    </div>
    <div className="star-ratings-back">
      <span className="icon-ic_rating" />
      <span className="icon-ic_rating" />
      <span className="icon-ic_rating" />
      <span className="icon-ic_rating" />
      <span className="icon-ic_rating" />
    </div>
  </div>
);

export default DisplayStar;
