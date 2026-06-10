@if($type == 'gov')
    
    @foreach($posts as $post)
        <div class="card ann-post mb-3" data-id="{{ $post->gov_id }}">
            <div class="card-body">
                <div class="float-end">
                    <button class="btn btn-sm btn-danger" onclick="remove_post('{{ $post->gov_id }}')"><i class="fa fa-times"></i></button>
                </div>
                <div class="d-flex">
                    @if($post->pic)
                        <img src="{{ config('app.url') }}/file/get/emp-img/{{ $post->pic }}" class="img-thumbnail rounded-circle ann-post-usr-img" alt="{{ $post->gov_postby }}">
                    @else
                        <div class="img-fluid rounded-circle ann-post-usr-img placeholder"></div>
                    @endif
                    
                    <div class="d-block mb-3 ms-1">
                        <strong class="d-block post-user-name">{{ $post->empinfo ? trim(ucwords($post->empinfo->pers_firstname." ".$post->empinfo->pers_lastname)) : '' }}</strong>
                        <span class="text-muted fw-light post-date d-block lh-sm">{{ date('F d, Y', strtotime($post->gov_timestamp)) }}</span>
                        <span class="text-muted fw-light post-date d-block lh-sm">{{ date('h:i A', strtotime($post->gov_timestamp)) }}</span>
                    </div>
                </div>
                <p class="lh-sm mb-3">{!! nl2br($post->gov_title) !!}</p>
                <div class="row row-cols-3 justify-content-center mx-0 mb-1 ann-content-image">
                    @foreach($post->gov_img as $img)
                        <div class="col g-1 flex-fill">
                            <img src="{{ config('app.url') }}/file/get/announcement/{{ $img }}" class="img-fluid w-auto" alt="{{ $img }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

@else

    @foreach($posts as $post)
        <div class="card ann-post mb-3" data-id="{{ $post->ann_id }}">
            <div class="card-body">
                @if($post->ann_status == 'Reported')
                    <div class="alert alert-danger ann-report" role="alert">
                        <div class="d-flex">
                            <h4 class="alert-heading">Reported</h4>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" title="Deny removal" onclick="deny_report('{{ $post->ann_id }}')"><i class="fa fa-times"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary" title="Approve removal" onclick="approve_report('{{ $post->ann_id }}')"><i class="fa fa-check"></i></button>
                        </div>
                        <div class="d-flex">
                            @if($post->reportby_pic)
                                <img src="{{ config('app.url') }}/file/get/emp-img/{{ $post->reportby_pic }}" class="img-thumbnail rounded-circle ann-post-usr-img" alt="{{ $post->ann_reportby }}">
                            @else
                                <div class="img-fluid rounded-circle ann-post-usr-img placeholder"></div>
                            @endif

                            <div class="d-block mb-3 ms-1">
                                <strong class="d-block post-user-name">{{ $post->reportby_info ? trim(ucwords($post->reportby_info->pers_firstname." ".$post->reportby_info->pers_lastname)) : '' }}</strong>
                                <p class="card-text fw-light post-date d-block lh-sm">{!! $post->ann_report_reason !!}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="float-end">
                        <button class="btn btn-sm btn-danger" onclick="remove_post('{{ $post->ann_id }}')"><i class="fa fa-times"></i></button>
                    </div>
                @endif
                <div class="d-flex">
                    @if($post->pic)
                        <img src="{{ config('app.url') }}/file/get/emp-img/{{ $post->pic }}" class="img-thumbnail rounded-circle ann-post-usr-img" alt="{{ $post->ann_approvedby }}">
                    @else
                        <div class="img-fluid rounded-circle ann-post-usr-img placeholder"></div>
                    @endif
                    
                    <div class="d-block mb-3 ms-1">
                        <strong class="d-block post-user-name">{{ $post->empinfo ? trim(ucwords($post->empinfo->pers_firstname." ".$post->empinfo->pers_lastname)) : '' }}</strong>
                        <span class="text-muted fw-light post-date d-block lh-sm">{{ date('F d, Y', strtotime($post->ann_timestatmp)) }}</span>
                        <span class="text-muted fw-light post-date d-block lh-sm">{{ date('h:i A', strtotime($post->ann_timestatmp)) }}</span>
                    </div>
                </div>
                <p class="lh-sm mb-3">{!! nl2br($post->ann_title) !!}</p>
                <div class="row row-cols-3 justify-content-center mx-0 mb-1 ann-content-image">
                    @foreach($post->ann_content as $img)
                        <div class="col g-1 flex-fill text-center">
                            <img src="{{ config('app.url') }}/file/get/announcement/{{ $img }}" class="img-fluid w-auto" alt="{{ $img }}">
                        </div>
                    @endforeach
                </div>
                <div class="d-flex">
                    <div class="reaction-container">
                        <button type="button" id="react-button-{{ $post->ann_id }}" class="btn btn-light btn-sm p-0 reaction-trigger" data-reaction="{{ $post->reaction_type }}">
                            {{-- @if ($post->reaction_type == 'heart')
                                <img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'like')
                                <img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'love')
                                <img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'eey')
                                <img src="https://i.pinimg.com/564x/9d/04/2c/9d042cb030e250961454adf7131f76b5.jpg" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'cry')
                                <img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'haha')
                                <img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'wow')
                                <img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle">
                            @elseif ($post->reaction_type == 'angry')
                                <img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle">
                            @else
                                <i class="ti ti-face-smile"></i>
                            @endif --}}
                            <i class="ti ti-face-smile"></i>
                        </button>

                        <div class="reaction-options">
                            <div name="reaction" class="reaction" data-reaction="like"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/like.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="eey"><img width="50" height="60" src="https://i.pinimg.com/originals/58/91/52/58915204d17860c24d4c02be7425a830.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="heart"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/heart.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="love"><img class="img" width="50" height="50" src="https://media1.tenor.com/m/63nE7vC84pIAAAAd/care-discord.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="cry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/sad.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="haha"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/haha.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="wow"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/woow.gif"></div>
                            <div name="reaction" class="reaction" data-reaction="angry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/angry.gif"></div>
                        </div>
                    </div>
                    <div class="reaction-list ms-2" style="padding-left: 10px;">
                        <img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle" data-reaction="heart" style="{{ $post->reactionList?->doesntContain('reaction_type', 'heart') ? 'display: none;' : '' }}z-index: 8; margin-left: -10px; position: relative;">

                        <img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle" data-reaction="like" style="{{ $post->reactionList?->doesntContain('reaction_type', 'like') ? 'display: none;' : '' }}z-index: 7; margin-left: -10px; position: relative;">

                        <img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle" data-reaction="love" style="{{ $post->reactionList?->doesntContain('reaction_type', 'love') ? 'display: none;' : '' }}z-index: 6; margin-left: -10px; position: relative;">

                        <img src="https://i.pinimg.com/564x/9d/04/2c/9d042cb030e250961454adf7131f76b5.jpg" class="img-fluid" data-reaction="eey" style="{{ $post->reactionList?->doesntContain('reaction_type', 'eey') ? 'display: none;' : '' }}z-index: 5; margin-left: -10px; position: relative;">

                        <img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle" data-reaction="cry" style="{{ $post->reactionList?->doesntContain('reaction_type', 'cry') ? 'display: none;' : '' }}z-index: 4; margin-left: -10px; position: relative;">

                        <img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle" data-reaction="haha" style="{{ $post->reactionList?->doesntContain('reaction_type', 'haha') ? 'display: none;' : '' }}z-index: 3; margin-left: -10px; position: relative;">

                        <img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle" data-reaction="wow" style="{{ $post->reactionList?->doesntContain('reaction_type', 'wow') ? 'display: none;' : '' }}z-index: 2; margin-left: -10px; position: relative;">

                        <img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle" data-reaction="angry" style="{{ $post->reactionList?->doesntContain('reaction_type', 'angry') ? 'display: none;' : '' }}z-index: 1; margin-left: -10px; position: relative;">
                    </div>
                    <span class="list-counter">{{ $post->reactionList?->count() }}</span>
                    @if($comments->where('com_post_id', $post->ann_id)->count() > 0)
                        <button class="ms-auto btn btn-sm btn-light comment-count">
                            {{ $comments->where('com_post_id', $post->ann_id)->count() }}
                            <i class="fa-regular fa-message"></i>
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body border-top py-1 comment-list">
                @foreach($comments->where('com_post_id', $post->ann_id) as $comment)
                    <div class="comment-list-item d-flex {{ $loop->remaining - 2 >= 0 ? 'd-none' : '' }}">
                        @if($comment->pic)
                            <img src="{{ config('app.url') }}/file/get/emp-img/{{ $comment->pic }}" class="img-thumbnail rounded-circle ann-comment-usr-img" alt="{{ $comment->com_post_by }}">
                        @else
                            <div class="img-fluid rounded-circle ann-comment-usr-img placeholder"></div>
                        @endif
                        <div class="d-block ms-1">
                            <strong class="d-block post-user-name">{{ $comment->empinfo ? trim(ucwords($comment->empinfo->pers_firstname." ".$comment->empinfo->pers_lastname)) : '' }}</strong>
                            <p class="card-text m-0">{{ $comment->com_content }}</p>
                            <span class="text-muted fw-light post-date d-block lh-sm">{{ $comment->age_string }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="card-body pt-1 comment-area">
                <div class="d-flex">
                    @if($post->pic)
                        <img src="{{ config('app.url') }}/file/get/emp-img/{{ $post->pic }}" class="img-thumbnail rounded-circle ann-comment-usr-img" alt="{{ $post->ann_approvedby }}">
                    @else
                        <div class="img-fluid rounded-circle ann-comment-usr-img placeholder"></div>
                    @endif
                    <div class="input-group input-group-sm mb-1 ms-1 comment-input">
                        <input type="text" class="comment-content form-control form-control-sm lh-sm rounded-start-5 border-end-0 border-secondary-subtle" placeholder="Write a comment...">
                        <button type="button" class="btn btn-sm lh-sm border-top border-bottom border-start-0 border-end-0 border-secondary-subtle btn-emoji-list"><i class="fa-regular fa-face-smile"></i></button>
                        <button type="button" class="btn-send-comment btn btn-sm lh-sm rounded-end-5 border-start-0 border border-secondary-subtle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16" style="transform: rotate(45deg); transition: transform 0.2s ease;">
                                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="emoji-list" style="display: none;">
                    <ul class="nav nav-underline" id="emojiTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="face-tab" data-bs-toggle="tab" data-bs-target="#face-tab-pane" type="button" role="tab" aria-controls="face-tab-pane" aria-selected="true">&#128578;</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="heart-tab" data-bs-toggle="tab" data-bs-target="#heart-tab-pane" type="button" role="tab" aria-controls="heart-tab-pane" aria-selected="false">&#129293;</button>
                            {{-- &#129293; or &#x1F90D; --}}
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="food-tab" data-bs-toggle="tab" data-bs-target="#food-tab-pane" type="button" role="tab" aria-controls="food-tab-pane" aria-selected="false">&#127860;</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="plant-tab" data-bs-toggle="tab" data-bs-target="#plant-tab-pane" type="button" role="tab" aria-controls="plant-tab-pane" aria-selected="false">&#127808;</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="weather-tab" data-bs-toggle="tab" data-bs-target="#weather-tab-pane" type="button" role="tab" aria-controls="weather-tab-pane" aria-selected="false">&#127759;</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="symbols-tab" data-bs-toggle="tab" data-bs-target="#symbols-tab-pane" type="button" role="tab" aria-controls="symbols-tab-pane" aria-selected="false">&#127881;</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-1" id="emojiTabContent">
                        <div class="tab-pane fade show active" id="face-tab-pane" role="tabpanel" aria-labelledby="face-tab" tabindex="0">
                            @foreach($emoji['faces'] as $v)
                                <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                            @endforeach
                        </div>
                        <div class="tab-pane fade" id="heart-tab-pane" role="tabpanel" aria-labelledby="heart-tab" tabindex="0">
                            @foreach($emoji['heart'] as $v)
                                <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                            @endforeach
                        </div>
                        <div class="tab-pane fade" id="food-tab-pane" role="tabpanel" aria-labelledby="food-tab" tabindex="0">
                            @foreach($emoji['food'] as $v)
                                <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                            @endforeach
                        </div>
                        <div class="tab-pane fade" id="plant-tab-pane" role="tabpanel" aria-labelledby="plant-tab" tabindex="0">
                            @foreach($emoji['plant'] as $v)
                                <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                            @endforeach
                        </div>
                        <div class="tab-pane fade" id="weather-tab-pane" role="tabpanel" aria-labelledby="weather-tab" tabindex="0">
                            @foreach($emoji['weather'] as $v)
                                <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                            @endforeach
                        </div>
                        <div class="tab-pane fade" id="symbols-tab-pane" role="tabpanel" aria-labelledby="symbols-tab" tabindex="0">
                            @foreach($emoji['symbols'] as $v)
                                <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endif