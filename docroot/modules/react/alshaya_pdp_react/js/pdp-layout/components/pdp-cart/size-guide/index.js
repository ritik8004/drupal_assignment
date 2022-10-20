import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const getRcsSizeGuideSettings = () => {
  if (hasValue(drupalSettings.alshayaRcs) && hasValue(drupalSettings.alshayaRcs.sizeGuide)) {
    return drupalSettings.alshayaRcs.sizeGuide;
  }
  return null;
};

const SizeGuide = ({ attrId }) => {
  // Get size guide as per v3 architecture.
  let sizeGuide = getRcsSizeGuideSettings();

  // Get size guide from drupal settings for v2 architecture.
  if (!hasValue(sizeGuide)) {
    const { isSizeGuideEnabled } = drupalSettings;
    if (isSizeGuideEnabled) {
      sizeGuide = { drupalSettings };
    }
  }
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
