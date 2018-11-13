<?php
namespace Home\Controller;
use Think\Controller;
class ComicPublicController extends Controller {
    /**
     * 获取漫画图片详情
     * @param  int $comicId 漫画ID
     * @param  int $chapter 章节
     * @return arr          漫画图片数组
     */
    public function getComicDetail($comicId, $chapter){
        $path = "Uploads/comic/".$comicId."/".$chapter."/*";
        $folder = glob($path);
        asort($folder,SORT_NATURAL);
        return array_values($folder);
    }

    /**
     * 验证费用
     * @param  int $comicId 漫画ID
     * @param  int $chapter 章节
     * @param  string $openid  字符串
     * @return 1：免费 2：已购买 -1：未购买
     */
    public function checkCost($comicId, $chapter, $openid){
        $comic = M('comics','','DB_COMIC');
        $consume = M('consume_order','','DB_COMIC');

        $comicInfo = $comic->find($comicId);
        $freeChapter = $comicInfo['free_chapter'];

        if ($chapter <= $freeChapter) {
            // 免费章节
            return 1;
        } else {
            // 付费章节
            $cond_consume = [
                'comic_id' => $comicId,
                'openid'   => $openid,
                'chapter'  => $chapter,
                'status'   => C('STATUS_Y')
            ];
            $consumeInfo = $consume->where($cond_consume)->find();
            if ($historyInfo) {
                // 已购买
                return 2;
            } else {
                // 未购买
                return '-1';
            }
        }
    }
}