require.config({
    paths: {
        jquery: '../bower_components/jquery/jquery',
        pubnub: '../bower_components/pubnub/web/pubnub.min',
        pubnubCrypto: '../bower_components/pubnub/web/pubnub-crypto.min'
    },
    shim: {
        pubnub: {
            deps: ['jquery']
        },
        pubnubCrypto: {
            deps: ['pubnub']
        }
    }
});

require(['jquery', 'pubnub', 'pubnubCrypto'], function ($) {
    'use strict';
    // use app here

    var bitQuotes = [],
        initialize,
        updateQuotes;

    initialize = function (options) {
        var options = $.extend({
            "fiat": "USD",
            "fiatSymbol": "$",
            "container": "#values",
            "href": "https://bitcoinaverage.com/",
            "autoUpdate": true,
            "updateInterval": 6000,
            "autoResize": true
            }, options);

        bitQuotes.push(options);

        var container = options.container;

        $.get("https://api.bitcoinaverage.com/ticker/" + options.fiat, function (data) {
            $(container + " .values__price").html(options.fiatSymbol + data.last);
            $(container + " .values__bid").html("Bid: " + options.fiatSymbol + data.bid);
            $(container + " .values__ask").html("Ask: " + options.fiatSymbol + data.ask);

            if (options.autoResize) {
                //adjustWidth(container, options.fiatSymbol.length);
            }
        });

        if (options.href) {
            $(container).on('click', function (e) {
                window.location = options.href;
            });
        }

        setInterval(function () {
            updateQuotes(bitQuotes);
        }, options.updateInterval);
    }

    updateQuotes = function (bitOptions) {
        $.each(bitOptions, function (i, options) {
            var container = options.container;

            if (options.autoUpdate) {
                $.get("https://api.bitcoinaverage.com/ticker/" + options.fiat, function (data) {
                    if ($(container + " .values__price").text() != options.fiatSymbol + data.last) {
                        $(container + " .values__price").fadeOut(600, function () {
                            $(this).text(options.fiatSymbol + data.last).fadeIn(600);
                        });
                    }

                    if ($(container + " .values__bid").text() != "Bid: " + options.fiatSymbol + data.bid) {
                        $(container + " .values__bid").fadeOut(600, function () {
                            $(this).text("Bid: " + options.fiatSymbol + data.bid).fadeIn(600);
                        });
                    }

                    if ($(container + " .values__ask").text() != "Ask: " + options.fiatSymbol + data.ask) {
                        $(container + " .values__ask").fadeOut(600, function () {
                            $(this).text("Ask: " + options.fiatSymbol + data.ask).fadeIn(600);
                        });
                    }
                });
            }
        });
    }

    // init app
    initialize();

    var inSession = false,
        usdInMachine = 0,
        btcPrice = 0,
        recvAddress = "NA",
        satoshi = 0,
        paidOut = 0,
        sessionTotal = 0,
        txout = "",
        timerNum;

    var pubnub = PUBNUB.init({
        publish_key: 'pub-c-45e0c943-0d95-4adc-b7f2-55c8edfd220e',
        subscribe_key: 'sub-c-fe0f3b9a-4e7f-11e3-98c1-02ee2ddab7fe'
    });

    pubnub.subscribe({
        channel : "atm",
        callback : function(m){inputMessage(JSON.stringify(m));}
    });

    pubnub.subscribe({
        channel : "price",
        callback : function(m){inputMessage(JSON.stringify(m)); }
    });

    var inputMessage = function (msg) {
        msg = JSON.parse(msg);

        console.log(msg);

        if(msg["tendered"] != undefined) {
            usdInMachine += parseInt(msg["tendered"]);
        }

        if(msg["address"] != undefined) {
            alert("address");
            recvAddress = msg["address"];
        }

        if(msg["paid"] != undefined) {
            paidOut += parseInt(msg["paid"]);
            sessionTotal = usdInMachine - paidOut;
            inSession = false;
        }

        if(msg["recipt"] != undefined) {
            txout = msg["txout"];
            window.setTimeout(function() { txout=""; jsToHTML(); }, 5000);
        }

        if(msg["livemsg"] != undefined) {
            window.alert(msg["livemsg"]);
        }

        if(msg["btcPrice"] != undefined) {
            btcPrice = parseInt(msg["btcPrice"]);
        }

        if(usdInMachine > paidOut) {
            inSession = true;
            sessionTotal = usdInMachine - paidOut;
            //sessiontotal = parseInt(msg["sessiontotal"]);
            alert(sessiontotal);
        }

        jsToHTML();
    }

    function jsToHTML() {
        var div_indev = $("#indevice"),
            div_paidout = $("#paidout"),
            div_satoshi = $("#satoshi"),
            div_txout = $("#txout"),
            div_sessiontotal = $("#sessiontotal"),
            div_recvAddress = $("#recvaddress");

        div_indev.html(usdInMachine.toString());
        div_paidout.html(paidOut.toString());
        div_satoshi.html(satoshi.toString());
        div_txout.html(txout);
        div_recvAddress.html(recvAddress);
    }
});
