import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const SizeGuide = ({ attrId }) => {
  // Get size guide as per v3 architecture.
  const sizeGuide = window.commerceBackend.getSizeGuideSettings();

  if (hasValue(sizeGuide)) {
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
