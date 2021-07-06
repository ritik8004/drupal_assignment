import React, { useRef, useEffect } from 'react';
import { isMobile } from '../../utils';
import StickyFilterWrapper from '../base/StickyFilterWrapper';

/**
 * Sticky filters.
 */
const PlpStickyFilter = ({ children, pageType = null }) => {
  const stickyFiltersRef = useRef();
  useEffect(() => {
    const stickyFilterWrapper = stickyFiltersRef.current.parentNode;
    if (!isMobile() && stickyFilterWrapper.querySelector('.site-brand-home') === null) {
      const siteBrand = document.querySelector('.site-brand-home').cloneNode(true);
      stickyFilterWrapper.insertBefore(siteBrand, stickyFilterWrapper.childNodes[0]);
    }
  }, [children]);

  return <StickyFilterWrapper callback={children} ref={stickyFiltersRef} pageType={pageType} />;
};

export default PlpStickyFilter;
