# PHP SDK 接入指南 & CHANGELOG

## 仿照饿了么openApi编写

## 接入指南

  1. PHP version >= 5.4 & curl extension support
  2. 通过composer安装SDK
  3. 创建Config配置类，填入key，secret和sandbox参数
  4. 使用sdk提供的接口进行开发调试
  5. 上线前将Config中$sandbox值设为false以及填入正式环境的key和secret
 
### 基本用法

```php
    use MeituanOpenApi\Config\Config;
    use MeituanOpenApi\Api\ProductService;
    
    //实例化一个配置类
    $config = new Config($app_key, $app_secret, false);
    
    //使用config和token对象，实例化一个服务对象
    $productService = new ProductService($token, $config);
    
    //调用服务方法，获取资源
    $shop = $productService->queryCateList(12345);

```



