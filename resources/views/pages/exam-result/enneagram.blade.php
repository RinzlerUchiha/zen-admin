<ul class="nav nav-tabs" id="enneagramTab" role="tablist">
    @foreach ($topItems as $item => $score)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->index == 0 ? 'active' : '' }}" id="item-{{ $item }}-tab" data-bs-toggle="tab" data-bs-target="#item-{{ $item }}-tab-pane" type="button" role="tab" aria-controls="item-{{ $item }}-tab-pane" aria-selected="{{ $loop->index == 0 ? 'true' : 'false' }}">{{ $item }}</button>
        </li>
    @endforeach
</ul>
<div class="tab-content" id="enneagramTabContent">
    @if ($topItems->keys()->contains(1))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 1 ? 'show active' : ''}}" id="item-1-tab-pane" role="tabpanel" aria-labelledby="item-1-tab" tabindex="0">
            <h3>(1) PERFECTIONIST</h3>
            <p>Ones are motivated by the need to live their life the right way, including improving themselves and the world around
                them.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Ones at their BEST are</th>
                        <th>Ones at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Ethical</td>
                        <td>Judgmental</td>
                    </tr>
                    <tr>
                        <td>Reliable</td>
                        <td>Inflexible</td>
                    </tr>
                    <tr>
                        <td>Productive</td>
                        <td>Dogmatic (strict)</td>
                    </tr>
                    <tr>
                        <td>Wise</td>
                        <td>Obsessive-compulsive</td>
                    </tr>
                    <tr>
                        <td>Idealistic</td>
                        <td>Critical of others</td>
                    </tr>
                    <tr>
                        <td>Fair</td>
                        <td>Overly Serious</td>
                    </tr>
                    <tr>
                        <td>Honest</td>
                        <td>Controlling</td>
                    </tr>
                    <tr>
                        <td>Orderly</td>
                        <td>Anxious</td>
                    </tr>
                    <tr>
                        <td>Self-disciplined</td>
                        <td>Jealous</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Take your share of the responsibility so I don’t end up with all the work.</li>
                <li class="list-group-item py-1">Acknowledge my achievements.</li>
                <li class="list-group-item py-1">I’m hard on myself. Reassure me that I’m fine the way I am.</li>
                <li class="list-group-item py-1">Tell me that you value my advice.</li>
                <li class="list-group-item py-1">Be fair and considerate, as I am.</li>
                <li class="list-group-item py-1">Apologize if you have been unthoughtful. It will help me to forgive.</li>
                <li class="list-group-item py-1">Gently encourage me to lighten up and to laugh at myself when I get uptight, but hear my worries first.</li>
            </ul>
            <br>
            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Ones at their best in a relationship are loyal, dedicated, conscientious, and helpful. They are well balanced and have a good sense of humor.</li>
                <li class="list-group-item py-1">Ones at their worst in a relationship are critical, argumentative, nit-picking, and uncompromising. They have high expectations of others.</li>
            </ul>
            <br>
            <h4>CAREERS</h4>
            <p>Ones are efficient, organized, and always complete the task. The more analytical and tough-minded Ones are found in management, science, and law enforcement. The more people-oriented Ones are found in health care, education, and religious work.
            <br>
            Since they do things in a professional, honest, and ethical manner, you would do well to have Ones as your car mechanic, surgeon, dentist, banker, and stockbroker.
            </p>

        </div>
    @endif

    @if ($topItems->keys()->contains(2))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 2 ? 'show active' : ''}}" id="item-2-tab-pane" role="tabpanel" aria-labelledby="item-2-tab" tabindex="0">
            <h3>(2) HELPER</h3>
            <p>Two are motivated by the need to be loved and valued and to express their positive feelings toward others.
                Traditionally society has encouraged Two qualities in females more than in males.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Twos at their BEST are</th>
                        <th>Twos at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Loving</td>
                        <td>Martyr like</td>
                    </tr>
                    <tr>
                        <td>Caring</td>
                        <td>Indirect</td>
                    </tr>
                    <tr>
                        <td>Adaptable</td>
                        <td>Manipulative</td>
                    </tr>
                    <tr>
                        <td>Insightful</td>
                        <td>Possessive</td>
                    </tr>
                    <tr>
                        <td>Generous</td>
                        <td>Hysterical</td>
                    </tr>
                    <tr>
                        <td>Enthusiastic</td>
                        <td>Overly Accommodating</td>
                    </tr>
                    <tr>
                        <td>Turned in to how</td>
                        <td>Overly demonstrative (the more extroverted Twos)</td>
                    </tr>
                    <tr>
                        <td>People feel</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Tell me that you appreciate me. Be specific.</li>
                <li class="list-group-item py-1">Share fun times with me.</li>
                <li class="list-group-item py-1">Take an interest in my problems, though I will probably try to focus on yours.</li>
                <li class="list-group-item py-1">Let me know that I am important and special to you.</li>
                <li class="list-group-item py-1">Be gentle if you decide to criticize me.</li>
            </ul>
            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Twos at their best in a relationship are attentive, appreciative, generous, warm, playful, and nurturing. Twos makes their partners feel special and loved.</li>
                <li class="list-group-item py-1">Twos at their worst in relationship are controlling, possessive, needy, and insincere. Since they have trouble asking directly, they tend to manipulate to get what they want.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Twos usually prefer to work with people, often in the helping professions, as counselors, teachers, and health workers.
                <br>
                Extroverted twos are sometimes found in the limelight as actresses, actors, and motivational speakers.
                <br>
                Twos also work in sales and helping others as receptionists, secretaries, assistants, decorators, and clothing consultants.
            </p>
        </div>
    @endif

    @if ($topItems->keys()->contains(3))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 3 ? 'show active' : ''}}" id="item-3-tab-pane" role="tabpanel" aria-labelledby="item-3-tab" tabindex="0">
            <h3>(3) ACHIEVER</h3>
            <p>Threes are motivated by the need to be productive, achieve success, and avoid failure.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Threes at their BEST are</th>
                        <th>Threes at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Optimistic</td>
                        <td>Deceptive</td>
                    </tr>
                    <tr>
                        <td>Confident</td>
                        <td>Narcissistic</td>
                    </tr>
                    <tr>
                        <td>Industrious</td>
                        <td>Pretentious</td>
                    </tr>
                    <tr>
                        <td>Efficient</td>
                        <td>Vain</td>
                    </tr>
                    <tr>
                        <td>Self-propelled</td>
                        <td>Superficial</td>
                    </tr>
                    <tr>
                        <td>Energetic</td>
                        <td>Vindictive</td>
                    </tr>
                    <tr>
                        <td>Practical</td>
                        <td>Overly competitive</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Leave me alone when I am doing my work.</li>
                <li class="list-group-item py-1">Give me honest, but not unduly critical or judgmental, feedback.</li>
                <li class="list-group-item py-1">Help me keep my environment harmonious and peaceful.</li>
                <li class="list-group-item py-1">Don’t burden me with negative emotions. </li>
                <li class="list-group-item py-1">Tell me when you’re proud of me or my accomplishments.</li>
                <li class="list-group-item py-1">Tell me you like being around me.</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Threes at their best in relationship value and accept their partners. They are playful, giving, responsible, and well regarded by others in the community.</li>
                <li class="list-group-item py-1">Threes are their worst in a relationship are preoccupied with work and projects. They are self-absorbed, defensive, impatient, dishonest, and controlling.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>These are hardworking, goal oriented, organized, and decisive. They are frequently in management or leadership positions in business, law, banking, the computer field, and politics. Being in the public eye, as broadcasters and performers, is also common. The more helping-oriented Threes also become homemakers who put tremendous energy into their responsibilities.</p>
        </div>
    @endif

    @if ($topItems->keys()->contains(4))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 4 ? 'show active' : ''}}" id="item-4-tab-pane" role="tabpanel" aria-labelledby="item-4-tab" tabindex="0">
            <h3>(4) ROMANTIC</h3>
            <p>Fours are motivated by the need to experience their feeling and to be understood, to search for the meaning of life,
                and to avoid being ordinary.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Fours at their BEST are</th>
                        <th>Fours at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Warm</td>
                        <td>Depressed</td>
                    </tr>
                    <tr>
                        <td>Compassionate</td>
                        <td>Self-conscious</td>
                    </tr>
                    <tr>
                        <td>Introspective</td>
                        <td>Guilt-ridden</td>
                    </tr>
                    <tr>
                        <td>Expressive</td>
                        <td>Moralistic</td>
                    </tr>
                    <tr>
                        <td>Creative</td>
                        <td>Withdrawn</td>
                    </tr>
                    <tr>
                        <td>Intuitive</td>
                        <td>Stubborn</td>
                    </tr>
                    <tr>
                        <td>Supportive</td>
                        <td>Moody</td>
                    </tr>
                    <tr>
                        <td>Refined</td>
                        <td>Self-absorbed</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Give me plenty of compliments. They mean a lot to me.</li>
                <li class="list-group-item py-1">Be a supportive friend or partner. Help me to learn to love and value myself.</li>
                <li class="list-group-item py-1">Respect me for my special gifts of intuition and vision.</li>
                <li class="list-group-item py-1">Though I don’t always want to be cheered up when I’m feeling melancholy, I sometimes like to have someone lighten me up a little.</li>
                <li class="list-group-item py-1">Don’t tell me I’m too sensitive or that I’m overreacting!</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Fours at their best in a relationship are empathic, supportive, gentle, playful, passionate, and witty. They are self-revealing and bond easily.</li>
                <li class="list-group-item py-1">Fours at their worst in a relationship are too self-absorbed, jealous, emotionally needy, moody, self-righteous, and overly critical. They become hurt and feel rejected easily.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Fours can inspire, influence, and persuade through the arts (music, fine art, dancing) and the written or spoken word (poetry, novels, journalism, teaching). Many like to help bring out the best in people as psychologist or counselors. Some take pride in the small business they own. Often Fours accept mundane jobs to support their creative pursuits.</p>

        </div>
    @endif

    @if ($topItems->keys()->contains(5))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 5 ? 'show active' : ''}}" id="item-5-tab-pane" role="tabpanel" aria-labelledby="item-5-tab" tabindex="0">
            <h3>(5) OBSERVER</h3>
            <p>Fives are motivated by the need to know and understand everything, to be self-sufficient, and to avoid looking
                foolish.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Fives at their BEST are</th>
                        <th>Fives at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Analytical</td>
                        <td>Intellectually Arrogant</td>
                    </tr>
                    <tr>
                        <td>Persevering</td>
                        <td>Stingy</td>
                    </tr>
                    <tr>
                        <td>Sensitive</td>
                        <td>Stubborn</td>
                    </tr>
                    <tr>
                        <td>Wise</td>
                        <td>Distant</td>
                    </tr>
                    <tr>
                        <td>Objective</td>
                        <td>Critical of others</td>
                    </tr>
                    <tr>
                        <td>Perceptive</td>
                        <td>Unassertive</td>
                    </tr>
                    <tr>
                        <td>Self-contained</td>
                        <td>Negative</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Be independent, not clingy.</li>
                <li class="list-group-item py-1">Speak in a straightforward and brief manner.</li>
                <li class="list-group-item py-1">I need time alone to process my feelings and thoughts. </li>
                <li class="list-group-item py-1">Remember that if I seem aloof, distant, or arrogant, it may be that I am feeling uncomfortable.</li>
                <li class="list-group-item py-1">Make me feel welcome, but not to intensely, or I might doubt your sincerity.</li>
                <li class="list-group-item py-1">If I become irritated when I have to repeat things, it may be because it was such an effort to get my thoughts out in the first place.</li>
                <li class="list-group-item py-1">Don’t come on like a bulldozer.</li>
                <li class="list-group-item py-1">Help me to avoid my pet peeves: big parties, other people’s loud music, overdone emotions, and intrusions on my privacy.</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Fives at their best in a relationship are kind, perceptive, open-minded, self-sufficient, and trustworthy. Fives at their worst in a relationship are contentious, suspicious, withdrawn, and negative. They are on their guard against being engulfed.
                </li>
                {{-- <li class="list-group-item py-1">Fives at their best in a relationship are kind, perceptive, open-minded, self-sufficient, and trustworthy. Fives at their worst in a relationship are contentious, suspicious, withdrawn, and negative. They are on their guard against being engulfed.
                </li> --}}
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Fives are often in scientific, technical, or other intellectually demanding fields. They have strong analytical skills and are good at problem solving. Those with a well-developed Four wing are more likely to be counselors, musicians, artists, or writers. Fives usually like to work alone and are independent thinkers.
            </p>

        </div>
    @endif

    @if ($topItems->keys()->contains(6))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 6 ? 'show active' : ''}}" id="item-6-tab-pane" role="tabpanel" aria-labelledby="item-6-tab" tabindex="0">
            <h3>(6) QUESTIONER</h3>
            <p>Sixes are motivated by the need for security. Phobic Sixes are outwardly fearful and seek approval. Counterphobic
                Sixes confront their fear. Both of these aspects can appear in the same person.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sixes at their BEST are</th>
                        <th>Sixes at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Loyal</td>
                        <td>Hypervigilant</td>
                    </tr>
                    <tr>
                        <td>Likable</td>
                        <td>Controlling</td>
                    </tr>
                    <tr>
                        <td>Caring</td>
                        <td>Unpredictable</td>
                    </tr>
                    <tr>
                        <td>Warm</td>
                        <td>Judgmental</td>
                    </tr>
                    <tr>
                        <td>Compassionate</td>
                        <td>Paranoid</td>
                    </tr>
                    <tr>
                        <td>Witty</td>
                        <td>Defensive</td>
                    </tr>
                    <tr>
                        <td>Practical</td>
                        <td>Rigid</td>
                    </tr>
                    <tr>
                        <td>Helpful</td>
                        <td>Self-defeating</td>
                    </tr>
                    <tr>
                        <td>Responsible</td>
                        <td>Testy</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Be direct and clear.</li>
                <li class="list-group-item py-1">Listen to me carefully.</li>
                <li class="list-group-item py-1">Don’ts judge me for my anxiety.</li>
                <li class="list-group-item py-1">Work things through with me.</li>
                <li class="list-group-item py-1">Reassure me that everything is OK between us.</li>
                <li class="list-group-item py-1">Laugh and make jokes with me.</li>
                <li class="list-group-item py-1">Gently push me toward new experiences.</li>
                <li class="list-group-item py-1">Try not to overreact to my overreacting.</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Sixes at their best in a relationship are warm, playful, open, loyal, supportive, honest, fair, and reliable.</li>
                <li class="list-group-item py-1">Sixes at their worst in a relationship are suspicious, controlling, inflexible, and sarcastic. They either withdraw or put on a tough act when threatened.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Though sixes can be found in almost any career, they are often attracted to the justice system, the military, the corporate world, and academia. Sixes often like being part of a team. Many are in health care and education.
            <br>
            Counterphobic Sixes sometimes have jobs that involve risk. Those who learn toward the antiauthoritarian side are usually happier when self-employed.
            <br>
            If sixes are unhappy with their work situation, they are likely to become rebellious or secretive.
            </p>

        </div>
    @endif

    @if ($topItems->keys()->contains(7))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 7 ? 'show active' : ''}}" id="item-7-tab-pane" role="tabpanel" aria-labelledby="item-7-tab" tabindex="0">
            <h3>(7) ADVENTURER</h3>
            <p>Sevens are motivated by the need to be happy and plan enjoyable activities, to contribute to the world, and to avoid
                suffering and pain.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sevens at their BEST are</th>
                        <th>Sevens at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Fun-loving</td>
                        <td>Narcissistic</td>
                    </tr>
                    <tr>
                        <td>Spontaneous</td>
                        <td>Impulsive</td>
                    </tr>
                    <tr>
                        <td>Imaginative</td>
                        <td>Unfocused</td>
                    </tr>
                    <tr>
                        <td>Productive</td>
                        <td>Rebellious</td>
                    </tr>
                    <tr>
                        <td>Enthusiastic</td>
                        <td>Undisciplined</td>
                    </tr>
                    <tr>
                        <td>Quick</td>
                        <td>Possessive</td>
                    </tr>
                    <tr>
                        <td>Confident</td>
                        <td>Manic</td>
                    </tr>
                    <tr>
                        <td>Charming</td>
                        <td>Self-destructive</td>
                    </tr>
                    <tr>
                        <td>Curious</td>
                        <td>Restless</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Give me companionship, affection, and freedom.</li>
                <li class="list-group-item py-1">Engage with me in stimulating conversation and laughter.</li>
                <li class="list-group-item py-1">Appreciate my grand visions and listen to my stories. </li>
                <li class="list-group-item py-1">Don’t try to change my style. Accept me the way I am.</li>
                <li class="list-group-item py-1">Be responsible for yourself. I dislike clingy or needy people. </li>
                <li class="list-group-item py-1">Don’t tell me what to do.</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Sevens at their best in a relationship are lighthearted, generous, outgoing, caring, and fun. They introduce their friends and loved ones to new activities and adventures.</li>
                <li class="list-group-item py-1">Sevens at their worst in a relationship are narcissistic, opinionated, defensive, and distracted. They are often ambivalent about being tied down to a relationship.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Many sevens have several careers at once or jobs where they travel a lot (as pilots, flight attendants, or photographers, for example). Some like using tools or machines or working outdoors. Others prefer solving problems as entrepreneurs or troubleshooters. Still others are in the helping professions as teachers, nurses, or counselor. Sevens are not likely to be found in repetitive work (in assembly lines or accounting, for instance). They like challenges and think quickly in emergencies.
            </p>

        </div>
    @endif

    @if ($topItems->keys()->contains(8))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 8 ? 'show active' : ''}}" id="item-8-tab-pane" role="tabpanel" aria-labelledby="item-8-tab" tabindex="0">
            <h3>(8) ASSERTER</h3>
            <p>Eights are motivated by the need to be self-reliant and strong and to avoid feeling weak or dependent.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Eights at their BEST are</th>
                        <th>Eights at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Direct</td>
                        <td>Controlling</td>
                    </tr>
                    <tr>
                        <td>Authoritative</td>
                        <td>Rebellious</td>
                    </tr>
                    <tr>
                        <td>Loyal</td>
                        <td>Insensitive</td>
                    </tr>
                    <tr>
                        <td>Energetic</td>
                        <td>Domineering</td>
                    </tr>
                    <tr>
                        <td>Earthy</td>
                        <td>Self-centered</td>
                    </tr>
                    <tr>
                        <td>Protective</td>
                        <td>Skeptical</td>
                    </tr>
                    <tr>
                        <td>Self-confident</td>
                        <td>Aggressive</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Stand up for yourself… and me.</li>
                <li class="list-group-item py-1">Be confident, strong, and direct.</li>
                <li class="list-group-item py-1">Don’t gossip about me or betray my trust.</li>
                <li class="list-group-item py-1">Be vulnerable and share your feelings. See and knowledge my tender, vulnerable side.</li>
                <li class="list-group-item py-1">Give me space to be alone.</li>
                <li class="list-group-item py-1">Acknowledge the contributions I make, but don’t flatter me.</li>
                <li class="list-group-item py-1">I often speak in an assertive way. Don’t automatically assume it’s a personal attack.</li>
                <li class="list-group-item py-1">When I scream, curse, and stomp around, try to remember that’s just the way I am.</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Eights at their best in a relationship are loyal, caring positive, playful, truthful, straightforward, committed, generous, and supportive.</li>
                <li class="list-group-item py-1">Eights at their worst in a relationship are demanding, arrogant, combative, possessive, uncompromising, and quick to find fault.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Eights are good at taking the initiative to move ahead. They want to be in charge. Since they want the freedom to make their own choices, they are often self-employed. Eights have a strong need for financial security. Many are entrepreneurs, business executive, lawyers, military and union leaders, and sports figures. They are also in teaching and the helping and health professions. Eights are attracted to careers in which they can demonstrate their willingness to accept responsibility and take on and resolve difficult problems.
            </p>

        </div>
    @endif

    @if ($topItems->keys()->contains(9))
        <div class="tab-pane fade {{ $topItems->keys()->first() == 9 ? 'show active' : ''}}" id="item-9-tab-pane" role="tabpanel" aria-labelledby="item-9-tab" tabindex="0">
            <h3>(9) PEACEMAKER</h3>
            <p>Nines are motivated by the need to keep the peace, to merge with others, and to avoid conflict. Since they
                especially, take on qualities of the other eight types, Nines have many variations in their personalities, from
                gentle and mild-mannered to independent and forceful.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nines at their BEST are</th>
                        <th>Nines at their WORST are</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Pleasant</td>
                        <td>Spaced-out</td>
                    </tr>
                    <tr>
                        <td>Peaceful</td>
                        <td>Forgetful</td>
                    </tr>
                    <tr>
                        <td>Generous</td>
                        <td>Stubborn</td>
                    </tr>
                    <tr>
                        <td>Patient</td>
                        <td>Obsessive</td>
                    </tr>
                    <tr>
                        <td>Receptive</td>
                        <td>Apathetic</td>
                    </tr>
                    <tr>
                        <td>Diplomatic</td>
                        <td>Passive-aggressive</td>
                    </tr>
                    <tr>
                        <td>Open-minded</td>
                        <td>Judgmental</td>
                    </tr>
                    <tr>
                        <td>Emphatic</td>
                        <td>Unassertive</td>
                    </tr>
                </tbody>
            </table>
            <h4>HOW TO GET ALONG WITH ME</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">If you want me to do something, how you ask is important. I especially don’t like expectations or pressure.</li>
                <li class="list-group-item py-1">I like to listen and to be of service, but don’t take advantage of this.</li>
                <li class="list-group-item py-1">Listen until I finish speaking, even though I meander (wander) a bit.</li>
                <li class="list-group-item py-1">Give me time to finish things and make decisions. It’s OK to nudge me gently and nonjudgmentally.</li>
                <li class="list-group-item py-1">Ask me questions to help me get clear.</li>
                <li class="list-group-item py-1">Tell me when you like how I look. I’m not averse (reluctant) to flattery.</li>
                <li class="list-group-item py-1">Hug me, show physical affection. It opens me up to my feelings.</li>
                <li class="list-group-item py-1">I like a good discussion but not a confrontation.</li>
                <li class="list-group-item py-1">Let me know you like what I’ve done or said.</li>
                <li class="list-group-item py-1">Laugh with me and share in my enjoyment of life.</li>
            </ul>

            <br>

            <h4>RELATIONSHIPS</h4>
            <ul class="list-group">
                <li class="list-group-item py-1">Nines at their best in a relationship are kind, gentle, reassuring, supportive, loyal, and nonjudgmental.</li>
                <li class="list-group-item py-1">Nines at their worst in a relationship are stubborn, passive-aggressive, unassertive, overly accommodating, and defensive.</li>
            </ul>

            <br>

            <h4>CAREERS</h4>
            <p>Nines listens well, are objective, and make excellent mediators and diplomats. They are frequently in the helping professions. Some prefer structured situations, such as the military, civil service, and other bureaucracies.
            <br>
            When Nines move toward point Three or Six, or their One or Eight wing is strong, they are more aggressive and competitive.
            </p>

        </div>
    @endif
</div>