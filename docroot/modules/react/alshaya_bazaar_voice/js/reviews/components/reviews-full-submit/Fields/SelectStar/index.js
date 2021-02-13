import React, { useState } from 'react';

const SelectStar = () => {
  const [rating, setRating] = useState(null);
  const [hover, setHover] = useState(null);

  return (
    <div className="select-star-rating">
      <div className="select-star__wrap">
        {[...Array(5)].map((star, i) => {
          const ratingValue = i + 1;
          return (
            <label key={ratingValue}>
              <input
                className="select-star-rating__input"
                type="radio"
                name="rating"
                value={ratingValue}
                onClick={() => setRating(ratingValue)}
              />
              <span
                className={ratingValue <= (hover || rating)
                  ? 'select-star-rating__ico ratingfull'
                  : 'select-star-rating__ico rating'}
                onMouseEnter={() => setHover(ratingValue)}
                onMouseLeave={() => setHover(null)}
              />
            </label>
          );
        })}
      </div>
      <input type="hidden" required="required" value={rating || ''} />
    </div>
  );
};

export default SelectStar;
