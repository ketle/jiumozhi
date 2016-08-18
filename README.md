鸠摩智简介
-------------
前几天看到 http://doc.shenjianshou.cn/ 觉得不错,就省下几天守望开车时间照着文档用php实现了一遍,下面是对比shenjianshou的不足,改进和使用上的区别

> **不足:**

> - 没有js渲染
> - 没有验证码识别
> - 没有自动换代理
> - 没有图片本地化/托管云
> - 没有模拟登录
> - 没有录入数据库


> **特点/改进:**

> - 使用curl multi"多线程",可以自定义"线程"数
> - 好像没有别的优点了, - -

> **使用上的区别:**

> - 查看SiteConfig目录下 数字.php 具体配置,因为用php搞的,各种传递参数啥的都不一样,但看起来大体一样;
> - jsonpath没怎么弄
> - contentUrlRegexes helperUrlRegexes 规则里没带域名

#### <i class="icon-hdd"></i> 如何安装
> git clone https://github.com/ketle/jiumozhi.git
> composer install

#### <i class="icon-hdd"></i> 如何安装
> git clone https://github.com/ketle/jiumozhi.git
> composer install

#### <i class="icon-file"></i> 如何开始
> 浏览器打开: http://ip/path/index.php
> 自带了5个例子,点"测试"就可以了,因为抓回来的都是html,可能看起来页面会乱,那就"查看源代码" 即可;

#### <i class="icon-file"></i> 为啥叫"鸠摩智"
拍脑袋想出来的,不敢称第一,鸠摩智水平吧,哈哈哈