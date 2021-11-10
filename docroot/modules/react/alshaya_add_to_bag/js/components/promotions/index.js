import React from 'react';

/**
 * Display the promotion labels as links.
 */
const Promotions = ({ promotions }) => {
  if (promotions.length === 0) {
    return (null);
  }

  const promotionsData = promotions
    .map((promotion) => (
      <a href={promotion.url} key={promotion.url}>
        {promotion.label}
      </a>
    ))
    .reduce((prev, current) => [prev, current]);

  return (
    <div className="promotions">
      {promotionsData}
    </div>
  );
};

export default Promotions;
