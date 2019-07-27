<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/15
 * Time: 下午9:38.
 */

namespace App\Http\Controllers\Api;

use App\Models\LyMeta;
use App\Models\LyAudio;
use App\Services\Upyun;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class LyMetaController extends Controller
{
    public function index()
    {
        $lyMetas = LyMeta::active()->orderBy('category')->get();

        return view('lymeta.index', [
            'lymetas'  =>  $lyMetas,
            'categorys' => LyMeta::CATEGORY,
            // 'shareData' => $shareData,
            // 'signPackage' => $signPackage
        ]);
    }

    public function all()
    {
        return LyMeta::active()->pluck('code');
    }

    /**
     * @param $code
     * @param int $offset
     * @return array|string
     */
    public static function get($code, $offset = 0)
    {
        $customRes = false;
        $cdnLink = LyMeta::CDN;

        $default_desc = '点击▶️收听';
        $customMessage = false;
        $lyMeta = $code;
        if (is_string($lyMeta)) {
            $lyMeta = LyMeta::where('code', $code)->firstOrFail();
        }
        $title = $lyMeta->name;
        $index = $lyMeta->index;
        //dynamic get from http://txly2.net/
        if ($index >= 641 && $index <= 645) {
            $ltsUrl = 'http://txly2.net'; //todo
            $programUrl = $ltsUrl.'/'.$code;
            $html = file_get_contents($programUrl);
            preg_match_all('/((?<=setup\(\{)|(?<=,\{))[^}]*(?=\})/', $html, $matches);
            //TODO 10天内节目
            if (! isset($matches[0]) || count($matches[0]) < 15) {
                Log::error(__CLASS__, [__FUNCTION__, __LINE__, '没有抓去到lts这么多节目', $index, $code, $matches[0]]);

                return false;
            }
            if ($offset > 20) {
                return [
                    'type'=>'text',
                    'ga_data'       => [
                        'category' => 'lyapi_error',
                        'action'   => '您无权限,超出范围',
                    ],
                    'offset'   => $offset,
                    'content'=> '您无权限,超出范围',
                ];
            }
            $mp3Files = [];
            $titles = [];
            array_map(
                function ($str) use (&$mp3Files, &$titles) {
                    preg_match('/(?<=:\')[^\']*(?=\')/', $str, $matches);
                    if (is_array($matches) && isset($matches[0])) {
                        $mp3Files[] = $matches[0];
                    }
                    preg_match('/(?<=title:\')[^\']*(?=\')/', $str, $matches);
                    if (is_array($matches) && isset($matches[0])) {
                        $titles[] = $matches[0];
                    }
                },
                $matches[0]
            );
            $pq = \phpQuery::newDocumentHTML($html);
            $descriptions = [];
            for ($no = 0; $no < 20; $no++) {
                $selector = "#sermon{$no}:first p"; //'.cat-list-row'.$no.':first p';
                $text = $pq->find($selector)->remove('span')->text();
                $descriptions[] = $text;
            }
            $hqUrl = $ltsUrl.$mp3Files[$offset];

            return [
                'type'=>'music',
                'comment_id' => 0,
                'subscribe_id' => $lyMeta->id,
                'ga_data'       => [
                    'category' => 'lyapi_audio',
                    'action'   => $title,
                ],
                'offset'   => $offset,
                'custom_message' => $descriptions[$offset],
                'content'=>[
                    'title'          => $titles[$offset].' '.str_replace('良友圣经学院', '', $title),
                    'description'    => $default_desc,
                    'url'            => $hqUrl,
                    'hq_url'         => $hqUrl,
                    'thumb_media_id' => null,
                ],
            ];
        }
        $has_program = false;
        $tmp_offset = 0;

        $time = time();
        do {
            $tmp_time = $time - $offset * 86400;
            $date = date('ymd', $tmp_time); //161129
            $titleDate = date('n/j', $tmp_time); //161129
            $tmp_path = LyMeta::CDN_PREFIX.date('Y', $tmp_time).'/'.$code.'/'.$code;
            $commentDate = $date; //rt,161127
            $path = $tmp_path.$date.'.mp3'; // /2016/rt/rt161127.mp3
            $uri = $cdnLink.$path;
            $musicUrl = $uri; //. Upyun::sign($path);
            $temp = @fast_headers($musicUrl, 1)
                or die("Unable to connect to $musicUrl");
            if ($temp[0] == 'HTTP/1.1 200 OK') {//远程有!!!
                $has_program = true;
                //bug no news for mw
                $lyAudio = LyAudio::where('play_at', (int) $date)
                    ->where('target_id', $lyMeta->id)
                    ->first();
                if ($lyAudio) {
                    if ($lyAudio->excerpt) {
                        $customMessage = $lyAudio['excerpt'];
                    }
                    if ($lyAudio->body) {
                        $customRes = [
                            'type'  => 'news',
                            'content' => [
                                'title'       => $title.' '.$titleDate,
                                'description' => $lyAudio->excerpt,
                                'url'         => route('LyAudio.show', ['slug'=>$lyAudio->slug]),
                                'image'       => $lyMeta->image,
                            ],
                            'ga_data' => [
                                'category' => 'lymeta_news',
                                'action'   => $lyMeta->name,
                            ],
                        ];
                        // dd($tplMessage);
                    }
                }
                break;
            } else {
                $offset++;
                $tmp_offset++;
            }
        } while (! $has_program && $tmp_offset < 7); //上下范围7天

        if (! $has_program) {
            return [
                 'type'=>'text',
                 'ga_data'       => [
                     'category' => 'lyapi_error',
                     'action'   => '上下范围7天内无节目',
                 ],
                 'offset'   => $offset,
                 'content'=> '上下范围7天内无节目',
             ];
        }

        if ($offset) {
            $title .= ' '.$titleDate;
        }
        // target_type = lymeta
        // target_id = 161127
        $commentDate = $lyMeta->id.','.$commentDate;

        return [
            'type'=>'music',
            'comment_id' => $commentDate,
            'subscribe_id' => $lyMeta->id,
            'ga_data'       => [
                'category' => 'lyapi_audio',
                'action'   => $lyMeta->name,
            ],
            'custom_res' => $customRes,
            'custom_message' => $customMessage,
            'offset'   => $offset,
            'content'=>[
                'title'          => $title,
                'description'    => $default_desc,
                'url'            => $musicUrl,
                'hq_url'         => $musicUrl,
                'thumb_media_id' => null,
            ],
        ];
    }

    // update from api.terms or 729ly.
    public static function get_liangyou_audio_list()
    {
        return [
            'ib'      => [
                'title'    => '无限飞行号',
                'day'      => '17', //17=>1-7 15=>1-5 67=>weekend 7=>7 6=>6 135=>135 74 周五周六无,
                'index'    => 601,
                'lywx'     => 202,
                'author'   => '',
                'imageurl' => '',
                'category' => '关怀辅导',
                'desc'     => '在这里，有与你遭遇相连的他和她；在这里，有渴望关心残疾人的你和我；在这里，有神与我们同飞行。突破身体心灵缺陷，发掘生命蕴藏的更美本质，一起登上属于你和我的《无限飞行号》',
            ],
            'im'      => [
                'title'    => 'i关怀心磁场',
                'day'      => '74',
                'index'    => 602, //74 周五周六无,
                'lywx'     => 203,
                'author'   => '',
                'imageurl' => '',
                'category' => '关怀辅导',
                'desc'     => '人是身心灵的集合体，由外而内，我们看到光鲜亮丽背后的破碎；由内向外，我们看到伤心流泪后的希望',
            ],
            'cc'      => [
                'title'    => '空中辅导',
                'day'      => '17',
                'index'    => 603,
                'desc'     => '聆听听众各类苦闷心声，以圣经和祷告回应。在对话过程中，表达体会和盼望，指引成长的方向',
                'lywx'     => 201,
                'author'   => '',
                'imageurl' => '',
                'category' => '关怀辅导',
            ],
            'se'      => [
                'title'    => '恋爱季节',
                'day'      => '15',
                'index'    => 604,
                'lywx'     => 301,
                'author'   => '',
                'imageurl' => '',
                'category' => '婚恋家庭',
                'desc'     => '有人说：“最完美的爱情在小说里，最完美的婚姻在梦境里！”难道希望拥有完美的恋爱、婚姻，只是一个梦想吗？请立刻收听《恋爱季节》，梦想就要成真！欢迎孤男寡女、一男一女、已婚男女、不分男女，一起沉浸在《恋爱季节》',
            ],
            'bf'      => [
                'title'    => '幸福满家园',
                'day'      => '67',
                'index'    => 605,
                'lywx'     => 302,
                'author'   => '',
                'imageurl' => '',
                'category' => '婚恋家庭',
                'desc'     => '幸福人生是我们的希望，美满家庭是我们的追求；在家园里学习爱和关怀，在家园里学习耕耘和灌溉；让爱的种子生长、开花、结果，让我们的家庭成为合神心意的基督化家庭！',
            ],
            'up'      => [
                'title'    => '亲情不断电',
                'day'      => '15',
                'index'    => 606,
                'lywx'     => 303,
                'author'   => '',
                'imageurl' => '',
                'category' => '婚恋家庭',
                'desc'     => '有一种情，从你出生就已经存在；有一种情，就算是死亡也无法割舍；它，塑造我们鲜明的个性；它，教会我们珍惜彼此；它就是亲情，是我们一生的牵挂',
            ],
            'hg'      => [
                'title'     => '欢乐卡恰碰',
                'day'       => '17',
                'index'     => 607,
                'lywx'      => 304,
                'author'    => '',
                'imageurl'  => '',
                'category'  => '婚恋家庭',
                'desc'      => '小小的扭蛋，大大的欢笑，轻轻转扭的“卡恰”声，“碰”出许多神秘与惊喜！在这个专属儿童的乐园里，你可以和许多小朋友一起欢笑，一起学习！快来加入我们，让我们陪你成长，与你同乐',
                'feearadio' => '1',
            ],
            'yu'      => [
                'title'     => '绝妙当家',
                'day'       => '17',
                'index'     => 608,
                'desc'      => '没有理由，不需要理由，你就是主角。绝对唯一，绝对独特，你是无可取代',
                'feearadio' => '1', //http://media.feearadio.net/program/YU/yu-151214.mp3
                'lywx'      => 104,
                'author'    => '',
                'imageurl'  => '',
                'category'  => '生活智慧',
            ],
            'fc'      => [
                'title'    => '微播出炉',
                'day'      => '15',
                'index'    => 609,
                'lywx'     => 101,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
                'desc'     => '把社会话题与福音联系起来，在媒体中发出另一种声音，让听众了解圣经如何处理这些热门话题。',
            ],
            'gv'      => [
                'title'    => '生活无国界',
                'day'      => '17',
                'index'    => 610,
                'desc'     => '幽默风趣、欢声笑语，一个快乐你生活的节目，听了做梦都会笑 天南地北、古今中外，一扇开阔你视野的窗口，精彩世界看得见 紧密互动、开放参与，一个实现你梦想的平台，下一位DJ就是我 贴心关怀、专业辅导，一位陪伴你成长的夥伴，与你同行在爱中',
                'lywx'     => 105,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
            ],
            'zz'      => [
                'title'    => '零点零距离',
                'day'      => '15',
                'index'    => 611,
                'desc'     => '透过心灵隽语、诗歌、见证、心身灵健康讨论、时事评论、周末剧场等，让你修复人际关系，提升灵命，在工作场上，无往不利',
                'lywx'     => 106,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
            ],
            'bc'      => [
                'title'     => '书香园地',
                'day'       => '15',
                'index'     => 612,
                'desc'      => '打开心灵的窗户，让书香的味道飘洒进来；走进缤纷的园地，让生命的色彩丰富起来。邀请爱书人进入《书香园地》，细细品味书中的芬芳，与刘文和作者一起寻找心灵的交汇点',
                'feearadio' => '1',
                'lywx'      => 102,
                'author'    => '',
                'imageurl'  => '',
                'category'  => '生活智慧',
            ],
            'ir'      => [
                'title'    => 'iradio爱广播',
                'day'      => '17',
                'index'    => 613,
                'desc'     => '为你带出电台最新消息，最动听节目推介，也替电台寻找听电台、爱电台的知音人，分享生命的故事。台长也会定期出现，分享电台最新的政策，也与听众分享他的个人心声，勉励事奉',
                'lywx'     => 107,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
            ],
            'rt'      => [
                'title'    => '今夜心未眠',
                'day'      => '67',
                'index'    => 614,
                'desc'     => '冲一壶好茶，选一本好书，细细品读，慢慢分享，一场心与心的对话',
                'lywx'     => 103,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
            ],
            'tr'      => [
                'title'    => '彩虹桥',
                'day'      => '15',
                'index'    => 615,
                'lywx'     => 402,
                'author'   => '',
                'imageurl' => '',
                'category' => '诗歌音乐',
                'desc'     => '诗歌音乐:《彩虹桥》用诗歌搭起人与神之间的桥梁，带来弟兄姊妹与主更亲近，爱主更深，对主的信心也越发坚强',
            ],
            'ws'      => [
                'title'    => '长夜的牵引',
                'day'      => '17',
                'index'    => 616,
                'lywx'     => 403,
                'author'   => '',
                'imageurl' => '',
                'category' => '诗歌音乐',
                'desc'     => '诗歌音乐:有一束光，用跳跃的音符，带领着你和我穿过长夜。那是牵引，那是爱，进入自由的天地，显明真理。长夜的牵引，和你一起离开黑暗，迎向那光！',
            ],
            'gn'      => [
                'title'    => '现代人的希望',
                'day'      => '15',
                'index'    => 617,
                'lywx'     => 502,
                'author'   => '',
                'imageurl' => '',
                'category' => '慕道探索',
                'desc'     => '疲累、焦虑、寂寞、压力，构筑出现代人的共同困境。唯有来自永恒的声音，才能抚慰虚空的心灵。圣经是迷途者的指南，耶稣是现代人的希望。一个给你带来永恒盼望的节目',
            ],
            'dy'      => [
                'title'    => '献上今天', //ns 生命的四季
                'day'      => '15',
                'index'    => 618,
                'lywx'     => 602,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '生命的四季',
            ],
            'ee'      => [
                'title'    => '拥抱每一天',
                'day'      => '17',
                'index'    => 619,
                'lywx'     => 603,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '生命能够成长，因为我们愿意放下昨天；生命是那么的美好，因为我们拥有今天；生命充满希望，因为我们可以计划明天',
            ],
            'mw'      => [
                'title'    => '旷野吗哪',
                'day'      => '17',
                'index'    => 620,
                'lywx'     => 601,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '与你分享灵修材料，以亲切并深入关怀生活层面的讲解，为你传达明确清晰的圣经信息，帮助你应用于信仰生活上，好叫信徒珍爱灵修，灵命长进！',
            ],
            'be'      => [
                'title'    => '真道分解',
                'day'      => '17',
                'index'    => 621,
                'lywx'     => 701,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '透过经卷研读，陪伴你建立真理基础，栽培你灵命成长，帮助你活出信仰',
            ],
            'bs'      => [
                'title'    => '圣言盛宴',
                'day'      => '67',
                'index'    => 622,
                'lywx'     => 702,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '透过小组查经的方式与呈现，带领听众明白神的圣言，领受属灵的丰盛筵席',
            ],
            'cw'      => [
                'title'    => '齐来颂扬',
                'day'      => '15',
                'index'    => 623,
                'desc'     => '诗歌音乐:精选古今中外赞美圣诗，带领信徒齐来敬拜，同心颂扬万王之王，万主之主！',
                'lywx'     => 401,
                'author'   => '',
                'imageurl' => '',
                'category' => '诗歌音乐',
            ],
            'tg'      => [
                'title'    => '施恩座前',
                'day'      => '17',
                'index'    => 624,
                'lywx'     => 604,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '一起祷告、学习、分享、互动，培养属灵操练的习惯，建立同心同行的关系',
            ],
            'th'      => [
                'title'    => '真理之光',
                'day'      => '17',
                'index'    => 625,
                'lywx'     => 609,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '带领弟兄姊妹更深入认识圣经真理，让神的话语成为我们生命中随时的提醒、帮助、鼓励、安慰和引导，让我们喜乐的事奉神，荣耀神，为神做美好的见证！',
            ],
            'pb'      => [
                'title'    => '接棒人',
                'day'      => '15',
                'index'    => 626,
                'lywx'     => 1001,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '为教会领袖和事奉人员提供神学和教义训练，着重知识与灵命培育，并装备学员参与事奉与牧养，成为新一代的接棒人',
            ],
            'hw'      => [
                'title'    => '聆听主道',
                'day'      => '67',
                'index'    => 627,
                'lywx'     => 703,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '神所重用的仆人，为当代信徒讲解圣经真道，让我们一同安静下来，靠近主脚前，用心聆听主道',
            ],
            'aw'      => [
                'title'    => '空中崇拜',
                'day'      => '7',
                'index'    => 628,
                'lywx'     => 606,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '空中崇拜',
            ],
            'yp'      => [
                'title'    => '善牧良言',
                'day'      => '7',
                'index'    => 629,
                'lywx'     => 706,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '透过神所重用的牧者，结合他们多年的牧养、带领，以及丰富的人生历练，融入释经讲道的分享来建造听众，使我们因神的道而成长',
            ],
            'gsa'     => [
                'title'    => '好牧人',
                'day'      => '7',
                'index'    => 630,
                'lywx'     => 705,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '本节目邀请神的仆人分享，供应神的话语，满足你的灵命需求，使得生命更丰盛',
            ],
            'ba'      => [
                'title'    => '经动人心',
                'day'      => '7',
                'index'    => 631,
                'lywx'     => 704,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '基于圣经，加上创意，化作动人心弦的圣经广播剧。一起走进圣经人物的生命，体会他们的喜怒哀乐，认识救主耶稣基督，激励我们在生活中实践信仰',
            ],
            'bm'      => [
                'title'    => '佳美脚踪',
                'day'      => '6',
                'index'    => 632,
                'lywx'     => 608,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '佳美脚踪',
            ],
            'hd'      => [
                'title'    => '蓝天绿洲',
                'day'      => '67',
                'index'    => 633,
                'lywx'     => 801,
                'author'   => '',
                'imageurl' => '',
                'category' => '中英双语',
                'desc'     => '中英双语:想谋生？想人生？想生活？想生命？都可以一起来想。爱英语？爱思考？爱音乐？爱真理？也不妨共同去爱！',
            ],
            'sr'      => [
                'title'    => '给力人生',
                'day'      => '15',
                'index'    => 634,
                'lywx'     => 611,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '本节目为初信栽培而设，透过不同的课题，包括生命读经，家庭规划，自我探索，让信仰落实到生活的每个层面，为我们的人生给力',
            ],
            'bb'      => [
                'title'    => '生根建造',
                'day'      => '135',
                'index'    => 636,
                'lywx'     => 610,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '生根建造',
            ],
            'tu'      => [
                'title'    => '信仰百宝箱',
                'day'      => '135',
                'index'    => 637,
                'lywx'     => 612,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '借查考圣经和分享信徒生活专题，让信徒对信仰及生活有更明确的方向，面对生活的种种挑战时，能以圣经真理作行事为人的基础',
            ],
            'iv'      => [
                'title'    => '生活之光',
                'day'      => '67',
                'index'    => 638,
                'lywx'     => 708,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '本节目是史温道牧师（Charles Swindoll）30多年来之讲道选集。节目包含三个讲道系列：在恩典中觉醒、奏出原本婚姻的乐章和旧约圣经人物的奇妙故事。讲道以圣经真理为基础，并融汇历代基督徒作家、神学家和知名牧者的观点论据，配合生活化的例子和个人经历，深入浅出，历久弥新，对信徒与非信徒的生命必能有所造就。',
            ],
            'gl'      => [
                'title'    => '生命的福音',
                'day'      => '7',
                'index'    => 639,
                'desc'     => '生命的福音',
                'lywx'     => 501,
                'author'   => '',
                'imageurl' => '',
                'category' => '慕道探索',
            ],
            'mp'      => [
                'title'    => '这一刻清心',
                'day'      => '67',
                'index'    => 640,
                'desc'     => '这一刻，清心',
                'lywx'     => 605,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
            ],
            'ds'      => [
                'title'    => '晨曦讲座',
                'day'      => '17',
                'index'    => 646,
                'lywx'     => 1002,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '拓展视野，关心教会发展，按照真理共建神家，放眼世界禾场，努力实践晨曦异象',
            ],
            'vc'      => [
                'title'    => '良院专区', //vx良院精选
                'day'      => '7',
                'index'    => 647,
                'lywx'     => 1003,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '良院精选',
            ],
            'wa'      => [
                'title'    => '天路导向',
                'day'      => '7',
                'index'    => 648,
                'lywx'     => 802,
                'author'   => '',
                'imageurl' => '',
                'category' => '中英双语',
                'desc'     => '中英双语:以主题信息讲座的方式，配以诗歌、祷告、内容简介和扼要总结，让信息传达更清晰，使记忆更深刻。逐句英译中讲道方式，灵性造就的同时，英文水平也得到操练与提高',
            ],
            'cwa'     => [
                'title'    => '天路导向粤',
                'day'      => '7',
                'index'    => 649,
                'lywx'     => 803,
                'author'   => '',
                'imageurl' => '',
                'category' => '中英双语',
                'desc'     => '中英双语:天路导向（粤语）以主题信息讲座的方式，配以诗歌、祷告、内容简介和扼要总结，让信息传达更清晰，使记忆更深刻。逐句英译中讲道方式，灵性造就的同时，英文水平也得到操练与提高',
            ],
            'gt'      => [
                'title'    => '恩典与真理',
                'day'      => '7',
                'index'    => 650,
                'lywx'     => 901,
                'author'   => '',
                'imageurl' => '',
                'category' => '少数民族',
                'desc'     => '少数民族:恩典与真理（回民）《恩典与真理》，引导你走上正义之路。愿你得着真主给你的色俩麦提和瑞孜给，因沙安拉。',
            ],
            'ynf'     => [
                'title'    => '爱在人间',
                'day'      => '17',
                'index'    => 651,
                'lywx'     => 902,
                'author'   => '',
                'imageurl' => '',
                'category' => '少数民族',
                'desc'     => '少数民族:爱在人间（云南话）谈天说地、细数民俗、倾听民歌、谈论信仰 ... 汇集成为彩云之南的特色节目，把主耶稣的爱向各族人民传讲',
            ],
            'ls'      => [
                'title'    => '燃亮的一生',
                'day'      => '67',
                'index'    => 652,
                'lywx'     => 607,
                'author'   => '',
                'imageurl' => '',
                'category' => '生命成长',
                'desc'     => '与你分享宣教士的生平和服侍，让我们一同体会信仰的生命力，盼望你与我们一起多关心宣教，一同活出燃亮的生命！',
            ],
            'it'      => [
                'title'    => '与神同行', //tp 真理与柱石
                'day'      => '5',
                'index'    => 654,
                'lywx'     => 707,
                'author'   => '',
                'imageurl' => '',
                'category' => '学习真道',
                'desc'     => '透过经卷研读，陪伴你建立真理基础，栽培你灵命成长，帮助你活出信仰',
            ],
            'hdb'     => [
                'title'    => '蔚蓝心情',
                'day'      => '5', //????
                'index'    => 655,
                'lywx'     => 804,
                'author'   => '',
                'imageurl' => '',
                'category' => '中英双语',
                'desc'     => '透过经卷研读，陪伴你建立真理基础，栽培你灵命成长，帮助你活出信仰',
            ],
            'sg'      => [
                'title'    => '津津乐道',
                'day'      => '5', //????
                'index'    => 656,
                'lywx'     => 503,
                'author'   => '',
                'imageurl' => '',
                'category' => '慕道探索',
                'desc'     => '透过经卷研读，陪伴你建立真理基础，栽培你灵命成长，帮助你活出信仰',
            ],
            'ltsnp'   => [
                'title'    => '基础课程',
                'day'      => '7',
                'index'    => 641,
                'lywx'     => 1641,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '基础课程',
            ],
            'ltsdp1'  => [
                'title'    => '本科1',
                'day'      => '7',
                'index'    => 642,
                'lywx'     => 1642,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '本科文凭课程1',
            ],
            'ltsdp2'  => [
                'title'    => '本科2',
                'day'      => '7',
                'index'    => 643,
                'lywx'     => 1643,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '本科文凭课程2',
            ],
            'ltshdp1' => [
                'title'    => '进深1',
                'day'      => '7',
                'index'    => 644,
                'lywx'     => 1644,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '进深文凭课程1',
            ],
            'ltshdp2' => [
                'title'    => '进深2',
                'day'      => '7',
                'index'    => 645,
                'lywx'     => 1645,
                'author'   => '',
                'imageurl' => '',
                'category' => '栽培训练',
                'desc'     => '进深文凭课程2',
            ],
            'tm'      => [
                'title'    => '我们仨，还有你',
                'day'      => '67',
                'index'    => 653,
                'desc'     => '2016-10-30',
                'lywx'     => 108,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
            ],
            'bk'      => [
                'title'    => '听书‧想飞',
                'day'      => '7',
                'index'    => 635,
                'desc'     => '2016-10-30',
                'lywx'     => 109,
                'author'   => '',
                'imageurl' => '',
                'category' => '生活智慧',
            ],
        ];
    }
}
