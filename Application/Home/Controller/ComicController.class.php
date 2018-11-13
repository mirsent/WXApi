<?php
namespace Home\Controller;
use Think\Controller;
class ComicController extends Controller {

    /**
     * 登录
     */

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
     * 漫画
     */

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
     * 按发布类型获取漫画列表
     * 1.按sort倒叙
     * 2.限制数量
     * 3.只get类型下设置漫画的
     */
    public function get_comic_by_release(){
        $cond['status'] = C('STATUS_Y');
        $data = M('release_type','','DB_COMIC')
            ->field('id as release_type_id,release_type_name')
            ->order('sort desc')
            ->where($cond)
            ->select();

        $comic = M('comics','','DB_COMIC');
        foreach ($data as $key => $value) {
            $cond_comic = [
                'status' => C('STATUS_Y'),
                'release_type_id' => $value['release_type_id']
            ];
            $comicList = $comic
                ->field('id,cover,title,brief')
                ->limit(C('INDEX_SHOW'))
                ->order('sort desc')
                ->where($cond_comic)
                ->select();

            if ($comicList) {
                $data[$key]['products'] = $comicList;
            } else {
                unset($data[$key]);
            }
        }

        ajax_return(1, '漫画列表', array_filter($data));
    }

    /**
     * 获取漫画列表
     * @param type 漫画类型 -1：全部
     */
    public function get_comic_list(){
        $cond['c.status'] = C('C_STATUS_U');

        // 发布类型
        $release = I('release');
        if ($release) {
            $cond['release_type_id'] = $release;
        }

        // 漫画类型
        $type = I('type');
        if ($type != '-1') {
            $cond['_string'] = 'FIND_IN_SET('.$type.', type_ids)';
        }

        $data = M('comics','','DB_COMIC')
            ->alias('c')
            ->join(C('DB_COMIC_NAME').'.release_type rt ON rt.id = c.release_type_id')
            ->field('c.*,release_type_name')
            ->order('sort desc')
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
     * 获取漫画信息
     */
    public function get_comic_info(){
        $cond['id'] = I('comic_id');
        $data = M('comics','','DB_COMIC')
            ->field('id,cover,title,brief,type_ids,heat,popularity,total_chapter,s_serial')
            ->where($cond)
            ->find();
        $cond_type = [
            'status' => C('STATUS_Y'),
            'id'     => array('in', $data['type_ids'])
        ];
        $data['types'] = M('comic_type','','DB_COMIC')
            ->where($cond_type)
            ->getField('comic_type_name', true);

        $data['s_serial_name'] = $data['s_serial'] == C('C_SERIAL_L') ? '连载中' : '已完结';

        ajax_return(1, '漫画信息', $data);
    }

    /**
     * 获取漫画章节列表
     * @param comic_id 漫画ID
     * @return catalog_name 第1章
     */
    public function get_comic_chapter(){
        $cond = [
            'status'   => C('STATUS_Y'),
            'comic_id' => I('comic_id')
        ];
        $data = M('chapter','','DB_COMIC')
            ->where($cond)
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['chapter_title'] = $value['chapter_title'] ?: '';
            $data[$key]['catalog_name'] = '第'.$value['catalog'].'章';
        }

        ajax_return(1, '漫画章节', $data);
    }

    /**
     * 阅读
     */
    public function reading(){
        $cp = A('ComicPublic');

        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter');

        if (!$chapter) {
            // 直接阅读
            $cond_history = [
                'comic_id' => $comicId,
                'openid'   => $openid
            ];
            $historyInfo = M('history','','DB_COMIC')->where($cond_history)->find();

            if ($historyInfo) {
                // 历史记录
                $chapter = $historyInfo['chapter'];
            } else {
                // 首次
                $chapter = 1;
            }
        }

        $data = $cp->getComicDetail($comicId, $chapter);

        ajax_return(1, '漫画阅读', $data);
    }


    /**
     * 获取漫画图片
     * @param comic_id 漫画ID
     * @param chapter 章节
     * @param openid 读者身份openid
     */
    public function get_comic_imgs(){
        $comicId = I('comic_id');
        $openid = I('openid');

        // 查询历史记录
        $history = M('history','','DB_COMIC');
        $cond_history = [
            'comic_id' => $comicId,
            'openid'   => $openid
        ];
        $historyInfo = $history->where($cond_history)->find();
        if ($historyInfo) {
            $chapter = $historyInfo['chapter'];
        } else {
            // 初次阅读,记录
            $chapter = 1;
            $data_history = [
                'comic_id'  => $comicId,
                'openid'    => $openid,
                'chapter'   => $chapter,
                'last_time' => date('Y-m-d H:i:s')
            ];
            $history->add($data_history);
        }

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


    /**
     * 读者
     */

    /**
     * 点赞
     * @param comic_id 漫画ID
     * @param reader_id 读者ID
     */
    public function like(){
        $data = [
            'comic_id'  => I('comic_id'),
            'reader_id' => I('reader_id'),
            'create_at' => date('Y-m-d H:i:s'),
            'status'    => C('STATUS_Y')
        ];
        $res = M('like','','DB_COMIC')->add($data);

        if ($res === false) {
            ajax_return(0, '点赞失败');
        }
        ajax_return(1);
    }

    /**
     * 收藏
     * @param comic_id 漫画ID
     * @param reader_id 读者ID
     */
    public function collect(){
        $data = [
            'comic_id'  => I('comic_id'),
            'reader_id' => I('reader_id'),
            'create_at' => date('Y-m-d H:i:s'),
            'status'    => C('STATUS_Y')
        ];
        $res = M('collect','','DB_COMIC')->add($data);

        if ($res === false) {
            ajax_return(0, '收藏失败');
        }
        ajax_return(1);
    }

}