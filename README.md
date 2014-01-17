Planb Bitcoin ATM

Pyramid-Acceptors-Rpi-Python-Driver
===================================

This is for the Apex 7000 bill acceptor from http://pyramidacceptors.com/ Drivers for the Raspberry Pi

Created by 
Warren from Absorbing Technologies http://www.absorbingtechnologies.com/

continued development by BitHighlander



Setup
===================

clean Raspbian install

sudo apt-get install python-flask

sudo apt-get install python-gevent

sudo apt-get install python-pip

sudo pip install flask-sockets

sudo apt-get install gunicorn

sudo apt-get install python-mysqldb

sudo apt-get install midori



runtime
========


cd (to home folder)

sudo apt-get install git

git clone https://github.com/HighlanderEnterprises/Pyramid-Acceptors-Rpi-Python-Driver

cd Pyramid-Acceptors-Rpi-Python-Driver

mv * ..

sudo python boot.py



Kiosk setup
===============


sudo apt-get update

sudo apt-get upgrade

sudo apt-get install matchbox chromium x11-xserver-utils ttf-mscorefonts-installer xwit sqlite3 libnss3

sudo nano /boot/config.txt

cp from settings.txt

sudo nano /etc/rc.local

cp from settings.txt

sudo apt-get install libx11-dev libxext-dev libxi-dev x11proto-input-dev

wget http://github.com/downloads/tias/xinput_calibrator/xinput_calibrator-0.7.5.tar.gz

tar xvzf xinput_calibrator-0.7.5.tar.gz

cd xinput_calibrator-0.7.5/

./configure 

make 

sudo make install

sudo nano /etc/xdg/lxsession/LXDE/autostart 

copy content




Setup acceptor (not needed for testing)
===================

clean Raspbian install

sudo apt-get update

sudo apt-get install python-crypto

sudo apt-get install python-serial

sudo apt-get install zbar-tools
