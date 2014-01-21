
#!/usr/bin/python
import MySQLdb
import sys
from Pubnub import Pubnub
import json

pubnub = Pubnub('pub-c-9a952874-0857-4976-8fd6-be2c969dc5bc', 'sub-c-ecc13c7c-48d9-11e3-b088-02ee2ddab7fe',None,True)

db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="BETA00011", # your username
                      passwd="", # your password
                      db="BETA00011") # name of the data base


print "Server is LIVE"
def listen():
        global pubnub
        pubnub.subscribe({'channel': 'BETA00011', 'callback': callbe })

def send(key, val):
        info = pubnub.publish({'channel': 'BETA00011', key: val})
        return info

def callbe(msg):
        global db
        call = msg
        keys = []
        for key, value in call.iteritems():
                keys.append(key)
        if u'tendered' in keys:
                #if given ANY tenderd, not just one
                if call[u'tendered']:
                        vaule = msg['tendered']
                        cur = db.cursor()
                        #deturming bill
                        if value == "100":
                                y= 'b100'
                        elif value == "50":
                                y= 'b50'
                        elif value == "20":
                                y= 'b20'
                        elif value == "10":
                                y= 'b10'
                        elif value == "5":
                                y= 'b5'
                        elif value == "1":
                                y= 'b1'
                        cur.execute("INSERT INTO bills("+ y +") VALUES('1')")
                        db.commit()
                        #db.close()
                        #test = "test"
                        send("message",{'pending':vaule})
                        print "sent"
                        listen()

