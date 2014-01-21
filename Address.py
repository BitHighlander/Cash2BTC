
#!/usr/bin/python
import MySQLdb
import sys
from Pubnub import Pubnub
import json


pubnub = Pubnub('pub-c-9a952874-0857-4976-8fd6-be2c969dc5bc', 'sub-c-ecc13c7c-48d9-11e3-b088-02ee2ddab7fe',None,True)


db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="billacceptor", # your username
                      passwd="", # your password
                      db="BETA00016") # name of the data bas



print "Server is LIVE"
def listen():
        global pubnub
        print pubnub.subscribe({'channel': 'BETA00016', 'callback': callbe })

def send(key, val):
        global pubnub
        info = pubnub.publish({'channel': 'BETA00016', "message": {key: val}})
        return info

def callbe(msg):
                global db
                call = msg
                keys = []
                for key, value in call.iteritems():
                                keys.append(key)
                if u'address' in keys:
                        value = msg['address']
                        cur = db.cursor()
                        cur.execute('''INSERT INTO address (`address`) VALUES (%s)''',(value))
                        db.commit()
                        send("debug",value)
                listen()


if __name__ == "__main__":
                listen()

