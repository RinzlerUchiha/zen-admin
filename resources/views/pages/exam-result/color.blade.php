<ul class="nav nav-tabs" id="colorTab" role="tablist">
    @foreach ($colorResult as $i => $item)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->index == 0 ? 'active' : '' }}" id="item-{{ $i }}-tab" data-bs-toggle="tab" data-bs-target="#item-{{ $i }}-tab-pane"
                type="button" role="tab" aria-controls="item-{{ $i }}-tab-pane" aria-selected="{{ $loop->index == 0 ? 'true' : 'false' }}" style="color: {{ strtolower($item[0]) }};">{{ $item[0] }}</button>
        </li>
    @endforeach
</ul>
<div class="tab-content" id="colorTabContent">
    @if ($colorResult->keys()->contains(1))
        <div class="tab-pane fade {{ $colorResult->keys()->first() == 1 ? 'show active' : ''}}" id="item-1-tab-pane" role="tabpanel" aria-labelledby="item-1-tab" tabindex="0">
            <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:10.0pt; color: blue;">BLUE (Controller)</span></strong></span></span></p>

            <p><span style="font-size:10.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Blue is the color of the sky and the ocean. It also serves as the color of authority. Explorers have long been the pioneers of then land, the ocean and in space and their characteristics match this style. They enjoy looking at the &ldquo;big picture&rdquo;, being in charge, and are comfortable taking appropriate risk for themselves and their group. They are goal-oriented people and love to have their fingers in many pies. They are generally motivated by challenge and like competition. People of other &ldquo;styles&rdquo; get frustrated with these &ldquo;blues&rdquo; because they see them sometimes as impatient and abrupt people, selective listeners, but they appreciate the strong leadership qualities that they display. </span></span></p>
        </div>
    @endif

    @if ($colorResult->keys()->contains(2))
        <div class="tab-pane fade {{ $colorResult->keys()->first() == 2 ? 'show active' : ''}}" id="item-2-tab-pane" role="tabpanel" aria-labelledby="item-2-tab" tabindex="0">
            <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:10.0pt; color: green;">GREEN (Analyst)</span></strong></span></span></p>

            <p style="margin-left:0in; margin-right:0in"><span style="font-size:10.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Green is the color of money and was one of the original colors of the computer screen. Of all the types, &ldquo;greens&rdquo; are the most comfortable where accuracy and numbers are important. Precision is inherent in their style. &ldquo;If a job is worth doing, it is worth doing right, the first time&rdquo; might be their motto. They are willing to take the time to get the job done right. They are the best of the four styles at critical thinking and planning. They make the best administrators as they like order, structure, following guidelines and plans (especially if they initiate them). Other styles complain that greens are too rigid, too slow at making decisions too &ldquo;picky&rdquo; but value their planning and problem solving skills. </span></span></p>
        </div>
    @endif

    @if ($colorResult->keys()->contains(3))
        <div class="tab-pane fade {{ $colorResult->keys()->first() == 3 ? 'show active' : ''}}" id="item-3-tab-pane" role="tabpanel" aria-labelledby="item-3-tab" tabindex="0">
            <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:10.0pt; color: red;">RED (Promoter)</span></strong></span></span></p>

            <p><span style="font-size:10.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Red is the color of Valentines and tends to connote passion and enthusiasm, which sounds a lot like the &ldquo;reds&rdquo;. Reds are happiest when they are influencing or entertaining other people. Like the blues, they are comfortable taking risks and enjoy trying new things. They get bored if they have to do the same old thing all the time. They are charming, playful, spontaneous, talkative types who are energized by being the center of attention. They are motivated by recognition. They want to be liked. Other styles see them as unfocused, as they go along with everything, but appreciate their talents as great promoters who can sell anything.</span></span></p>
        </div>
    @endif

    @if ($colorResult->keys()->contains(4))
        <div class="tab-pane fade {{ $colorResult->keys()->first() == 4 ? 'show active' : ''}}" id="item-4-tab-pane" role="tabpanel" aria-labelledby="item-4-tab" tabindex="0">
            <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:10.0pt; color: yellow;">YELLOW (Supporter)</span></strong></span></span></p>

            <p><span style="font-size:10.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Yellow is the color of the sun and &ldquo;yellows&rdquo; are like a ray of sunshine when they enter a room with their warm and caring style. Family is their number one priority. They tend to be more concerned with the needs of others. They are the best team builders, always listening to, encouraging and bringing out the best in others. They are motivated by appreciation or work done and have a strong need to please others. Like the &ldquo;greens&rdquo;, they dislike confrontation and will give into others to avoid conflict. Other styles see &ldquo;yellows&rdquo; as too soft, not hard-nosed enough, indecisive (they can see all sides of an issue) and resistant to change. They are often the &ldquo;glue&rdquo; that holds a group together. </span></span></p>
        </div>
    @endif
</div>