<?php
namespace Home\Controller;
use Think\Controller;
class DingShengController extends Controller {

    /**
     * 登录凭证校验
     */
    public function code_2_session()
    {
        $appid = C('WX_DS_CONFIG.APPID');
        $secret = C('WX_DS_CONFIG.APPSECRET');
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.I('js_code').'&grant_type=authorization_code';
        $info = file_get_contents($url);
        $json = json_decode($info, true);

        $openid = $json['openid'];

        M('openid','','DB_DS')->add(['openid'=>$json['openid']]);

        $rule = [
            'oAunM4psS7HxSg1O3H50NOEfkjI8',
            'oAunM4u0KEtPno6SoKgKRY9zgt9Q',
            'oAunM4noOcKB88HwxIpiAUkSHfX8',
            'oAunM4ijW8sXUTegpaJz_pUC1c-I'
        ];
        if (in_array($openid, $rule)) {
            ajax_return(1, '有权限');
        }
        ajax_return(0, '没有权限');
    }

    /**
     * 获取公司信息
     */
    public function get_company_info()
    {
        $data = M('company','','DB_DS')->find(1);
        ajax_return(1, '公司信息', $data);
    }

    /**
     * 获取产品类型
     */
    public function get_pro_type()
    {
        $all[] = '全部';
        $cond['status'] = C('STATUS_Y');
        $data = M('t_product','','DB_DS')
            ->where($cond)
            ->getField('product_type_name', true);
        ajax_return(1, '产品类型', array_merge($all,$data));
    }

    /**
     * 获取产品banner
     */
    public function get_pro_banner()
    {
        $cond = [
            'status' => C('STATUS_Y'),
        ];
        $data = M('pro_banner','','DB_DS')
            ->where($cond)
            ->order('turn')
            ->select();
        ajax_return(1, '产品banner', $data);
    }

    /**
     * 获取产品页信息
     */
    public function get_pro_info()
    {
        $cond['status'] = C('STATUS_Y');

        // 产品类型
        $all[] = '全部';
        $type = M('t_product','','DB_DS')
            ->where($cond)
            ->getField('product_type_name', true);

        // 产品banner
        $banner = M('pro_banner','','DB_DS')
            ->where($cond)
            ->order('turn')
            ->select();

        $data = [
            'type'   => array_merge($all,$type),
            'banner' => $banner
        ];
        ajax_return(1, '产品页信息', $data);
    }

    /**
     * 获取产品列表
     * @param type 搜索类型
     */
    public function get_pro_list()
    {
        $cond['status'] = C('STATUS_Y');

        $type = I('type');
        if ($type && $type != '全部') {
            $cond_type = [
                'status'            => C('STATUS_Y'),
                'product_type_name' => $type
            ];
            $typeId = M('t_product','','DB_DS')->where($cond_type)->getField('id');
            $cond['product_type_id'] = $typeId;
        }


        $data = M('product','','DB_DS')
            ->where($cond)
            ->field('id,product_title,product_brief,product_url')
            ->select();
        ajax_return(1, '产品列表', $data);
    }

    /**
     * 获取产品详情
     */
    public function get_pro_detail()
    {
        $cond['p.id'] = I('pro_id');
        $data = M('product','','DB_DS')
            ->alias('p')
            ->join('dsdb.t_product tp ON p.product_type_id = tp.id')
            ->field('p.id,product_title,product_url,product_detail,product_type_name')
            ->where($cond)
            ->find();
        $data['product_detail'] = htmlspecialchars_decode($data['product_detail']);
        ajax_return(1, '产品详情', $data);
    }

    /**
     * 获取开票信息
     */
    public function get_bill_info()
    {
        $company = M('company','','DB_DS')->field('company_wx_code')->find(1);
        $data = M('bill','','DB_DS')->find(1);
        $data['wx_code'] = $company['company_wx_code'];
        ajax_return(1, '开票信息', $data);
    }

    /**
     * 获取订单页数据
     * @return demand 需方
     * @return standard 规格
     * @return origin 产地
     * @return unit 单位
     */
    public function get_order_info()
    {
        $cond['status'] = C('STATUS_Y');

        $bill = M('bill','','DB_DS')->where($cond)->find(1);
        $company = M('company','','DB_DS')->where($cond)->find(1);
        $product = M('product','','DB_DS')->where($cond)->getField('product_title', true);
        $demand = M('demand','','DB_DS')->where($cond)->getField('name', true);
        $standard = M('standard','','DB_DS')->where($cond)->getField('standard_name', true);
        $origin = M('origin','','DB_DS')->where($cond)->getField('origin_name', true);
        $unit = M('unit','','DB_DS')->where($cond)->getField('unit_name', true);

        $data = [
            'product'  => $product,
            'bill'     => $bill,
            'company'  => $company,
            'demand'   => $demand,
            'standard' => $standard,
            'origin'   => $origin,
            'unit'     => $unit,
            'today'    => date('Y年n月j日')
        ];
        ajax_return(1, '订单信息', $data);
    }



    /**
     * 添加需方信息
     */
    public function add_demand()
    {
        $demand = M('demand','','DB_DS');
        $demand->create();
        $demand->status = C('STATUS_Y');
        $res = $demand->add();

        if ($res === false) {
            ajax_return(0, '添加需方失败');
        }

        $cond['status'] = C('STATUS_Y');
        $data = $demand->where($cond)->getField('name', true);
        ajax_return(1, '添加需方成功', $data);
    }

    /**
     * 添加规格信息
     */
    public function add_standard()
    {
        $standard = M('standard','','DB_DS');
        $standard->create();
        $standard->status = C('STATUS_Y');
        $res = $standard->add();

        if ($res === false) {
            ajax_return(0, '添加规格失败');
        }

        $cond['status'] = C('STATUS_Y');
        $data = $standard->where($cond)->getField('standard_name', true);
        ajax_return(1, '规格信息', $data);
    }

    /**
     * 根据需方名称获取需方信息
     */
    public function get_demand_by_name()
    {
        $cond= [
            'status' => C('STATUS_Y'),
            'name'   => I('demand_name')
        ];
        $data = M('demand','','DB_DS')->where($cond)->find();
        ajax_return(1, '需方信息', $data);
    }

    /**
     * 金额转大写
     */
    public function convertUp()
    {
        $order = M('order','','DB_DS');
        $order->create();
        $order->create_at = date('Y-m-d H:i:s');
        $order->status = C('STATUS_Y');
        $order->add();

        $amount = I('number') * I('unit_price');
        $data = [
            'amount'         => $amount,
            'capital_amount' => num2rmb($amount)
        ];
        ajax_return(1, '大写金额', $data);
    }
}