# Webalizer-Parser
Webalizer Parser class, parse html webalizer files and return array with data. You just have to set the Webalizer HTML filepath in the constructor, and the class will return the data as an array.

Private Functions:
- extract_tables
- extract_days
- extract_rows
- extract_hits
- extract_single_hits
- array_search_partial


Public Functions:
- monthly
- daily
- top_10
- top_30
- top_entry
- top_exit
- referer
- useragents


This class allows you to convert the Webalizer html files to array, for easy viewing in your own website/panel. See images - preview of my own dashboard, where i use this class to show the webalizer statistics in my own gauges, and tables - looks alot better than the webalizer files.

If you use it in your website, please just like our facebook page and credits would be nice :) Please visit our website: https://erdesigns.eu and facebook page: https://fb.me/erdesigns.eu
