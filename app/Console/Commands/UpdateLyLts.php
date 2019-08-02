<?php

namespace App\Console\Commands;

use App\Models\LyLts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateLyLts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lylts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动更新lts from Api';

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
        $apiJson = file_get_contents('https://wechat.edu.pl/lyapi/lts');
        $apiData = json_decode($apiJson, 1);
        $imageLinks = [
            'http://www.729ly.net/Template/Shared/images/programs/vc_prog_banner.png',
            'http://www.729ly.net/Template/Shared/images/programs/lts_np_prog_banner.png',
            'http://www.729ly.net/Template/Shared/images/programs/lts_dp_prog_banner.png',
            'http://www.729ly.net/Template/Shared/images/programs/lts_hdp_prog_banner.png',
        ];
        if (!count($apiData)) {
            Log::error(__FILE__, [__FUNCTION__, __LINE__, $apiData, 'wechat.edu.pl error']);

            return false;
        }
        foreach ($apiData as $data) {
            $code = $data['code'];
            $ltsModel = LyLts::where('code', $code)->first();

            $data['category'] = array_search($data['category'], LyLts::CATEGORY);
            $data['image'] = $imageLinks[$data['category']];
            if ($ltsModel) {
                foreach ($data as $key => $value) {
                    $ltsModel->{$key} = $value;
                }
                $ltsModel->save();
            } else {
                LyLts::updateOrCreate($data);
            }
        }
    }
}
