from Pubnub import Pubnub
import requests, time, json

pubnub = Pubnub('pub-c-9a952874-0857-4976-8fd6-be2c969dc5bc', 'sub-c-ecc13c7c-48d9-11e3-b088-02ee2ddab7fe',None,True)


def getPrice():
        url = 'http://data.mtgox.com/api/2/BTCUSD/money/ticker'
        r = requests.get(url, headers={'Accept': 'application/json'})
        return (json.loads(r.text)['data']['avg']['display_short'][1:]).replace(",","")
        #return r.json['data']['avg']['display_short'][1:]

if __name__ == "__main__":
        while True:
                pubnub.publish({"channel":"BETA00016","message":{"btcPrice": getPrice()}})
                time.sleep(3)
