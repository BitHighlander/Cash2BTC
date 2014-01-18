NYC Hackathon
===============


Goals



Shared PubNub Keys for channel
-----------------------------

Subscribe Key	 sub-c-ecc13c7c-48d9-11e3-b088-02ee2ddab7fe
Publish Key	pub-c-9a952874-0857-4976-8fd6-be2c969dc5bc


or just goto


http://www.pubnub.com/console/?sub=sub-c-ecc13c7c-48d9-11e3-b088-02ee2ddab7fe&pub=pub-c-9a952874-0857-4976-8fd6-be2c969dc5bc&sec=sec-c-YjA0OGVmNmEtZWYyMC00MTE3LTg1YjQtZmFhYTFkMWQxMTRh



Channel: BETA00016


connect 

Overview
-----------


What is an ATM, 
Automatic teller machine
why would bitcoin need a teller?

Tendering currency is a job of a teller, autenicating bills and crediting/debiting accounts.

Although this is where the line is done.
Users of bitcoin dont trust a unit, unlike a bank teller

bla bla sean finish this
http://news.cnet.com/8301-17938_105-20005010-1.html


what we are really making is a vending machine
(clever name for bilater, cashing out machine*)








so what makes a Bitcoin Vending machine?


Authenicate cash
=================

- Bill Validator

- Hosted wallet

thats it

A hosted wallet that allows customers to upload a sending address, and after permission from the bill validator caluclate the amount of bitcoin to be send, and display proof its sent to the user.





So what all is in this github
=============================


Bill accetpor python script
--------------------------


python.py
https://github.com/BitHighlander/Cash2BTC/blob/master/pyramid-acceptors-Python2Pubnub

This intakes the bill acceptor data and publish's to pubnub




HOSTED BITCOIN VENDING WALLET
=========================

This wallet intakes data from pubnub

It is authenicated by pubnub

Dont reinvent the wheel






Scripts

Listen to pubnub

intake bill data

intake what type of currency they want. (doge bla bla)




















