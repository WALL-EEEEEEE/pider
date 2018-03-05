from scrapy import Spider;
from scrapy import Request;
from scrapy import Selector;
import json
import os

"""
@class tagspider

Spider that used to crawle tag infomation of tmall product.
"""

class tagspider(Spider):
   name = "tagspider"
   domains = "www.tmall.com" 
   start_urls = [
           "https://detail.m.tmall.com/item.htm?id=13245395432"
    ]
   def parse(self,response):
       pname = response.xpath('//div[@id="content"]//div[contains(@class,"module-title")]//div[contains(@class,"cell")]/text()').extract_first().strip();
       pattract = response.xpath('//h3[contains(@class,"newAttraction")]/text()').extract_first().strip();
       details_msg_json = Selector(response=response).re(r'.*var\s+_DATA_Mdskip\s*=\s*({.*})')[0];
       details_msg = json.loads(details_msg_json);
       pid = details_msg.get('item',{}).get('itemId','');
       pprice = details_msg['price']['price']['priceText']
       pptags  = details_msg.get('price',{}).get('priceTag',{})
       pcoupons = details_msg.get('resource',{}).get('coupon',{}).get('couponList',[])
       self.logger.info(pcoupons)
       tagscloud_api = 'https://rate.tmall.com/listTagClouds.htm?itemId='+str(pid)+'&callback=jsonp';
       self.logger.info(tagscloud_api)
       tagscloud = Request(url=tagscloud_api,callback=self.get_tagscloud)
       tagscloud.meta['info'] = {}
       tagscloud.meta['info']['price'] = pprice
       tagscloud.meta['info']['url'] = response.url
       tagscloud.meta['info']['id'] = pid
       tagscloud.meta['info']['coupons'] = pcoupons
       tagscloud.meta['info']['attract'] = pattract
       tagscloud.meta['info']['name'] = pname
       yield tagscloud;

   def get_tagscloud(self,response):
       comment_tags_json = Selector(response=response).re(r'jsonp\((.*)\)')[0]
       comment_tags = json.loads(comment_tags_json)
       tags = comment_tags.get('tags',{}).get('tagClouds',{})
       details_info = response.meta['info']
       details_info['tagscloud'] = tags
       self.logger.info(details_info)




