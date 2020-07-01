import React from 'react';
import parse from 'html-react-parser';

const SizeGuide = ({ attrId }) => {
  const { isSizeGuideEnabled } = drupalSettings;

  // If size guide is enabled.
  if (isSizeGuideEnabled) {
    const { sizeGuide } = drupalSettings;
    // If the current attr matches the size attribute.
    if (sizeGuide.attributes.indexOf(attrId) !== -1) {
      return (
        <div className="size-guide">
          {parse(sizeGuide.link)}
        </div>
      );
    }
  }
  return (
    <></>
  );
};

export default SizeGuide;
