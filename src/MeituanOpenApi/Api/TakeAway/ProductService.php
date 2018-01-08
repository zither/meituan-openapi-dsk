<?php

namespace MeituanOpenApi\Api\TakeAway;

use MeituanOpenApi\Api\RpcService;

/**
 * 菜品服务
 */
class ProductService extends RpcService
{
    const DISH_MAP_API = 'https://open-erp.meituan.com/waimai-dish-mapping';

    /**
     * 查询菜品分类
     * @return mixed
     */
    public function queryCateList()
    {
        return $this->client->call('get', 'waimai/dish/queryCatList');
    }


    /**
     * 新增/更新菜品分类
     * @param $oldCatName
     * @param $catName
     * @param $sequence
     * @return mixed
     */
    public function addOrUpdCate($oldCatName, $catName, $sequence)
    {
        return $this->client->call('post', 'waimai/dish/updateCat', ['oldCatName' => $oldCatName, 'catName' => $catName, 'sequence' => $sequence]);
    }


    /**
     * 删除菜品分类
     * @param $catName
     * @return mixed
     */
    public function delCate($catName)
    {
        return $this->client->call('post', 'waimai/dish/deleteCat', ['catName' => $catName]);
    }


    /**
     * 根据ERP的门店id查询门店下的菜品基础信息【包含美团的菜品Id】(7.2.2)
     * @param $ePoiId
     * @return mixed
     */
    public function queryBaseListByEPoiId($ePoiId)
    {
        return $this->client->call('get', 'waimai/dish/queryBaseListByEPoiId', ['ePoiId' => $ePoiId]);
    }


    /**
     * 根据ERP的门店id查询门店下的菜品【不包含美团的菜品Id】(7.2.4)
     * @param $ePoiId
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function queryListByEPoiId($ePoiId, $offset, $limit)
    {
        return $this->client->call('get', 'waimai/dish/queryListByEPoiId', ['ePoiId' => $ePoiId, 'offset' => $offset, 'limit' => $limit]);
    }


    /**
     * 菜品映射链接
     * @param $ePoiId
     * @return string
     */
    public function getDishMapUrl($ePoiId)
    {
        return self::DISH_MAP_API . '?' . http_build_query([
                'signKey'      => $this->client->getSignKey(),
                'appAuthToken' => $this->client->getToken(),
                'ePoiId'       => $ePoiId,
            ]);
    }


    /**
     * 重定向至菜品映射链接 (7.2.3 重定向跳转)
     * @param $ePoiId
     */
    public function redirectDishMap($ePoiId)
    {
        header('Location:' . $this->getDishMapUrl($ePoiId));
    }


    /**
     * 建立菜品映射(美团商品与本地erp商品映射关系) （7.2.3 openapi接入）
     * @param $ePoiId
     * @param $dishMappings
     * @return mixed
     */
    public function mapping($ePoiId, $dishMappings)
    {
        return $this->client->call('post', 'waimai/dish/mapping', ['ePoiId' => $ePoiId, 'dishMappings' => $dishMappings]);
    }


    /**
     * 批量上传／更新菜品
     * @param $ePoiId
     * @param $dishes
     * @return mixed
     */
    public function batchUpload($ePoiId, $dishes)
    {
        return $this->client->call('post', 'waimai/dish/queryListByEPoiId', ['ePoiId' => $ePoiId, 'dishes' => $dishes]);
    }


    /**
     * 更新菜品价格【sku的价格】
     * @param $ePoiId
     * @param $dishSkuPrices
     * @return mixed
     */
    public function updatePrice($ePoiId, $dishSkuPrices)
    {
        return $this->client->call('post', 'waimai/dish/updatePrice', ['ePoiId' => $ePoiId, 'dishSkuPrices' => $dishSkuPrices]);
    }


    /**
     * 更新菜品库存【sku的库存】
     * @param $ePoiId
     * @param $dishSkuStocks
     * @return mixed
     */
    public function updateStock($ePoiId, $dishSkuStocks)
    {
        return $this->client->call('post', 'waimai/dish/updateStock', ['ePoiId' => $ePoiId, 'dishSkuStocks' => $dishSkuStocks]);
    }


    /**
     * 删除菜品
     * @param $ePoiId
     * @param $eDishCode
     * @return mixed
     */
    public function delete($ePoiId, $eDishCode)
    {
        return $this->client->call('post', 'waimai/dish/delete', ['ePoiId' => $ePoiId, 'eDishCode' => $eDishCode]);
    }


    /**
     * 删除菜品sku
     * @param $eDishCode
     * @param $eDishSkuCode
     * @return mixed
     */
    public function deleteSku($eDishCode, $eDishSkuCode)
    {
        return $this->client->call('post', 'waimai/dish/deleteSku', ['eDishCode' => $eDishCode, 'eDishSkuCode' => $eDishSkuCode]);
    }


    /**
     * 上传菜品图片，返回图片id
     * @param $ePoiId erp商家id
     * @param $imageName 文件名
     * @param $file 文件base64字节流
     * @return mixed
     */
    public function uploadImage($ePoiId, $imageName, $file)
    {
        return $this->client->call('post', 'waimai/image/upload', 
            [
                'ePoiId' => $ePoiId, 
                'imageName' => $imageName, 
                'file' => $file
            ], 
            ['contentType：multipart/form-data'], false
        );
    }


    /**
     * 批量查询菜品信息
     * 根据eDishCode批量查询外卖菜品信息
     * @param $ePoiId 
     * @param $eDishCodes 
     * @return mixed
     */
    public function batchProducts($ePoiId, $eDishCodes)
    {
        return $this->client->call('post', 'waimai/dish/queryListByEdishCodes', ['ePoiId' => $ePoiId, 'eDishCodes' => $eDishCodes]);
    }

}