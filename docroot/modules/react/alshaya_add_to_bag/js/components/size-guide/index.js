import React from 'react';
import parse from 'html-react-parser';
import DrupalDialog from '../../../../js/utilities/components/drupal-dialog';

const SizeGuide = ({ sizeGuideData }) => {
  const sizeGuideLink = parse(sizeGuideData.link);

  // Attach event handler for size guide modal open.
  Drupal.customGlobal.modalClasses('size-guide', 'sizeguide-modal-overlay');

  return (
    <DrupalDialog
      url={sizeGuideLink.props.href}
      linkText={sizeGuideLink.props.children}
      linkClass={sizeGuideLink.props.className}
      dialogDisplay="fullscreen"
      dialogType="dialog"
      isSizeGuideLink
    />
  );
};

export default SizeGuide;
