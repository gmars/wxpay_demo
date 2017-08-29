<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {

    public function index($pid){
        //引入微信支付sdk
        vendor('WxPay.lib.WxPayApi');
        //引入生成二维码类库
        vendor("phpqrcode.phpqrcode");
        //给微信扫码支付模式一的数据类型赋值
        $biz = new \WxPayBizPayUrl();
        $biz->SetProduct_id($pid);
        //生成微信扫码支付模式一二维码的规则
        $values = \WxpayApi::bizpayurl($biz);
        $url = "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);

        //将url生成二维码
        // 纠错级别：L、M、Q、H
        $level = 'L';
        // 点的大小：1到10,用于手机端4就可以了
        $size = 4;
        // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
        $path = "qrcode/";
        // 生成的文件名
        $fileName = $path.time().'.png';
        \QRcode::png($url, $fileName, $level, $size);

        $this->assign('product_id', $pid);
        $this->assign('filename', $fileName);
        $this->display();
    }

    /**
     *
     * 参数数组转换为url参数
     * @param array $urlObj
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            $buff .= $k . "=" . $v . "&";
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    public function test()
    {

        $data = json_decode('{"appid":"wx005ba882c8519d98","code_url":"weixin:\/\/wxpay\/bizpayurl?pr=caeVvKH","mch_id":"1345487901","nonce_str":"53DQZierLfI8vPKX","prepay_id":"wx2016112109243368655c8c130065891962","result_code":"SUCCESS","return_code":"SUCCESS","return_msg":"OK","sign":"63E549788C2D973F958C280889EE44C1","trade_type":"NATIVE"}');
        $xml = "<xml>";
        foreach($data as $k=>$v)
        {

            if (is_numeric($v)){
                $xml.="<".$k.">".$v."</".$k.">";
            }else{
                $xml.="<".$k."><![CDATA[".$v."]]></".$k.">";
            }

            //M('log')->add(['data' => $k.'=='.$v, 'time' => time()]);
        }
        $xml.="</xml>";
        echo $xml;

    }
}