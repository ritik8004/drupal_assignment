# Alshaya New Brand Sub Theme Setup

## Changes needed after copying

* Rename the directory.
* Rename `.info` file and the name of theme and description in info file.
* Rename `.libraries.yml` file.
* Rename `.theme` file.
* Rename `.breakpoints.yml` file.
* Rename the `.settings.yml` file in `config/install`.

## After renaming

* change the `screenshot.png`, `logo.svg` and `favicon.ico` as per the
requirements.

## Colors & Theme inheritance

* We use color variables which can be overriden inside brand themes inside
`_colors.scss`
* This way without writing any CSS you can override colors for components coming
 from base theme.
* Maximum emphasis should be given to reuse from base theme and override what is
 necessary rather than duplicating.
