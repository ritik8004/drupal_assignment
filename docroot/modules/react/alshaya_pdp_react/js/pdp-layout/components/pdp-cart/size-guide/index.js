import React, { useEffect, useRef } from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const SizeGuide = ({ attrId, context }) => {
  const sizeGuideRef = useRef(null);

  // Get size guide as per v3 architecture.
  const sizeGuide = window.commerceBackend.getSizeGuideSettings();

  useEffect(() => {
    const sizeGuideLink = sizeGuideRef.current.querySelector('.size-guide-link');
    if (sizeGuideLink && context) {
      sizeGuideLink.addEventListener('click', () => {
        Drupal.alshayaSeoGtmPushSizeGuideEvents('open', context);
      });
    }
  }, []);

  if (hasValue(sizeGuide)) {
    // If the current attr matches the size attribute.
    if (sizeGuide.attributes.indexOf(attrId) !== -1) {
      return (
        <div className="size-guide" ref={sizeGuideRef}>
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
