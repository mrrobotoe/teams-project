<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Team Members') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('These are the members of your team.') }}
        </p>
    </header>

    <div class="mt-6">
        <ul class="divide-y divide-gray-200">
            @foreach($team->members as $member)
                <x-team-member-item :team="$team" :member="$member"  />
            @endforeach
        </ul>
    </div>

</section>
<?php
