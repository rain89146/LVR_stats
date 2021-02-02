import DBConn from '../DBconn.js';
import Config from '../Include/Config.include.js';
import Tools from '../Include/Tools.include.js';
import Component from '../Include/Component.include.js';

//  Connection
let template = new Component();
let dbconn = new DBConn();
let tool = new Tools();


//  Current date
// const TODAY = tool.TODAY();
const TODAY = '2020-12-01';
const month_range = tool.generate_month_range(TODAY, 2);

let sold_listing_city_obj = {
    'cmd': Config.GET_SOLD_LISTING,
    'param': {
        'purpose': 'sold_listing_city',
        'date_range': month_range,
        'city': CITY,
        'query_date': '2020-12-29'
    }
}

let days_on_market_city_obj = {
    'cmd': Config.GET_DAY_ON_MARKET,
    'param': {
        'purpose': 'days_on_market_city',
        'date_range': month_range,
        'city': CITY,
        'query_date': '2020-12-29'
    }
}

let active_listing_city_obj = {
    'cmd': Config.GET_ACTIVE_LISTING,
    'param': {
        'purpose': 'active_availability_city',
        'query_date': '2020-12-29',
        'city': CITY
    }
}

let no_offer_active_listing_city_obj = {
    'cmd': Config.GET_NO_OFFER_ACTIVE_LISTING,
    'param': {
        'purpose': 'no_offer_active_availability_city',
        'query_date': '2020-12-29',
        'city': CITY
    }
}

dbconn.connect(
    Config.APIURL,
    days_on_market_city_obj,
    (status, result) => days_on_market_callback(status, result)
);

dbconn.connect(
    Config.APIURL,
    sold_listing_city_obj,
    (status, result) => sold_listing_callback(status, result)
);

dbconn.connect(
    Config.APIURL,
    active_listing_city_obj,
    (status, result) => active_listing_callback(status, result)
);

dbconn.connect(
    Config.APIURL,
    no_offer_active_listing_city_obj,
    (status, result) => no_offer_active_listing_callback(status, result)
);



function sold_listing_callback(stats, res) {
    console.log(res);
}

function days_on_market_callback(stats, res) {
    console.log(res);
}

function active_listing_callback(stats, res) {
    console.log(res);
}

function no_offer_active_listing_callback(stats, res) {
    console.log(res);
}

console.log(CITY);