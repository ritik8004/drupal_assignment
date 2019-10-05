import React from 'react';
import { connectHighlight } from 'react-instantsearch-dom';

const Highlight = ({ highlight, attribute, hit }) => {
  const parsedHit = highlight({
    highlightProperty: '_highlightResult',
    attribute,
    hit,
  });

  return (
    <div className="aa-suggestion" role="option">
      <span className="suggested-text">
        {parsedHit.map(
          (part, index) =>
            part.isHighlighted
              ? (<span key={index} className="highlighted">{part.value}</span>)
              : (<span key={index} className="nonHighlighted">{part.value}</span>)
        )}
      </span>
      <span className="populate-input">&#8598;</span>
    </div>
  );
};

export default connectHighlight(Highlight);
