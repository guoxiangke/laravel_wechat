<?php

use App\Http\Controllers\Api\LyMetaController;
use App\Models\LyMeta;
use Illuminate\Database\Seeder;

class LymetasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $metaUrl = 'https://m.729ly.net';
        $html = file_get_contents($metaUrl);
        $pq = \phpQuery::newDocumentHTML($html);

        $selector = 'div.w3-container.w3-border.progcat';
        $texts = $pq->find($selector);
        $oldLyMetas = LyMetaController::get_liangyou_audio_list();
        foreach ($texts as $text) {
            $categoryName = pq($text)->find('h4')->text(); //生活智慧
            //$categorySlug = pq($text)->attr('id'); //Wisdom
            $subTexts = pq($text)->find('div.w3-col.l2.m4.w3-margin-bottom');
            foreach ($subTexts as $subText) {
                $tmpData = [];
                $a = pq($subText)->find('a');
                $programName = $a->attr('title'); //绝妙当家
                $programCode = $a->attr('href'); //yu.html
                $programCode = str_replace('.html', '', $programCode);
                if (!$programCode) {
                    continue;
                }
                $programImg = $metaUrl.'/'.pq($a)->find('img')->attr('src'); //
                $ps = pq($subText)->find('p');
                $p1 = $ps->eq(0)->text();
                $p1 = str_replace($programName, '', $p1);
                $p1 = str_replace('　', '', $p1);
                $description = trim($p1);
                $p2 = trim($ps->eq(1)->text());
                $p2 = str_replace('主持：', '', $p2);
                $p2 = str_replace('　', '', $p2);
                $author = trim($p2);

                $tmpData['name'] = $programName;
                $tmpData['author'] = $author;
                $tmpData['description'] = $description;
                $tmpData['code'] = $programCode;
                $tmpData['category'] = array_search($categoryName, LyMeta::CATEGORY);
                $tmpData['image'] = $programImg;
                if (isset($oldLyMetas[$programCode])) {
                    $tmpData['day'] = $oldLyMetas[$programCode]['day'];
                    $tmpData['index'] = $oldLyMetas[$programCode]['index'];
                    $tmpData['ly_index'] = $oldLyMetas[$programCode]['lywx'];
                }
                LyMeta::updateOrCreate($tmpData);
            }
        }
    }
}
