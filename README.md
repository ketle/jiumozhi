鸠摩智(多进程版)简介
-------------
前几天看到 http://doc.shenjianshou.cn/ 觉得不错,就省下几天守望开车时间照着文档用php实现了一遍,下面是对比shenjianshou的不足,改进和使用上的区别

> **不足:**

> - 没有js渲染
> - 没有验证码识别
> - 暂时没有自动换代理
> - 暂时没有图片本地化/托管云
> - 暂时没有模拟登录
> - 暂时没有录入数据库 (已加)


> **特点/改进:**

> - 多进程,现在只能在liunx下使用了,单进程很容易内存耗尽- -
> - 使用curl multi"多线程",可以自定义"线程"数,速度刷刷刷的
> - 支持css选择器,xpath,正则3种选择器

> **使用上的区别:**

> - 查看SiteConfig目录下 数字.php 具体配置,因为用php搞的,各种传递参数啥的都不一样,但看起来大体一样;
> - 用Xpath取回来的是innerHtml
> - jsonpath没怎么弄
> - contentUrlRegexes helperUrlRegexes 规则里没带域名

#### <i class="icon-hdd"></i> 如何安装
```
git clone https://github.com/ketle/jiumozhi.git
cd jiumozhi
composer install
```

#### <i class="icon-file"></i> 如何开始
```
配置config.php下db,redis设置
Usage: php index.php <1-n> <test|start|stop|restart>
比如: php index.php 1 test
手动停止请Ctrl+c
SiteConfig目录下自带了5个例子;
第一个例子入库2599条数据 时间从2016-08-22 22:15:13 - 2016-08-22 22:17:48 才用时2分半
```

#### <i class="icon-file"></i> 为啥叫"鸠摩智"
拍脑袋想出来的,哈哈哈