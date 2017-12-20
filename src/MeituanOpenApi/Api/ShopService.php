<?php

namespace MeituanOpenApi\Api;

/**
 * 店铺服务
 */
class ShopService extends RpcService
{

    /** 门店置营业
     * 设置门店营业，门店营业后用户才可以下单。通过appAuthToken定位到要操作的门店。
     * @return mixed
     */
    public function open()
    {
        return $this->client->call('post','waimai/poi/open', []);
    }


    /** 门店置休息
     * 设置门店状态为休息中。通过appAuthToken定位到要操作的门店。
     * @return mixed
     */
    public function close()
    {
        return $this->client->call('post','waimai/poi/close', []);
    }


    /** 修改门店营业时间
     * @param $openTime
     * @return mixed
     */
    public function updateOpenTime($openTime)
    {
        return $this->client->call('post','waimai/poi/updateOpenTime', ['openTime' => $openTime]);
    }


    /** 查询门店信息
     * @param $ePoiIds 门店Ids
     * @return mixed
     */
    public function queryPoiInfo($ePoiIds)
    {
        return $this->client->call('get','waimai/poi/queryPoiInfo', ['ePoiIds' => $ePoiIds]);
    }


    /** 查询门店评价信息
     * @param $ePoiIds 门店Ids
     * @param $startTime 
     * @param $endTime 
     * @param $offset 
     * @param $limit 
     * @return mixed
     */
    public function queryReviewList($ePoiIds, $startTime, $endTime, $offset, $limit)
    {
        return $this->client->call('get', 'waimai/poi/queryReviewList', [
                'ePoiId' => $ePoiId,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'offset' => $offset,
                'limit' => $limit
            ]);
    }


    /** 查询门店是否延迟发配送
     * 延迟发配送只针对美团配送的 自建、代理两种配送方式
     * @return mixed
     */
    public function queryDelayDispatch()
    {
        return $this->client->call('get','waimai/poi/queryDelayDispatch', []);
    }


    /** 设置延迟发配送时间
     * 如果门店在延迟发配送名单内，才能设置延迟时间。延迟配送的前提必须是自建配送或者代理配送，且需要由美团的销售人员另外申请才可以使用该功能。
     * @param $delaySeconds
     * @return mixed
     */
    public function updateDelayDispatch($delaySeconds)
    {
        return $this->client->call('post','waimai/poi/updateDelayDispatch', ['delaySeconds' => $delaySeconds]);
    }

}