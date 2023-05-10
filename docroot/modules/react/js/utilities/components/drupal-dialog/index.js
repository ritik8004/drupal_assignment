import React from 'react';

/**
 * Triggers AJAX call for Drupal Modal.
 *
 * @param e
 *   The event object.
 * @param {object} options
 *   Configurable options.
 */
const openDrupalDialog = (e, options) => {
  e.preventDefault();

  const {
    url,
    dialogType,
    dialogClass,
    dialogDisplay,
  } = e.target.dataset;

  if (options.isSizeGuideLink) {
    Drupal.alshayaSeoGtmPushSizeGuideEvents('open', options.context);
  }

  // Open Drupal modal.
  Drupal.ajax({
    url: Drupal.url(url.replace(`/${drupalSettings.path.pathPrefix}`, '')),
    progress: { type: dialogDisplay },
    dialogType,
    dialog: { dialogClass },
  })
    .execute();
};

/**
 * Used for displaying Drupal dialog.
 */
const DrupalDialog = ({
  url,
  linkClass,
  linkText,
  dialogClass,
  dialogDisplay,
  dialogType,
  isSizeGuideLink,
  context,
}) => (
  <a
    className={linkClass}
    data-dialog-type={dialogType}
    type="button"
    href={url}
    data-url={url}
    data-dialog-class={dialogClass}
    data-dialog-display={dialogDisplay}
    onClick={(e) => openDrupalDialog(e, { isSizeGuideLink, context })}
  >
    {linkText}
  </a>
);

export default DrupalDialog;
