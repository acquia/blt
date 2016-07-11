#!/usr/bin/env bash

if [ $# -ne 1 ]; then
    echo $0: usage: install-node.sh 4.4.1
    exit 1
fi

if [ ! -d "$HOME/.nvm" ]; then
  echo "Downloading and installing nvm"
  curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.30.2/install.sh | bash
fi

NODE_VERSION=$1
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"  # This loads nvm
if [[ $(nvm ls $NODE_VERSION | grep "N/A") ]]; then
  echo "Downloading and installing node version $NODE_VERSION"
  nvm download $NODE_VERSION
  nvm install $NODE_VERSION
fi

# Sets version of node in .node-version so that
# it can be picked up by tools like avn.
echo $NODE_VERSION > .node-version

echo "Please run the following command":
echo "source ~/.bashrc && nvm use --delete-prefix $NODE_VERSION"
