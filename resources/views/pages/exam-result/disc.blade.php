<ul class="nav nav-tabs" id="discTab" role="tablist">
    @foreach ($discResult as $item => $score)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->index == 0 ? 'active' : '' }}" id="item-{{ $item }}-tab" data-bs-toggle="tab" data-bs-target="#item-{{ $item }}-tab-pane"
                type="button" role="tab" aria-controls="item-{{ $item }}-tab-pane" aria-selected="{{ $loop->index == 0 ? 'true' : 'false' }}">{{ $item }}</button>
        </li>
    @endforeach
</ul>
<div class="tab-content" id="discTabContent">
    @if ($discResult->keys()->contains('D'))
        <div class="tab-pane fade {{ $discResult->keys()->first() == 'D' ? 'show active' : ''}}" id="item-D-tab-pane" role="tabpanel" aria-labelledby="item-D-tab" tabindex="0">
            <div class="container-fluid">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="vertical-align: middle;"><img src="{{ route('applicant.file', ['src' => 'disc-asset', 'filename' => 'd-img.png']) }}" class="img-fluid"></td>
                            <td style="vertical-align: middle;">
                                <label>Dominance</label>
                                <p>Person places emphasis on accomplishing results, the bottom line, confidence</p>
                            </td>
                            <td style="vertical-align: middle;">
                                <label>Behaviors</label>
                                <p>- Sees the big picture</p>
                                <p>- Can be blunt</p>
                                <p>- Accepts challenges</p>
                                <p>- Gets straight to the point</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h2 style="margin-left:0in; margin-right:0in"><span style="font-size:18pt"><span style="background-color:white"><span style="font-family:'Times New Roman',serif"><strong><span style="font-size:13.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">D is for Dominance</span></span></span><br />
                                    <span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">People with the D style place an&nbsp;</span></span></span><strong><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">emphasis on shaping the environment by overcoming opposition to accomplish results.</span></span></span></strong><br />
                                    <br />
                                    <span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">A person with a D style</span></span></span></strong></span></span></span></h2>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">is motivated by winning, competition and success.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">prioritizes accepting challenge, taking action and achieving immediate results.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">is described as direct, demanding, forceful, strong willed, driven, and determined, fast-paced, and self-confident.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">may be limited by lack of concern for others, impatience and open skepticism.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">may fear being seen as vulnerable or being taken advantage of.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">values competency, action, concrete results, personal freedom, challenges.</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:'Times New Roman',serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">Goals:</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">unique accomplishments</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">new opportunities</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">control of audience</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">independence</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="background-color:white"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">Will need to expend more energy to:</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">show patience</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">display sensitivity</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">get into the details</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">allow deliberation</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:'Times New Roman',serif"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">When communicating with the D style individuals, give them the bottom line, be brief, focus your discussion narrowly, avoid making generalizations, refrain from repeating yourself, and focus on solutions rather than problems.<br />
                                            <br />
                                            <strong><span style="font-family:Arial, sans-serif">DiSC Classic Patterns</span></strong>: Developer, Results Orientated, Inspirational and Creative<br />
                                            <br />
                                            <strong><span style="font-family:Arial, sans-serif">Leadership styles</span></strong>:&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2014/02/commanding-leaders-everything-disc/" style="color:blue; text-decoration:underline"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#0099d8">Commanding</span></span></span></a><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">,&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2014/01/resolute-leaders-everything-disc/" style="color:blue; text-decoration:underline"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#0099d8">Resolute</span></span></span></a><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#424c4a">,&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2013/06/pioneering-leaders-and-everything-disc/" style="color:blue; text-decoration:underline"><span style="font-size:10.5pt"><span style="font-family:Arial, sans-serif"><span style="color:#0099d8">Pioneering</span></span></span></a></span></span></span></p>
            </div>
        </div>
    @endif

    @if ($discResult->keys()->contains('I'))
        <div class="tab-pane fade {{ $discResult->keys()->first() == 'I' ? 'show active' : ''}}" id="item-I-tab-pane" role="tabpanel" aria-labelledby="item-I-tab" tabindex="0">
            <div class="container-fluid">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="vertical-align: middle;"><img src="{{ route('applicant.file', ['src' => 'disc-asset', 'filename' => 'i-img.png']) }}" class="img-fluid"></td>
                            <td style="vertical-align: middle;">
                                <label>Influence</label>
                                <p>Person places emphasis on influencing or persuading others, openness, relationships</p>
                            </td>
                            <td style="vertical-align: middle;">
                                <label>Behaviors</label>
                                <p>- Shows enthusiasm</p>
                                <p>- Is optimistic</p>
                                <p>- Likes to collaborate</p>
                                <p>- Dislikes being ignored</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:13.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">I is for Influence</span></span></span></strong><br />
                                <span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">People with the i style place an&nbsp;<strong>emphasis on shaping the environment by influencing or persuading others.</strong><br />
                                            <br />
                                            A person with an i style</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">may be limited by being impulsive and disorganized and having lack of follow-through</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">is described as convincing, magnetic, enthusiastic, warm, trusting and optimistic</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">prioritizes taking action, collaboration, and expressing enthusiasm</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">is motivated by social recognition, group activities, and relationships</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">may fear loss of influence, disapproval and being ignored</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">values coaching and counseling, freedom of expression and democratic relationships</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">Goals</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">victory with flair</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">friendship and happiness</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">authority and prestige status symbols</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">popularity</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="background-color:#fffcf8"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">Will need to expend more energy to:</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">follow-through completely</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">research all the facts</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">speak directly and candidly</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">stay focused for long periods</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">When communicating with the i style individual, share your experiences, allow the i style person time to ask questions and talk themselves, focus on the positives, avoid overloading them with details, and don&#39;t interrupt them.<br />
                                            <br />
                                            <strong>DiSC Classic Patterns:&nbsp;</strong>Promoter, Persuader, Counselor, Appraiser<br />
                                            <br />
                                            <strong>Leadership styles:</strong>&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2013/07/energizing-leaders-and-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Energizing</span></span></span></a><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">,&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2013/06/pioneering-leaders-and-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Pioneering</span></span></span></a><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">,&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2013/08/affirming-leaders-and-eveything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Affirming</span></span></span></a></span></span></span></p>
            </div>
        </div>
    @endif

    @if ($discResult->keys()->contains('S'))
        <div class="tab-pane fade {{ $discResult->keys()->first() == 'S' ? 'show active' : ''}}" id="item-S-tab-pane" role="tabpanel" aria-labelledby="item-S-tab" tabindex="0">
            <div class="container-fluid">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="vertical-align: middle;"><img src="{{ route('applicant.file', ['src' => 'disc-asset', 'filename' => 's-img.png']) }}" class="img-fluid"></td>
                            <td style="vertical-align: middle;">
                                <label>Steadiness</label>
                                <p>Person places emphasis on cooperation, sincerity, dependability</p>
                            </td>
                            <td style="vertical-align: middle;">
                                <label>Behaviors</label>
                                <p>- Doesn't like to be rushed</p>
                                <p>- Calm manner</p>
                                <p>- Calm approach</p>
                                <p>- Supportive actions</p>
                                <p>- Humility</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:13.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">S is for Steadiness</span></span></span></strong></span></span></span></p>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">People with the S style place an<strong>&nbsp;emphasis on cooperating with others within existing circumstances to carry out the task.</strong>.</span></span></span></span></span></span></p>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">A person with an S style</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">is motivated by cooperation, opportunities to help and sincere appreciation</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">prioritizes giving support, collaboration and maintaining stability</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">is described as calm, patient, predictable, deliberate, stable and consistent.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">may be limited by being indecisive, overly accommodating and tendency to avoid change</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">may fear change, loss of stability and offending others.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">values loyalty, helping others and security</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">Goals:</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">personal accomplishments</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">group acceptance</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">power through formal roles and positions of authority</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">maintenance of status quo and controlled environment<br />
                                                <strong>Will need to expend more energy to:</strong></span></span></span></span></span></span></li>
                </ul>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">quickly adapt to change or unclear expectations</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">multitask</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">promote themselves</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">confront others</span></span></span></span></span></span></li>
                </ul>

                <p><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">When communicating with the S style individuals, be personal and amiable, express your interest in them and what you expect from them, take time to provide clarification, be polite, and avoid being confrontational, overly aggressive or rude.<br />
                                <br />
                                <strong>DiSC Classic Patterns</strong>: Specialist, Achiever, Agent, Investigator<br />
                                <br />
                                <strong>Leadership styles:</strong>&nbsp;</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;"><a href="http://www.discprofiles.com/blog/2013/09/inclusive-leaders-and-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Inclusive</span></span></span></a></span></span><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">,&nbsp;</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;"><a href="http://www.discprofiles.com/blog/2013/10/humble-leaders-and-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Humble</span></span></span></a></span></span><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">,&nbsp;</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;"><a href="http://www.discprofiles.com/blog/2013/08/affirming-leaders-and-eveything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Affirming</span></span></span></a></span></span></p>
            </div>
        </div>
    @endif

    @if ($discResult->keys()->contains('C'))
        <div class="tab-pane fade {{ $discResult->keys()->first() == 'C' ? 'show active' : ''}}" id="item-C-tab-pane" role="tabpanel" aria-labelledby="item-C-tab" tabindex="0">
            <div class="container-fluid">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="vertical-align: middle;"><img src="{{ route('applicant.file', ['src' => 'disc-asset', 'filename' => 'c-img.png']) }}" class="img-fluid"></td>
                            <td style="vertical-align: middle;">
                                <label>Conscientiousness</label>
                                <p>Person places emphasis on quality and accuracy, expertise, competency</p>
                            </td>
                            <td style="vertical-align: middle;">
                                <label>Behaviors</label>
                                <p>- Enjoys independence</p>
                                <p>- Objective reasoning</p>
                                <p>- Wants the details</p>
                                <p>- Fears being wrong</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:13.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">C is for Conscientiousness</span></span></span></strong></span></span></span></p>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">People with the C style place an<strong>&nbsp;emphasis on working conscientiously within existing circumstances to ensure quality and accuracy</strong>.<br />
                                            <br />
                                            A person with a C style</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">is motivated by opportunities to gain knowledge, showing their expertise, and quality work.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">prioritizes ensuring accuracy, maintaining stability, and challenging assumptions.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">is described as careful, cautious, systematic, diplomatic, accurate and tactful.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">may be limited by being overcritical, overanalyzing and isolating themselves.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">may fear criticism and being wrong.</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">values quality and accuracy</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">Goals:</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">unique accomplishments</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">correctness</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">stability</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">predictable accomplishments</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">personal growth</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">Will need to expend more energy to:</span></span></span></span></span></span></p>

                <ul>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">let go of and delegate tasks</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">compromise for the good of the team</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">join in social events and celebrations</span></span></span></span></span></span></li>
                    <li><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">make quick decisions</span></span></span></span></span></span></li>
                </ul>

                <p style="margin-left:0in; margin-right:0in"><span style="font-size:11pt"><span style="background-color:white"><span style="font-family:Calibri,sans-serif"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">When communicating with the C style individual, focus on facts and details; minimize &quot;pep talk&quot; or emotional language; be patient, persistent and diplomatic.<br />
                                            <strong>DiSC Classic Patterns:</strong>&nbsp;Objective Thinker, Perfectionist, Practitioner<br />
                                            <br />
                                            <strong>Leadership styles:</strong>&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2013/11/deliberate-leaders-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Deliberate</span></span></span></a><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">,&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2013/10/humble-leaders-and-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Humble</span></span></span></a><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#424c4a">,&nbsp;</span></span></span><a href="http://www.discprofiles.com/blog/2014/01/resolute-leaders-everything-disc/"><span style="font-size:10.5pt"><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><span style="color:#0099d8">Resolute</span></span></span></a></span></span></span></p>
            </div>
        </div>
    @endif
</div>