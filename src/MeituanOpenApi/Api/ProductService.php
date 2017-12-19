<?php

namespace MeituanOpenApi\Api;

/**
 * 商品服务
 */
class ProductService extends RpcService
{

    /**
     * 查询菜品分类
     * $return mixed
     */
    public function queryCateList()
    {
        return $this->client->call('get', 'waimai/dish/queryCatList');
    }

    /**
     * 新增／更新菜品分类
     * $return mixed
     */
    public function addOrUpdCate($oldCatName, $catName, $sequence)
    {
        return $this->client->call('post', 'waimai/dish/updateCat', ['oldCatName' => $oldCatName, 'catName' => $catName, 'sequence' => $sequence]);
    }


    /**
     * 删除菜品分类
     * $return mixed
     */
    public function delCate($catName)
    {
        return $this->client->call('post', 'waimai/dish/deleteCat', ['catName' => $catName]);
    }

    /**
     * 根据ERP的门店id查询门店下的菜品基础信息【包含美团的菜品Id】
     * $return mixed
     */
    public function queryBaseListByEPoiId($ePoiId)
    {
        return $this->client->call('get', 'waimai/dish/queryBaseListByEPoiId', ['ePoiId' => $ePoiId]);
    }

    /**
     * 根据ERP的门店id查询门店下的菜品【不包含美团的菜品Id】
     * $return mixed
     */
    public function queryListByEPoiId($ePoiId, $offset, $limit)
    {
        return $this->client->call('get', 'waimai/dish/queryListByEPoiId', ['ePoiId' => $ePoiId, 'offset' => $offset, 'limit' => $limit]);
    }


    /**
     * 建立菜品映射(美团商品与本地erp商品映射关系)
     * $return mixed
     */
    public function mapping($ePoiId, $dishMappings)
    {
        return $this->client->call('post', 'waimai/dish/mapping', ['ePoiId' => $ePoiId, 'dishMappings' => $dishMappings]);
    }


    /**
     * 批量上传／更新菜品
     * $return mixed
     */
    public function batchUpload($ePoiId, $dishes)
    {
        return $this->client->call('post', 'waimai/dish/queryListByEPoiId', ['ePoiId' => $ePoiId, 'dishes' => $dishes]);
    }


    /**
     * 更新菜品价格【sku的价格】
     * $return mixed
     */
    public function updatePrice($ePoiId, $dishSkuPrices)
    {
        return $this->client->call('post', 'waimai/dish/updatePrice', ['ePoiId' => $ePoiId, 'dishSkuPrices' => $dishSkuPrices]);
    }


    /**
     * 更新菜品库存【sku的库存】
     * $return mixed
     */
    public function updateStock($ePoiId, $dishSkuStocks)
    {
        return $this->client->call('post', 'waimai/dish/updateStock', ['ePoiId' => $ePoiId, 'dishSkuStocks' => $dishSkuStocks]);
    }


    /**
     * 删除菜品
     * $return mixed
     */
    public function delete($ePoiId, $eDishCode)
    {
        return $this->client->call('post', 'waimai/dish/delete', ['ePoiId' => $ePoiId, 'eDishCode' => $eDishCode]);
    }


    /**
     * 删除菜品sku
     * $return mixed
     */
    public function deleteSku($eDishCode, $eDishSkuCode)
    {
        return $this->client->call('post', 'waimai/dish/deleteSku', ['eDishCode' => $eDishCode, 'eDishSkuCode' => $eDishSkuCode]);
    }


    /** 上传菜品图片，返回图片id
     * @param $image 文件base64字节流
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
            ['contentType：multipart/form-data']
        );
    }

}