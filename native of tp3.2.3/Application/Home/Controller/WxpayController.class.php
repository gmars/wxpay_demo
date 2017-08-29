<?php
/**
 * Created by PhpStorm.
 * Author: weiyongqiang <hayixia606@163.com>
 * Site: www.weiyongqiang.com
 * Date: 2016/11/20
 * Time: 13:00
 */

namespace Home\Controller;
use Think\Controller;

class WxpayController extends Controller
{
    public function _initialize()
    {
        vendor('WxPay.lib.WxPayData');
        vendor('WxPay.lib.WxPayApi');
        vendor('WxPay.lib.WxPayException');
        vendor('WxPay.lib.WxPayNotify');
    }

    /**
     * 支付回调方法
     */
    public function scanner()
    {
        $postXml = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArr = (array)simplexml_load_string($postXml,'SimpleXMLElement',LIBXML_NOCDATA);
        try {
            $outorder = \WxPayConfig::MCHID.date("YmdHis");

            //添加订单信息进入商家系统
            /**
             * 可以再此添加一些其他的业务流程
             */
            M('OrderData')->add(['product_id'=>$postArr['product_id'],'out_trade_no'=>$outorder, 'is_pay'=>0]);

            /**
             * 添加开发日志
             */
            M('log')->add(['data' => $postXml, 'time' => time()]);

            $input = new \WxPayUnifiedOrder();
            $input->SetBody("魏老师的支付测试");
            $input->SetAttach("魏老师的支付测试");
            $input->SetOut_trade_no($outorder);
            $input->SetTotal_fee("1");
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("魏老师的支付测试");
            $input->SetNotify_url("http://www.weiyongqiang.com/Home/Wxpay/notify.html");
            $input->SetTrade_type("NATIVE");
            $input->SetOpenid($postArr['openid']);
            $input->SetProduct_id($postArr['product_id']);
            $result = \WxPayApi::unifiedOrder($input);

            /**
             * 将数据组装成xml格式
             */
            $xml = "<xml>";
            foreach($result as $k=>$v)
            {

                if (is_numeric($v)){
                    $xml.="<".$k.">".$v."</".$k.">";
                }else{
                    $xml.="<".$k."><![CDATA[".$v."]]></".$k.">";
                }

                //M('log')->add(['data' => $k.'=='.$v, 'time' => time()]);
            }
            $xml.="</xml>";
            //M('log')->add(['data' => $xml, 'time' => time()]);
            //将prepay_id返回给微信支付系统
            echo $xml;

        }catch(\WxPayException $e)
        {
            M('log')->add(['data' => $e->getMessage(), 'time' => time()]);
        }
    }

    /**
     * 微信支付系统通知支付结果给商户系统
     */
    public function notify()
    {
        $postdata = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArr = (array)simplexml_load_string($postdata,'SimpleXMLElement',LIBXML_NOCDATA);
        if ($postArr['return_code'] == 'SUCCESS') {
            M('log')->add(['data' => $postArr['out_trade_no'], 'time' => time()]);
            M('OrderData')->where(['out_trade_no'=>$postArr['out_trade_no']])->save(['is_pay'=>1,'pay_time'=>time()]);
        }else{
            M('log')->add(['data' => '支付失败', 'time' => time()]);
        }

    }


    /**
     * 用户界面询问支付结果
     */
    public function getResult()
    {
        $pid = I('get.pid');
        $result = M('OrderData')->where(['product_id'=>$pid])->find();
        if ($result['is_pay'] == 1) {
            echo json_encode(['status'=>1]);
        }else{
            echo json_encode(['status'=>0]);
        }

    }

    public function testit($pid)
    {
        $result = M('OrderData')->where(['product_id'=>$pid])->find();
        if ($result['is_pay'] == 1) {
            echo json_encode(['status'=>1]);
        }else{
            echo json_encode(['status'=>0]);
        }
    }
}



