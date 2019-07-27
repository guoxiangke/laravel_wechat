<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LyLts;
use App\Services\Upyun;

class LyLtsController extends Controller
{
    public static function get($index, $offset = 0)
    {
        //3位数关键字xxx
        //TODO 历史节目1-3-7 权限控制

        //region
        $lyLts = LyLts::all()->toArray();
        //100-700-900 todo
        if (substr($index, 1) === '00') {
            $menu_function = null;
            // others 100-800
            $num = substr($index, 0, 1);
            $begin = $num.'00';
            $end = $num.'99';
            $text = '';
            $add = '★';

            foreach ($lyLts as $mav) {
                $mavDir = $mav['category'];
                $key = $mav['index'];
                if ($key > $begin && $key <= $end) {
                    if ($mavDir == '本科课程') {
                        $add = '✿';
                    }
                    if ($mavDir == '进深课程') {
                        $add = '☆';
                    }
                    if ($mavDir == '专辑课程') {
                        $add = '*';
                    }
                    $text .= '【#'.$key.'】'.$mav['name'].$add."\n";
                }
            }

            return [
                'type'          => 'text',
                'ga_data'       => [
                    'category' => 'lts33',
                    'action'   => 'menu_'.$index,
                ],
                'offset'   => $offset,
                'content'  => $text,
            ];
        }

        $mavs = array_pluck($lyLts, 'index');
        if (!in_array($index, $mavs)) {
            return [
                'type'          => 'text',
                'ga_data'       => [
                    'category' => 'lts33',
                    'action'   => '编码超出范围',
                ],
                'offset'   => $offset,
                'content'  => '[大哭]编码有误，回复【#100】查看目录！',
            ];
        }
        $cdn = LyLts::CDN;
        $tmp_key = array_search($index, $mavs);
        $mav = $lyLts[$tmp_key];

        $total = $mav['count'];
        $mavDir = LyLts::CATEGORY[$mav['category']];
        $mavLevel = $mavDir == '基础课程' ? '启航课程' : $mavDir;
        $mavTitle = $mav['name'];
        $count = $offset;
        if ($count > $total) {
            $text = "[大哭]编码有误，超出最大范围！\n回复【#".$index.'0】查看指引！';

            return [
                'type'          => 'text',
                'ga_data'       => [
                    'category' => 'lts33',
                    'action'   => '编码不对',
                ],
                'offset'   => $offset,
                'content'  => $text,
            ];
        }

        if ($count === '0') {//获取目录概括+//获取pdf
            $mavSummary = <<<EOF
良友圣经学院《{$mavTitle}》
本课程共有{$mav['count']}课
属于{$mavLevel}
授课老师：{$mav['author']}
每日获取回复【#{$index}】
或【#{$index}1】～【#{$index}{$total}】
注意加#号，可不带【】⚠️
EOF;
            $path = LyLts::CDN_PREFIX.$mavDir.'/'.$mavTitle.'/'.$mavTitle.'.pdf';
            //$sign = Upyun::sign($path, Upyun::TTL);
            $path = LyLts::CDN_PREFIX.urlencode($mavDir).'/'.urlencode($mavTitle).'/'.$mavTitle.'.pdf';
            $link = $cdn.$path; //. $sign;

            $temp = @get_headers($link, 1)
                    or die("Unable to connect to $link");
            if ($temp[0] != 'HTTP/1.1 200 OK') {
                $text = $mavSummary.PHP_EOL.'本课程暂无讲义[抱拳]';

                return [
                    'type'          => 'text',
                    'ga_data'       => [
                        'category' => 'lts33',
                        'action'   => '远程暂无',
                    ],
                    'offset'   => $offset,
                    'content'  => $text,
                ];
            }
            $mavSummaryLinks = "<a href='{$link}'>》讲义链接</a>";
            $text = $mavSummary.PHP_EOL.$mavSummaryLinks;

            return [
                'type'          => 'text',
                'ga_data'       => [
                    'category' => 'lts33',
                    'action'   => '编码超出范围',
                ],
                'offset'   => $offset,
                'content'  => $text,
            ];
        }
        if ($offset == 0) {
            $count = date('z') % $total; //按顺序循环播出，每天一集！
            $count++;
        }
        $rep = str_pad($count, 2, '0', STR_PAD_LEFT);
        $path = LyLts::CDN_PREFIX.$mavDir.'/'.$mavTitle.'/ma'.$mav['code'].$rep.'.mp3';
        //$sign = Upyun::sign($path, Upyun::TTL);
        $path = LyLts::CDN_PREFIX.urlencode($mavDir).'/'.urlencode($mavTitle).'/ma'.$mav['code'].$rep.'.mp3';
        $link = $cdn.$path; //. $sign;
        // target_type = lylts
        // target_id = 1-22
        $commentDate = $mav['id'].','.$count; //$mav['id'],12
        $res = [
            'type'          => 'music',
            'ga_data'       => [
                'category' => 'lts33',
                'action'   => $mavTitle.'-'.$rep,
            ],
            'subscribe_id'   => $mav['id'],
            'comment_id'     => $commentDate,
            'custom_message' => false, //todo 良友圣经学院pdf等资料
            'offset'         => $count,
            'content'        => [
                'title'          => $mavTitle.'-'.$rep,
                'description'    => '点击▶️收听'.' 良院-'.$mavLevel,
                'url'            => $link,
                'hq_url'         => $link,
                'thumb_media_id' => null,
            ],
        ];

        return $res;
        //endregion
    }
}
