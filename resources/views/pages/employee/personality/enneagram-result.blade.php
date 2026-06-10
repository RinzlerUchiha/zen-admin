<?php
$enneagram_list = [
	'1' => 'PERFECTIONIST',
	'2' => 'HELPER',
	'3' => 'ACHIEVER',
	'4' => 'ROMANTIC',
	'5' => 'OBSERVER',
	'6' => 'QUESTIONER',
	'7' => 'ADVENTURER',
	'8' => 'ASSERTER',
	'9' => 'PEACEMAKER'
];
?>
<div class="container-fluid">
	<ul class="nav nav-tabs" id="enneagramTab" role="tablist">
    	@foreach(array_keys($core) as $s)
    		<li class="nav-item" role="presentation">
                <button class="nav-link active" id="enneagramtab_{{ $s }}-tab" data-bs-toggle="tab" data-bs-target="#enneagramtab_{{ $s }}-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">{{ $enneagram_list[$s] }}</button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="enneagramTabContent">
    	<div class="tab-pane fade show active" id="enneagramtab_1-tab-pane" role="tabpanel" aria-labelledby="enneagramtab_1-tab" tabindex="0">
    		<span>(1) PERFECTIONIST</span><br>
			<span>Ones are motivated by the need to live their life the right way, including improving themselves and the world around them.</span>
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
			<span>HOW TO GET ALONG WITH ME</span>
			<li>Take your share of the responsibility so I don’t end up with all the work.</li>
			<li>Acknowledge my achievements.</li>
			<li>I’m hard on myself. Reassure me that I’m fine the way I am.</li>
			<li>Tell me that you value my advice.</li>
			<li>Be fair and considerate, as I am.</li>
			<li>Apologize if you have been unthoughtful. It will help me to forgive.</li>
			<li>Gently encourage me to lighten up and to laugh at myself when I get uptight, but hear my worries first.</li>
			<br>
			<span>RELATIONSHIPS</span>
			<li>Ones at their best in a relationship are loyal, dedicated, conscientious, and helpful. They are well balanced and have a good sense of humor.</li>
			<li>Ones at their worst in a relationship are critical, argumentative, nit-picking, and uncompromising. They have high expectations of others.</li>
			<br>
			<span>CAREERS</span><br>
			<span>Ones are efficient, organized, and always complete the task. The more analytical and tough-minded Ones are found in management, science, and law enforcement. The more people-oriented Ones are found in health care, education, and religious work. 
			<br>
			Since they do things in a professional, honest, and ethical manner, you would do well to have Ones as your car mechanic, surgeon, dentist, banker, and stockbroker.
			</span>
    	</div>
    </div>
    {{-- @foreach($core as $s)
		@switch($s)
			@case('1')
		        
			@break

			@default
			@break
		@endswitch
    @endforeach --}}
</div>