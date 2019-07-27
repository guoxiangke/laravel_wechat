<?php

namespace App\Console\Commands;

use App\Models\LyMeta;
use Illuminate\Console\Command;

class UpdateLyMeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lymeta:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update lymetas from txly2.net';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $lyMetas = LyMeta::all();
        foreach ($lyMetas as $lyMeta) {
            $apiJson = file_get_contents('https://api.yongbuzhixi.com/lyapi/json');
            $apiData = json_decode($apiJson, 1);
            if (in_array($lyMeta->code, array_keys($apiData))) {
                $lyMeta->day = $apiData[$lyMeta->code]['day'];
                $lyMeta->index = $apiData[$lyMeta->code]['index'];
                $lyMeta->ly_index = $apiData[$lyMeta->code]['ly_index'];
                $lyMeta->save();
            }

            $categoryId = array_search('课程训练', LyMeta::CATEGORY);
            if ($lyMeta->code == 'ltsdp') {
                $lyMeta->update(
                    [
                        'code'     => 'ltsdp1',
                        'index'    => 642,
                        'ly_index' => 1642,
                        'name'     => '良院本科1',
                        'day'      => 7,
                        'category' => $categoryId,
                    ]
                );

                $ltsdp = $lyMeta->toArray();
                $ltsdp['code'] = 'ltsdp2';
                $ltsdp['index'] = '643';
                $ltsdp['ly_index'] = '1643';
                $ltsdp['name'] = '良院本科2';
                LyMeta::updateOrCreate($ltsdp);
            }

            if ($lyMeta->code == 'ltshdp') {
                $lyMeta->update(
                    [
                        'code'     => 'ltshdp1',
                        'index'    => 644,
                        'ly_index' => 1644,
                        'name'     => '良院进深1',
                        'day'      => 7,
                        'category' => $categoryId,
                    ]
                );

                $ltsdp = $lyMeta->toArray();
                $ltsdp['code'] = 'ltshdp2';
                $ltsdp['index'] = '645';
                $ltsdp['ly_index'] = '1645';
                $ltsdp['name'] = '良院进深2';
                LyMeta::updateOrCreate($ltsdp);
            }
        }

        return 'update done!';
    }
}
