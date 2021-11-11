import React from 'react';

const DisplayStar = (props) => {
  const {
    starPercentage,
  } = props;

  return (
    <div className="star-ratings-wrapper">
      <div className="inline-star">
        <div
          className="star-ratings-front"
          style={{ width: `${((parseFloat(starPercentage).toFixed(1) * 100) / 5)}%` }}
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
    </div>
  );
};

export default DisplayStar;
