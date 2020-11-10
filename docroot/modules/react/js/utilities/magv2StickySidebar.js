const magv2Sticky = (sidebar, gallery, crossell, main) => {
  const siderbarwrapper = sidebar;
  const gallerycontainer = gallery;
  const crosssellcontainer = crossell;
  const maincontainer = main;

  let pageScrollDirection;
  let lastScrollTop = 0;

  const scrollingDirection = () => {
    // Figure out scroll direction.
    const currentScrollTop = window.pageYOffset;
    if (currentScrollTop < lastScrollTop) {
      pageScrollDirection = 'up';
    } else {
      pageScrollDirection = 'down';
    }
    lastScrollTop = currentScrollTop;

    return pageScrollDirection;
  };

  window.addEventListener('scroll', () => {
    // Figure out scroll current scroll position.
    const currentScrollTop = window.pageYOffset;
    const gallerywrapper = gallerycontainer;
    const crosssellwrapper = crosssellcontainer;
    const maincontainerwrapper = maincontainer;
    const scrollDirection = scrollingDirection();

    // Gallery top.
    const topPosition = gallerywrapper.offsetTop + 30;

    if (currentScrollTop + siderbarwrapper.offsetHeight > crosssellwrapper.offsetTop) {
      if (siderbarwrapper.classList.contains('sidebar-sticky')) {
        siderbarwrapper.classList.add('contain');
        maincontainerwrapper.classList.add('magv2-main-contain');
      }
    } else if (currentScrollTop > topPosition) {
      if (!siderbarwrapper.classList.contains('sidebar-sticky')) {
        siderbarwrapper.classList.add('sidebar-sticky');
      }
    } else {
      siderbarwrapper.classList.remove('sidebar-sticky');
      maincontainerwrapper.classList.remove('magv2-main-contain');
    }

    if ((currentScrollTop + siderbarwrapper.offsetHeight < crosssellwrapper.offsetTop) && (scrollDirection === 'up')) {
      if (siderbarwrapper.classList.contains('contain')) {
        siderbarwrapper.classList.remove('contain');
        maincontainerwrapper.classList.remove('magv2-main-contain');
        // Remove top and let fixed work as defined for sticky.
        siderbarwrapper.style.top = '';
      }
    }
  });
};

export default magv2Sticky;
