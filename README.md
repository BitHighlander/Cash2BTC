Planb Bitcoin ATM
=============

*note*
-----
This was cash2BTC's original project. This has been abandoned and was never finished. It is not complete. It’s powered by pubnub. It has a local python script powering the bill acceptor. It publishes bill acceptor data to pubnub via the local raspberry pi. As well as a python webcam script that scans for address’s 24/7.


A live web page is hosted remotely on a server and a set of scripts powers the bitcoin functionality. This live page is included and powered by javascript. When creating this I envisioned a chat room like feel of the page that people can interact with customers, post twitter feed’s for prices, and even publish public bitcoin addresses so people can tip/interact with first time bitcoin purchasers.


These legacy pages are still active and visible on cash2btc.com/atm/BETA00016.php
 Server.php is a script with the primary server functions and is set up on blockchain.info’s simple API
This php script is meant to run server side (OUTSIDE of apache.) For long term use I recommend mitigating it to python. I did some work on this but managed to lose the files. Running a php script continually on a loop like this was designed for was not very reliable. 
This is mainly a proof of concept and there are thousands of different ways to run and power a bitcoin dispenser. 


Pyramid-Acceptors-Rpi-Python-Driver
===================================

This is for the Apex 7000 bill acceptor from http://pyramidacceptors.com/ Drivers for the Raspberry Pi

Created by 
Warren from Absorbing Technologies http://www.absorbingtechnologies.com/

continued development by BitHighlander


Server Setup
===============

sudo apt-get install openssh-server

sudo apt-get install tasksel

sudo tasksel

install LAMP

sudo apt-get install phpmyadmin

run phpmyadmin

run SQLtable.sql

sudo apt-get install git

copy dir

start server
------------

python price.py

python address.py

python bills.py

php server.php



Local Rpi Setup
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
