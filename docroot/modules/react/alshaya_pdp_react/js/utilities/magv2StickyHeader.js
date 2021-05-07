const magv2StickyHeader = (buttonRef, header, content, isMobile) => {
  if ((buttonRef !== null) && (buttonRef !== undefined)) {
    const rect = buttonRef.current.getBoundingClientRect();

    if ((content !== null) && (content !== undefined)) {
      // Check addToBagContainer is not in viewport & 20 is the margin which we are excluding.
      if (rect.top <= 0) {
        header.current.classList.remove('magv2-pdp-non-sticky-header');
        header.current.classList.add('magv2-pdp-sticky-header');
        header.current.classList.add('fadeInVertical');
        if (!isMobile) {
          header.current.classList.remove('fadeOutVertical');
        }
      } else if (isMobile && window.pageYOffset <= header.current.offsetHeight) {
        header.current.classList.remove('magv2-pdp-non-sticky-header');
      } else {
        header.current.classList.remove('magv2-pdp-sticky-header');
        header.current.classList.add('magv2-pdp-non-sticky-header');
        header.current.classList.remove('fadeInVertical');
        if (!isMobile) {
          header.current.classList.add('fadeOutVertical');
        }
      }
    }
  }
};

export default magv2StickyHeader;
