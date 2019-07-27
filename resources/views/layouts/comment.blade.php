@if (count($comments))
    <ul class="list-unstyled">
        @foreach ($comments as $comment)
            @php
                $user = $users->firstWhere('id', $comment->commented_id)->toArray();
                if(isset($user['profile']) && isset($user['profile']['nickname'])){
                    $userName =  $user['profile']['nickname'];
                    $headImgUrl = $user['profile']['headimgurl'];
                }else{
                    $userName = $user['name'];
                    $headImgUrl = asset('/storage/'.$user['avatar']);
                }
            //todo   | 回复  created_at

            @endphp
            <li class="media shadow" id="comment-{{ $comment->id }}">
                <img class="mr-3 avatar" src="{{$headImgUrl}}" alt="Generic placeholder image">
                <div class="media-body">
                    <h6 class="mt-0 mb-1">{{ $userName }} | {{ $comment->created_at }}</h6>
                    {{ $comment->comment }}
                    @foreach ($replys as $reply)
                        @php
                            $user = $users->firstWhere('id', $reply->commented_id)->toArray();
                            if(isset($user['profile']) && isset($user['profile']['nickname'])){
                            $userName =  $user['profile']['nickname'];
                            $headImgUrl = $user['profile']['headimgurl'];
                            }else{
                            $userName = $user['name'];
                            $headImgUrl = 'https://i.loli.net/2017/08/21/599a521472424.jpg';
                            }
                        @endphp
                        @if ($reply->commentable_id == $comment->id)
                            <div class="media mt-3 shadow  bg-white rounded reply" id="reply-{{ $reply->id }}">
                                <img class="mr-3 avatar" src="{{$headImgUrl}}" alt="Generic placeholder image">
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">{{ $userName }} | {{ $reply->created_at }} </h6>
                                    {{ $reply->comment }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </li>
        @endforeach
    </ul>
@else

   <div class="empty-block">
    <p>暂无评论,快来抢沙发啊! ~_~</p>
    <h6>评论方式:回复代码给(公众号永不止息[云彩助手])获取当日节目后,直接回复您的文字内容发送即可评论!</h6>
   </div>
@endif
