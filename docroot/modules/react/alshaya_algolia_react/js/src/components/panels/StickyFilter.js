import React, { useRef, useEffect } from 'react';
import { isMobile } from '../../utils';
import StickyFilterWrapper from '../base/StickyFilterWrapper';

/**
 * Sticky filters.
 */
const StickyFilter = ({ children }) => {
  const stickyFiltersRef = useRef();

  useEffect(() => {
    Drupal.algoliaReact.facetEffects();
    const stickyFilterWrapper = stickyFiltersRef.current.parentNode;
    if (!isMobile() && stickyFilterWrapper.querySelector('.site-brand-home') === null) {
      const siteBrand = document.querySelector('.site-brand-home').cloneNode(true);
      stickyFilterWrapper.insertBefore(siteBrand, stickyFilterWrapper.childNodes[0]);
    }
  }, [children]);

  return <StickyFilterWrapper callback={children} ref={stickyFiltersRef} />;
};

export default StickyFilter;
