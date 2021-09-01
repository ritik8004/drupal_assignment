import React from 'react';
import { connectHighlight } from 'react-instantsearch-dom';

const Highlight = ({
  highlight, attribute, hit, suffix,
}) => {
  const parsedHit = highlight({
    highlightProperty: '_highlightResult',
    attribute,
    hit,
  });

  return (
    <div className="aa-suggestion">
      <span className="suggested-text">
        {parsedHit.map(
          (part) => (part.isHighlighted
            ? (<span key={part.id} className="highlighted">{part.value}</span>)
            : (<span key={part.id} className="nonHighlighted">{part.value}</span>)),
        )}
      </span>
      {suffix}
    </div>
  );
};

export default connectHighlight(Highlight);
