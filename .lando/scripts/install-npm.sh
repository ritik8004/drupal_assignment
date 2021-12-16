#!/usr/bin/env bash

# Solution taken from https://github.com/nodesource/distributions/issues/866

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"  # This loads nvm
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"  # This loads nvm bash_completion

nvm install 8.17.0
nvm use 8.17.0

# .bashrc or .bash_profile don't work so we link them into /usr/local/bin.
ln -s /var/www/.nvm/versions/node/v8.17.0/bin/npm /usr/local/bin/npm
ln -s /var/www/.nvm/versions/node/v8.17.0/bin/node /usr/local/bin/node
