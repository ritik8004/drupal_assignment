import React from 'react';

/**
 * Triggers AJAX call for Drupal Modal.
 *
 * @param e
 *   The event object.
 */
const openDrupalDialog = (e) => {
  e.preventDefault();

  const {
    url,
    dialogType,
    dialogClass,
    dialogDisplay,
  } = e.target.dataset;

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
}) => (
  <a
    className={linkClass}
    data-dialog-type={dialogType}
    type="button"
    href={url}
    data-url={url}
    data-dialog-class={dialogClass}
    data-dialog-display={dialogDisplay}
    onClick={(e) => openDrupalDialog(e)}
  >
    {linkText}
  </a>
);

export default DrupalDialog;
