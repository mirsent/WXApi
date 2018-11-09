<?php
namespace Home\Controller;
use Think\Controller;
class ComicController extends Controller {

    /**
     * 登录凭证校验
     */
    public function code_2_session()
    {
        $appid = C('WX_TEST_CONFIG.APPID');
        $secret = C('WX_TEST_CONFIG.APPSECRET');
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.I('js_code').'&grant_type=authorization_code';
        $info = file_get_contents($url);
        $json = json_decode($info, true);

        ajax_return(1, '凭证校验', $json);
    }

    /**
     * 获取漫画类型
     */
    public function get_comic_type()
    {
        $all[] = [
            'id'              => '-1',
            'comic_type_name' => '全部',
            'is_on'           => true
        ];
        $cond['status'] = C('STATUS_Y');
        $data = M('comic_type','','DB_COMIC')
            ->where($cond)
            ->field('*,null as is_on')
            ->select();
        ajax_return(1, '漫画类型', array_merge($all, $data));
    }

    /**
     * 获取漫画banner
     */
    public function get_comic_banner(){
        $cond['status'] = C('STATUS_Y');
        $data = M('comic_banner','','DB_COMIC')
            ->where($cond)
            ->order('sort desc')
            ->select();
        ajax_return(1, '漫画banner列表', $data);
    }

    /**
     * 获取漫画列表
     */
    public function get_comic_list(){
        $cond['c.status'] = C('C_STATUS_U');

        $type = I('type');
        if ($type != '-1') {
            $cond['_string'] = 'FIND_IN_SET('.$type.', type_ids)';
        }

        $data = M('comics','','DB_COMIC')
            ->alias('c')
            ->join(C('DB_COMIC_NAME').'.release_type rt ON rt.id = c.release_type_id')
            ->field('c.*,release_type_name')
            ->where(array_filter($cond))
            ->select();

        $type = M('comic_type','','DB_COMIC');
        foreach ($data as $key => $value) {
            $cond_type = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['type_ids'])
            ];
            $typeArr = $type->where($cond_type)->getField('comic_type_name', true);
            $data[$key]['type_names'] = implode('；', $typeArr);
        }

        ajax_return(1, '漫画列表', $data);
    }

    /**
     * 获取漫画图片
     * @param comic_id 漫画ID
     * @param chapter 章节
     * @param openid 读者身份ID
     */
    public function get_comic_imgs(){
        $comicId = I('comic_id');
        $chapter = I('chapter');
        $openid = I('openid');

        $cond['id'] = $comicId;
        $comicInfo = M('comics','','DB_COMIC')->where($cond)->find();
        $totalChapter = $comicInfo['total_chapter']; // 总章节
        $freeChapter = $comicInfo['free_chapter']; // 免费章节
        $preChapterPay = $comicInfo['pre_chapter_pay']; // 章节费用
        $sFee = $comicInfo['s_fee']; // 收费/免费

        if ($chapter > $totalChapter) {
            ajax_return(2, '超出章节');
        }

        if ($sFee == C('C_FEE_Y') && $chapter > $freeChapter) {
            // 收费章节
            $cond_reader['openid'] = $openid;
            $readerInfo = M('reader','','DB_COMIC')->where($cond_reader)->find();
            $balance = $readerInfo['balance']; // 余额

            // 是否已买
            $cond_consumed = [
                'openid'   => $openid,
                'comic_id' => $comicId,
                'chapter'  => $chapter
            ];
            $isConsumed = M('consume','','DB_COMIC')->where($cond_consumed)->find();

            if (!$isConsumed) {
                if ($preChapterPay > $balance) {
                    ajax_return(3, '余额不足');
                }

                // todo 消费
            }
        }

        $path = "Uploads/comic/".$comicId."/".$chapter."/*";
        $folder = glob($path);
        asort($folder,SORT_NATURAL);
        ajax_return(1, '漫画图片列表', array_values($folder));
    }
}