<?php
header("Cache-Control: max-age:0");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Location:mitch://navigate?to=https://google.com.tw");
