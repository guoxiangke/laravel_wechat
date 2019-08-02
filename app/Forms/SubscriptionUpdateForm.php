<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class SubscriptionUpdateForm extends Form
{
    public function buildForm()
    {
        $this->add('_method', 'hidden', [
          'default_value' => 'PUT',
      ]);
        $validTime = [];
        for ($i = 5; $i < 24; $i++) {
            if (in_array($i, [6, 12, 18])) {
                $validTime[$i] = $i.':00';
            } else {
                $validTime[$i] = $i.':00 (会员尊享)';
            }
        }

        $this->add('send_at', 'select', [
          'label'          => '定时推送',
          'choices'        => $validTime,
          'selected'       => $this->getData('send_at'),
          'rules'          => 'required|integer|min:5|max:22',
          'error_messages' => [
              'send_at.integer' => '时间不对哦!',
              'send_at.min'     => '不要起太早哦!',
              'send_at.max'     => '睡的太晚不好哦!',
          ],
      ]);

        $this->add('info', 'static', [
      'label' => '⚠️提示:',
      'tag'   => 'div', // Tag to be used for holding static data,
      'attr'  => ['class' => 'form-control-static'], // This is the default
      'value' => '以下为会员专享功能', // If nothing is passed, data is pulled from model if any
  ]);
        $weekdays = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        $this->add('subscribe_rrule', 'choice', [
          'attr'     => ['disabled' => true],
          'label'    => '推送周期:(会员尊享)',
          'choices'  => $weekdays,
          'multiple' => true,
          'expanded' => true,
          'selected' => [0, 1, 2, 3, 4, 5, 6],
      ]);
        $this->add('push_type', 'select', [
        // 'attr' => ['disabled' => true],
        'label'          => '推送类型:(会员尊享)',
        'choices'        => ['音频', '音视频图文'],
        'choice_options' => [
            'wrapper'    => ['class' => 'choice-wrapper'],
            'label_attr' => ['class' => 'label-class'],
        ],
        'selected' => [0],
        'expanded' => true,
        'multiple' => false,
      ]);
        $this->add('submit', 'submit', [
        'label' => '点击更新',
        'attr'  => ['class' => 'btn btn-outline-primary btn-block'],
      ]);
    }
}
