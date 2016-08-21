<?php
return [
			'insertType' => 1,//1:点击'开始'后 判断数据库是否有该条数据,新增才插入; 2:先删除该site所有数据,全新插入;
			'db' => [
						'database_type' => 'mysql',
						'database_name' => 'jiumozhi',
						'server' => '127.0.0.1',
						'port' => '3306',
						'username' => 'root',
						'password' => 'root',
						'charset' => 'utf8'
						]
		];