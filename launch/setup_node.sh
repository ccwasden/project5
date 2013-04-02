#!/bin/bash
sudo yum -y install gcc-c++ make
sudo yum -y install openssl-devel
cd ~
mkdir lib
cd lib
git clone git://github.com/joyent/node.git
cd node
#git checkout v0.9.8-release
./configure
make
sudo make install
sudo env PATH=$HOME/local/node/bin:$PATH
cd ../
git clone https://github.com/isaacs/npm.git
cd npm
sudo make install
cd ~/repos/aws-source/server
sudo npm install express -g
node server.js
