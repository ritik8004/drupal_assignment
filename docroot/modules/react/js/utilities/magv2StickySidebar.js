const magv2Sticky = (sidebar, gallery, crossell, main) => {
  const siderbarwrapper = sidebar;
  const gallerycontainer = gallery;
  const crosssellcontainer = crossell;
  const maincontainer = main;

  let pageScrollDirection;
  let lastScrollTop = 0;

  // Helper function for checking scrolling direction.
  const scrollingDirection = () => {
    const currentScrollTop = window.pageYOffset;
    if (currentScrollTop < lastScrollTop) {
      pageScrollDirection = 'up';
    } else {
      pageScrollDirection = 'down';
    }
    lastScrollTop = currentScrollTop;

    return pageScrollDirection;
  };

  // Helper function for making the element sticky.
  const stickyElement = (area) => {
    let element = '';
    const removeClasses = ['sticky-element', 'contain'];
    if (area === 'sidebar') {
      element = siderbarwrapper;
      // Remove classes from gallery.
      gallerycontainer.classList.remove(...removeClasses);
    } else {
      element = gallerycontainer;
      // Remove classes from gallery.
      siderbarwrapper.classList.remove(...removeClasses);
    }
    const gallerywrapper = gallerycontainer;
    const crosssellwrapper = crosssellcontainer;
    const maincontainerwrapper = maincontainer;
    let topPosition;

    // Figure out scroll current scroll position.
    const currentScrollTop = window.pageYOffset;
    const scrollDirection = scrollingDirection();

    // Gallery & Siderbar top.
    if (gallerywrapper.offsetHeight > siderbarwrapper.offsetHeight) {
      topPosition = gallerywrapper.offsetTop;
    } else {
      topPosition = siderbarwrapper.offsetTop;
    }

    if (currentScrollTop + element.offsetHeight > (crosssellwrapper.offsetTop)) {
      if (element.classList.contains('sticky-element')) {
        element.classList.add('contain');
        maincontainerwrapper.classList.add('magv2-main-contain');
      }
    } else if (currentScrollTop > topPosition) {
      if (!element.classList.contains('sticky-element')) {
        element.classList.add('sticky-element');
      }
    } else {
      element.classList.remove('sticky-element');
      element.classList.remove('contain');
      element.classList.remove('magv2-main-contain');
    }

    if ((currentScrollTop + element.offsetHeight < (crosssellwrapper.offsetTop)) && (scrollDirection === 'up')) {
      if (element.classList.contains('contain')) {
        element.classList.remove('contain');
        maincontainerwrapper.classList.remove('magv2-main-contain');
        // Remove top and let fixed work as defined for sticky.
        element.style.top = '';
      }
    }
  };

  window.addEventListener('load', () => {
    const galleryWrapper = gallerycontainer;

    if (galleryWrapper.offsetHeight > siderbarwrapper.offsetHeight) {
      stickyElement(siderbarwrapper);
    } else {
      stickyElement(galleryWrapper);
    }
  });

  window.addEventListener('scroll', () => {
    const galleryWrapper = gallerycontainer;

    if (galleryWrapper.offsetHeight > siderbarwrapper.offsetHeight) {
      stickyElement('sidebar');
    } else {
      stickyElement('gallery');
    }
  });
};

export default magv2Sticky;
