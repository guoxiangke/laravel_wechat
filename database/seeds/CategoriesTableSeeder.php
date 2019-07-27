<?php

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        //Content
        $category = app('rinvex.categories.category');
        $c1 = $category->create(['name'=>'关爱婚姻','description'=>'婚恋相关','slug'=>'Family']);
        $c2 = $category->create(['name'=>'亲子教育','description'=>'教养孩童','slug'=>'Education']);
        $c3 = $category->create(['name'=>'生命成长','description'=>'生命与真理','slug'=>'Grow']);
        $c4 = $category->create(['name'=>'心灵驿站','description'=>'身心灵关怀（全人发展）','slug'=>'Care']);
        $c5 = $category->create(['name'=>'书香园地','description'=>'进步阶梯','slug'=>'Book']);
        $c6 = $category->create(['name'=>'彩虹之约','description'=>'同性恋辅导','slug'=>'Rainbow']);
        $c7 = $category->create(['name'=>'i看','description'=>'眼球有关','slug'=>'Video']);
        $c8 = $category->create(['name'=>'英语世界','description'=>'ABC','slug'=>'Abc']);

        $category->create(['name'=>'两性探秘','description'=>'婚恋相关','slug'=>'两性探秘','parent_id'=>1]);
        $category->create(['name'=>'婚前辅导','description'=>'婚恋相关','slug'=>'婚前辅导','parent_id'=>1]);
        $category->create(['name'=>'婚后辅导','description'=>'婚恋相关','slug'=>'婚后辅导','parent_id'=>1]);
        $category->create(['name'=>'唐老师信箱婚恋咨询','description'=>'婚恋相关','slug'=>'唐老师信箱婚恋咨询','parent_id'=>1]);

        $category->create(['name'=>'曼曼成长记','description'=>'教养孩童','slug'=>'曼曼成长记','parent_id'=>2]);
        $category->create(['name'=>'母子两地书','description'=>'教养孩童','slug'=>'母子两地书','parent_id'=>2]);

        $category->create(['name'=>'恩典365','description'=>'生命成长','slug'=>'恩典365','parent_id'=>3]);
        $category->create(['name'=>'每日亲近神','description'=>'生命成长','slug'=>'每日亲近神','parent_id'=>3]);
        $category->create(['name'=>'在天父怀中','description'=>'生命成长','slug'=>'在天父怀中','parent_id'=>3]);

        $category->create(['name'=>'健康生活','description'=>'心灵驿站','slug'=>'健康生活','parent_id'=>4]);
        $category->create(['name'=>'今夜心未眠','description'=>'心灵驿站','slug'=>'今夜心未眠','parent_id'=>4]);
        $category->create(['name'=>'绝妙听电影','description'=>'心灵驿站','slug'=>'绝妙听电影','parent_id'=>4]);

        $category->create(['name'=>'黑暗中的舞者','description'=>'书香园地','slug'=>'黑暗中的舞者','parent_id'=>5]);

        $category->create(['name'=>'孙叔唱副歌','description'=>'i看','slug'=>'孙叔唱副歌','parent_id'=>7]);
        $category->create(['name'=>'牛人看电影','description'=>'i看','slug'=>'牛人看电影','parent_id'=>7]);

        $category->create(['name'=>'活出彩虹','description'=>'彩虹之约','slug'=>'活出彩虹','parent_id'=>6]);
    }
}
